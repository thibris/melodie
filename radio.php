<!DOCTYPE html>
<html>
<head>
<title>MMN</title>
<script type="text/javascript">
  function ajax(filename, func_name)
  {
    fetch("ajax.php?" + func_name + "=" + filename)
    .then(response => {
        location.reload()
      })
  }
</script>

</head>
<body>
<?php
echo "Melodie:<br>";
foreach (glob("melodie*.mp3") as $filename)
{
  echo "<div class='".explode(".",$filename)[0]."".explode(".",$filename)[1]."'><a href=$filename>$filename</a>";
  echo "<span onclick=\"ajax('".$filename."', 'remove')\"> - X</span></div>";
}
echo '<br>';
include 'footer.php';
?>
</body>
