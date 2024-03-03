<!DOCTYPE html>
<html>
  <head>
    <title>Simple Audio Player</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="audio.css">
    <script src="audio.js"></script>
  </head>
  <body><div id="demo">
    <!-- (A) AUDIO TAG -->
    <audio id="demoAudio" controls></audio>

    <!-- (B) PLAYLIST -->
    <div id="demoList"><?php
      // (B1) GET ALL SONGS
      $songs = glob("*.{mp3,webm,ogg,wav}", GLOB_BRACE);

      // (B2) OUTPUT SONGS IN <DIV>
      if (is_array($songs)) { foreach ($songs as $k=>$s) {
        $name = basename($s);
        printf("<div data-src='%s' class='song'>%s</div>", rawurlencode($name), $name);
      }} else { echo "No songs found!"; }
    ?></div>
  </div></body>
</html>
