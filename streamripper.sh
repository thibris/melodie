#!/bin/sh
date=`date +"%Y_%m_%d_%HH%MM"`
url=https://stream.radiokampus.fm/kampus
output_filename=$1.${date}.mp3
duration=$2
output_dir=/var/www/html

cd $output_dir/originals

curl $url --max-time $duration -o $output_filename
#curl $url --max-time 1 -o $output_filename
ffmpeg -i $output_filename -acodec copy -ss 00:08:15.000 -t 01:05:00.000 $output_dir/$output_filename
chown www-data $output_dir/$output_filename

