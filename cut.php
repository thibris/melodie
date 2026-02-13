<?php
if(isset($_GET['filename']))
{
	echo "filename: ".$_GET['filename']."<br>";
	echo "time: ".$_GET['time']."<br>";
	#$filename = '/var/www/html/'.$_GET['archive'];
	#rename('/var/www/html/'.$_GET['archive'], '/var/www/html/archive/'.$_GET['archive']);
	#unlink($filename);
	$filename=$_GET['filename'];
	$command='ffmpeg -i '.$filename.' -acodec copy -ss 00:08:15.000 -t 01:00:00.000 cut1_'.$filename;
	$output=null;
	system($command, $output);
}
?>
