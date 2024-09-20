<!DOCTYPE html>
<html>
  <head>
    <title>MMN - Player</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="audio.css">
    <script src="audio.js"></script>
  </head>
  <body>
    <div id="demo">
      <audio id="demoAudio" controls></audio>
      <div id="demoList">
        <?php
          foreach (glob("*.mp3") as $s)
          {
            $name = basename($s);
            echo '<div data-src="'.rawurlencode($name).'" class="song">'.$name.'</div>';
          }
        ?>
      </div>
    </div></br>
  <?php include 'footer.php'; ?>
  </body>
</html>
