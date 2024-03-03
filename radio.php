<!DOCTYPE html>
<html>
<head>
<title>MMM</title>
<script type="text/javascript">
  function archive(filename)
  {
    fetch("ajax.php?archive=" + filename)
    .then(response => {
        var str = filename.split('.');
	var elem = document.querySelector("div." + str[0] + str[1]);
        elem.remove()
      })
  }
  function cut(filename)
  {
    fetch("ajax.php?cut=" + filename)
    .then(response => {
        location.reload()
      })
  }
</script>


</head>
<body>
<?php
function getSymbolByQuantity($bytes) {
  $symbols = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
  $exp = floor(log($bytes)/log(1024));
  return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));
}

echo "Melodie:";
foreach (glob("melodie*.mp3") as $filename)
{
  echo "<div class='".explode(".",$filename)[0]."".explode(".",$filename)[1]."'><a href=$filename>$filename - ".getSymbolByQuantity(filesize($filename))."</a>";
#  echo "<span onclick=\"cut('".$filename."')\"> - V</span>";
  echo "<span onclick=\"archive('".$filename."')\"> - X</span></div><br>";
}

echo "Darkside:";
foreach (glob("darkside*.mp3") as $filename)
{
  echo "<div class='".explode(".",$filename)[0]."".explode(".",$filename)[1]."'><a href=$filename>$filename - ".getSymbolByQuantity(filesize($filename))."</a>";
#  echo "<span onclick=\"cut('".$filename."')\"> - V</span>";
  echo "<span onclick=\"archive('".$filename."')\"> - X</span></div><br>";
}

$df = disk_free_space("/");
$ds = disk_total_space("/");
echo "total/free: ".getSymbolByQuantity($ds)."/".getSymbolByQuantity($df);
?>
</body>
