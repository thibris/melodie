<?php
if(isset($_GET['archive']))
{
	#$filename = '/var/www/html/'.$_GET['archive'];
	rename('/var/www/html/'.$_GET['archive'], '/var/www/html/archive/'.$_GET['archive']);
	#unlink($filename);
}
if(isset($_GET['cut']))
{
        echo "filename: ".$_GET['cut']."<br>";
        $filename=$_GET['cut'];
	$command='ffmpeg -i '.$filename.' -acodec copy -ss 00:08:10.000 -t 01:05:00.000 cut_'.$filename;
        $output=null;
	system($command, $output);
	rename('/var/www/html/'.$filename, '/var/www/html/originals/'.$filename);
}
?>
