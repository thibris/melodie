import os
os.chdir("/var/www/html")
import re
import requests
from bs4 import BeautifulSoup

from datetime import datetime
print(datetime.now())

URL = "https://melodienocy.blogspot.com/p/muzyka-z-melodii-mgie-nocnych.html"
WZORZEC_PLIKU = re.compile(r"melodie\.(\d{4})_(\d{2})_(\d{2})_23H55M\.mp3")

def pobierz_strone(url):
    try:
        response = requests.get(url)
        response.raise_for_status()
        response.encoding = 'utf-8'
        return BeautifulSoup(response.text, "html.parser")
    except requests.RequestException as e:
        print(f"Błąd podczas pobierania strony: {e}")
        return None

def znajdz_sekcje(soup):
    return soup.find("div", class_="post-body entry-content")

def zapisz_playliste(data, sekcja, plik_wyjsciowy):
    print(f"Szukanie playlisty dla daty: {data}")
    if not sekcja:
        print("Nie znaleziono sekcji z treścią.")
        return
    tekst = sekcja.get_text(separator="\n")
    bloki = re.split(r"\n(?=\d{2}\.\d{2}\.\d{2})", tekst)
    dt = datetime.strptime(data, "%d.%m.%y")
    data_full = dt.strftime("%d.%m.%Y")
    for blok in bloki:
        if blok.strip().startswith(data) or blok.strip().startswith(data_full):
            with open(plik_wyjsciowy, "w", encoding="utf-8") as plik:
                plik.write(blok.strip())
            print(f"Zapisano playlistę do pliku: {plik_wyjsciowy}")
            return
    print(f"Brak playlisty dla daty {data}.")

def przetworz_folder(sekcja):
    for plik in os.listdir("."):
        if plik.endswith(".mp3") and not os.path.exists(plik.replace(".mp3", ".txt")):
            dopasowanie = WZORZEC_PLIKU.match(plik)
            if dopasowanie:
                rok, miesiac, dzien = dopasowanie.groups()
                data = f"{dzien}.{miesiac}.{rok[2:]}"  # np. 03.11.25
                plik_txt = plik.replace(".mp3", ".txt")
                zapisz_playliste(data, sekcja, plik_txt)

def main():
    soup = pobierz_strone(URL)
    if soup:
        sekcja = znajdz_sekcje(soup)
        przetworz_folder(sekcja)

if __name__ == "__main__":
    main()
