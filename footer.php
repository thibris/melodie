<?php
  function getSymbolByQuantity($bytes) {
    $symbols = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
    $exp = floor(log($bytes)/log(1024));
    return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));
  }

  $df = disk_free_space("/");
  $dt = disk_total_space("/");
  $used = (($dt - $df) / $dt) * 100;
  $free = ($df / $dt) * 100;
  echo '<div id="container" style="width:450px;">';
  echo '<div style="float:left;  text-align: center; background-color:red;   width:'.$used.'%">'.round($used, 1).'%</div>';
  echo '<div style="float:right; text-align: center; background-color:green; width:'.$free.'%">'.round($free, 1).'%</div>';
  echo '</div></br>'.getSymbolByQuantity(($dt-$df)).' used / '.getSymbolByQuantity($df).' free';
?>
