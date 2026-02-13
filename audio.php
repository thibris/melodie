<!DOCTYPE html>
<html>
<head>
  <title>MMN - Player</title>
  <meta charset="utf-8">
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <style>
    body { font-family: Arial, sans-serif; }
    #demo { display: flex; gap: 20px; align-items: flex-start; }
    #demoList { flex: 1; border: 1px solid #ccc; padding: 10px; }
    .year, .month { cursor: pointer; font-weight: bold; margin: 5px 0; }
    .month { margin-left: 15px; }
    .songs { display: none; margin-left: 30px; }
    .song { cursor: pointer; padding: 3px; margin: 2px 0; border-bottom: 1px solid #eee; }
    .song:hover { background-color: #f0f0f0; }
    #playlistPanel { flex: 1; border: 1px solid #ccc; padding: 10px; min-height: 150px; background: #fafafa; }
    #demoAudio { width: 100%; margin-top: 10px; }
  </style>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const audio = document.getElementById("demoAudio");
      const playlistDiv = document.getElementById("playlistInfo");

      // obsługa kliknięcia w miesiąc
      document.querySelectorAll(".month").forEach(month => {
        month.addEventListener("click", function() {
          const songsDiv = this.nextElementSibling;
          songsDiv.style.display = (songsDiv.style.display === "block") ? "none" : "block";
        });
      });

      // obsługa kliknięcia w utwór
      document.querySelectorAll(".song").forEach(song => {
        song.addEventListener("click", function() {
          audio.src = decodeURIComponent(this.dataset.src);
          audio.play();
          playlistDiv.textContent = this.dataset.playlist || "Brak opisu";
        });
      });
    });
  </script>
</head>
<body>
  <?php include 'header.php';?>
  <div id="demo">
    <div id="demoList">
      <?php
        $songsByDate = [];
        foreach (glob("*.mp3") as $filename) {
          // przykład nazwy: melodie.2025_08_25_23H55M.mp3
          if (preg_match('/(\d{4})_(\d{2})_(\d{2})_(\d{2})H(\d{2})M/', $filename, $matches)) {
            $year  = $matches[1]; // 2025
            $month = $matches[2]; // 08
            $day   = $matches[3]; // 25
            $hour  = $matches[4]; // 23
            $min   = $matches[5]; // 55
          } else {
            // fallback gdyby nazwa nie pasowała
            $year = "Inne";
            $month = "00";
          }

          $basename = pathinfo($filename, PATHINFO_FILENAME).".txt";
          if (file_exists($basename)) {
            $file = fopen($basename, "r") or die("Unable to open file!");
            $playlist = fread($file, filesize($basename));
            fclose($file);
          } else {
            $playlist = '';
          }

          $songsByDate[$year][$month][] = [
            "file" => $filename,
            "playlist" => $playlist
          ];
        }

        // Renderowanie drzewa: rok → miesiąc → utwory
        foreach ($songsByDate as $year => $months) {
          echo "<div class='year'>$year</div>";
          foreach ($months as $month => $songs) {
            echo "<div class='month'>Miesiąc $month</div>";
            echo "<div class='songs'>";
            foreach ($songs as $song) {
              echo '<div data-src="'.rawurlencode($song["file"]).'" data-playlist="'.htmlspecialchars($song["playlist"]).'" class="song">'.$song["file"].'</div>';
            }
            echo "</div>";
          }
        }
      ?>
      <audio id="demoAudio" controls></audio>
    </div>
    <div id="playlistPanel">
      <h3>Opis playlisty</h3>
      <div id="playlistInfo" style="white-space: pre-line;">
        Kliknij utwór, aby zobaczyć opis
      </div>
    </div>
  </div>
  <?php include 'footer.php'; ?>
</body>
</html>

