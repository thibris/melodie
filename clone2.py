import threading
import numpy as np
import sounddevice as sd
import datetime
import time
import os
import platform
import requests
import sys

# ==========================
# CONFIGURATION
# ==========================
delay = 70                     # seconds between API calls
alert_threshold = 4            # 2–5
league = "Softcore"            # "Softcore" or "Hardcore"
ladder = "Non-ladder"          # "Ladder" or "Non-ladder"

# ==========================
# CONSTANTS
# ==========================
regionstrs = {'1':'America','2':'Europe','3':'Asia'}
leaguedict = {'Hardcore':'1', 'Softcore':'2'}
ladderdict = {'Ladder':'1', 'Non-ladder':'2'}
last_prog = [None, None, None]  # for detecting changes

# ==========================
# COLORS
# ==========================
RESET = "\033[0m"
BOLD = "\033[1m"
RED = "\033[91m"
ORANGE = "\033[33m"
YELLOW = "\033[93m"
CYAN = "\033[96m"
DIM = "\033[90m"
GREEN = "\033[92m"
PINK = "\033[95m"
WHITE = "\033[97m"

# ==========================
# HELPERS
# ==========================
def countdown(delay):
    # Countdown loop (nadpisuje jedną linijkę, nic nie zostaje w logu)
    for remaining in range(delay, 0, -1):
        print(
            f"\r{WHITE}Next refresh in: {YELLOW}{remaining}{WHITE} seconds...{RESET}",
            end="",
            flush=True
        )
        time.sleep(1)
    # wyczyść linię odliczania zanim wypiszemy wynik
    print("\r" + " " * 80 + "\r", end="")

def time_ago(dt):
    now = datetime.datetime.now()
    diff = now - dt
    seconds = diff.total_seconds()
    minutes = seconds // 60
    hours = minutes // 60
    days = hours // 24
    weeks = days // 7

    if seconds < 60:
        return "just now"
    elif minutes < 60:
        return f"{int(minutes)}m ago"
    elif hours < 24:
        return f"{int(hours)}h ago"
    elif days < 7:
        return f"{int(days)}d ago"
    else:
        return f"{int(weeks)}w ago"

def siren(siren_length):
    samplerate = 44100

    def sweep(f_start, f_end, duration=1.0, volume=0.5):
        t = np.linspace(0, duration, int(samplerate * duration))
        freqs = np.linspace(f_start, f_end, t.size)
        wave = np.sin(2 * np.pi * freqs * t) * volume
        sd.play(wave, samplerate)
        sd.wait()

    siren_start = time.time()
    while time.time() - siren_start < siren_length:
        sweep(500, 1200, 1.0)
        sweep(1200, 500, 1.0)

def color_for_progress(value):
    colors = {
        1: CYAN,
        2: GREEN,
        3: YELLOW,
        4: ORANGE,
        5: PINK,
        6: RED
    }
    return colors.get(value, WHITE)

# ==========================
# MAIN LOOP
# ==========================
def runloop():
    api_url = (
        "https://diablo2.io/dclone_api.php?ver=2"
        "&hc=" + leaguedict[league] +
        "&ladder=" + ladderdict[ladder] +
        "&sk=r&sd=a"
    )

    while True:

        # Fetch data
        try:
            call = requests.get(api_url).json()
        except:
            print(RED + "ERROR retrieving data... retrying..." + RESET)
            countdown(delay)
            continue
        alert = [False]

        timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        line_output = f"{DIM}[{timestamp}]{RESET} "

        # Build output for each region
        for regionidx in range(3):
            region_name = regionstrs[call[regionidx]['region']]
            prog = int(call[regionidx]['progress'])
            #progstr = str(prog)
            tstamp = datetime.datetime.fromtimestamp(int(call[regionidx]['timestamped']))

            # Append region info (colorized)
            prog_color = color_for_progress(prog)
            line_output += (
                f"{WHITE}{region_name}: "
                f"{prog_color}{'#' * prog}{'-' * (6-prog)} "
                f"{DIM}({time_ago(tstamp)}),{RESET}  "
            )

            # --- CHANGE DETECTION ---
            if last_prog[regionidx] is not None and last_prog[regionidx] < prog:
                if prog >= alert_threshold:
                    line_output += f"{BOLD}{RED} *** ALERT! Terror in {region_name}! *** {RESET}"
                    threading.Thread(target=siren, args=(15,), daemon=True).start()
                else:
                    threading.Thread(target=siren, args=(3,), daemon=True).start()

            last_prog[regionidx] = prog

        # Print the result as a new line
        print(line_output)

        countdown(delay)

# ==========================
# START
# ==========================
if __name__ == "__main__":
    runloop()