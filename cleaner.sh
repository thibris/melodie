#!/bin/sh
output_dir=/var/www/html

cd $output_dir/originals
winner=`ls -S | head -n 1`

ffmpeg -i $winner -acodec copy -ss 00:08:15.000 -t 01:00:00.000 $output_dir/${winner:2}
rm -rf $output_dir/originals/*
