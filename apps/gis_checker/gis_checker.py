#!/usr/bin/env python3
"""
GIS Alert Checker – skrypt uruchamiany przez cron na VPS.
Sprawdza stronę GIS pod kątem nowych ostrzeżeń i wysyła powiadomienia FCM.
"""

import json
import logging
import os
import sys
import requests
from bs4 import BeautifulSoup
from datetime import datetime
import google.auth.transport.requests
from google.oauth2 import service_account

# ──────────────────────────────────────────────
# Konfiguracja
# ──────────────────────────────────────────────
BASE_DIR        = os.path.dirname(os.path.abspath(__file__))
STATE_FILE      = os.path.join(BASE_DIR, "seen_alerts.json")
SERVICE_ACCOUNT = os.path.join(BASE_DIR, "firebase_service_account.json")
LOG_FILE        = os.path.join(BASE_DIR, "gis_checker.log")

GIS_URL         = "https://www.gov.pl/web/gis/ostrzezenia"
FCM_TOPIC       = "gis_alerts"
FCM_SCOPES      = ["https://www.googleapis.com/auth/firebase.messaging"]

# ──────────────────────────────────────────────
# Logging
# ──────────────────────────────────────────────
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[
        logging.FileHandler(LOG_FILE, encoding="utf-8"),
        logging.StreamHandler(sys.stdout),
    ],
)
log = logging.getLogger(__name__)


# ──────────────────────────────────────────────
# Stan (które ostrzeżenia już wysłano)
# ──────────────────────────────────────────────
def load_seen() -> list:
    """Wczytuje listę wysłanych ostrzeżeń zachowując kolejność."""
    if not os.path.exists(STATE_FILE):
        return []
    try:
        with open(STATE_FILE, "r", encoding="utf-8") as f:
            data = json.load(f)
        return data.get("seen", [])
    except Exception as e:
        log.warning(f"Błąd wczytywania stanu: {e} – zaczynam od pustej listy.")
        return []


def save_seen(seen: list) -> None:
    """Zapisuje listę wysłanych ostrzeżeń w kolejności (najnowsze na górze)."""
    with open(STATE_FILE, "w", encoding="utf-8") as f:
        json.dump(
            {"seen": seen, "updated": datetime.now().isoformat()},
            f,
            ensure_ascii=False,
            indent=2
        )
    log.info(f"Stan zapisany ({len(seen)} ostrzeżeń w historii).")


# ──────────────────────────────────────────────
# Scraping GIS
# ──────────────────────────────────────────────
def fetch_alerts() -> list[dict]:
    """
    Pobiera listę ostrzeżeń ze strony GIS.
    Zwraca listę słowników: [{"title": str, "url": str}, ...]
    """
    headers = {
        "User-Agent": (
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 "
            "(KHTML, like Gecko) Chrome/120.0 Safari/537.36"
        )
    }
    log.info(f"Pobieranie strony: {GIS_URL}")
    resp = requests.get(GIS_URL, headers=headers, timeout=30)
    resp.raise_for_status()

    soup = BeautifulSoup(resp.text, "html.parser")
    alerts = []

    # Gov.pl renderuje listę artykułów w tagach <article> lub <a> wewnątrz sekcji ostrzeżeń.
    # Szukamy linków z klasą charakterystyczną dla tej strony.
    # Struktura: div.article-area > ul > li > a  LUB  article > a
    candidates = soup.select("article a[href], .article-list a[href], .search-result a[href]")

    if not candidates:
        # Fallback: wszystkie linki zawierające /ostrzez w href
        candidates = [a for a in soup.find_all("a", href=True) if "/ostrzez" in a["href"].lower()]

    seen_urls = set()
    for a in candidates:
        title = a.get_text(separator=" ", strip=True)
        href  = a["href"].strip()

        # Pomiń puste i zduplikowane
        if not title or href in seen_urls:
            continue
        # Pomiń linki nawigacyjne (bardzo krótkie)
        if len(title) < 20:
            continue

        seen_urls.add(href)
        full_url = href if href.startswith("http") else f"https://www.gov.pl{href}"
        alerts.append({"title": title, "url": full_url})

    log.info(f"Znaleziono {len(alerts)} ostrzeżeń na stronie.")
    return alerts


# ──────────────────────────────────────────────
# Firebase FCM – wysyłanie do topica
# ──────────────────────────────────────────────
def get_fcm_access_token() -> str:
    """Pobiera krótkotrwały access token z service account JSON."""
    credentials = service_account.Credentials.from_service_account_file(
        SERVICE_ACCOUNT, scopes=FCM_SCOPES
    )
    request = google.auth.transport.requests.Request()
    credentials.refresh(request)
    return credentials.token


def send_fcm_notification(title: str, alert_url: str, topic: str = FCM_TOPIC) -> bool:
    """
    Wysyła powiadomienie FCM do podanego topica.
    Zwraca True jeśli sukces, False jeśli błąd.
    """
    try:
        token = get_fcm_access_token()

        # Odczytaj project_id z pliku service account
        with open(SERVICE_ACCOUNT, "r") as f:
            sa_data = json.load(f)
        project_id = sa_data["project_id"]

        url = f"https://fcm.googleapis.com/v1/projects/{project_id}/messages:send"
        headers = {
            "Authorization": f"Bearer {token}",
            "Content-Type": "application/json",
        }
        payload = {
            "message": {
                "topic": topic,
                "notification": {
                    "title": "Nowe ostrzeżenie GIS",
                    "body": title,
                },
                "data": {
                    "alert_title": title,
                    "alert_url": alert_url,
                    "topic": topic,
                },
                "android": {
                    "notification": {
                        "click_action": "OPEN_ALERT_URL",
                        "channel_id": "gis_alerts",
                    }
                },
            }
        }

        resp = requests.post(url, headers=headers, json=payload, timeout=15)
        if resp.status_code == 200:
            log.info(f"✅ FCM OK → temat: {topic} | tytuł: {title[:60]}")
            return True
        else:
            log.error(f"❌ FCM błąd {resp.status_code}: {resp.text}")
            return False

    except Exception as e:
        log.error(f"❌ Wyjątek podczas wysyłania FCM: {e}")
        return False


# ──────────────────────────────────────────────
# Główna logika
# ──────────────────────────────────────────────
def main():
    log.info("═" * 60)
    log.info("GIS Checker – start")

    seen = load_seen()
    alerts = fetch_alerts()

    if not alerts:
        log.warning("Nie znaleziono żadnych ostrzeżeń – sprawdź strukturę strony!")
        return

    new_count = 0
    for alert in alerts:
        title = alert["title"]
        url   = alert["url"]

        if title in seen:
            log.debug(f"Pominięto (już wysłane): {title[:60]}")
            continue

        log.info(f"🆕 Nowe ostrzeżenie: {title[:80]}")
        success = send_fcm_notification(title, url)
        if success:
            seen.insert(0, title)
            new_count += 1

    if new_count == 0:
        log.info("Brak nowych ostrzeżeń.")
    else:
        log.info(f"Wysłano {new_count} nowych powiadomień.")
        save_seen(seen)

    log.info("GIS Checker – koniec")
    log.info("═" * 60)


if __name__ == "__main__":
    main()

