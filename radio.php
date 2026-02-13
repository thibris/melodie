<!DOCTYPE html>
<html>
    <head>
        <title>MMN</title>
        <link rel="icon" href="favicon.ico" type="image/x-icon">
    </head>
    <body>
        <?php
        include 'header.php';
        echo "<h1>Melodie:</h1>";
        foreach (glob("melodie*.mp3") as $filename)
        {
            $basename = pathinfo($filename, PATHINFO_FILENAME).".txt";
            if (file_exists($basename))
            {
                $file = fopen($basename, "r") or die("aUnable to open file!");
                $playlist=fread($file, filesize($basename));
                fclose($file);
            }
            else {$playlist='';}
            echo "<a href=$filename download='' title='$playlist'>$filename</a><br>";
        }
        echo '<br>';
        include 'footer.php';
        ?>
    </body>
</html>
