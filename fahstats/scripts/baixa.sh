#!/bin/bash

arq1="daily_team_summary.txt.bz2"
arq2="daily_user_summary.txt.bz2"
save_dir="/var/www/html/fahstats.com/arquivos"
for arquivo in "$arq1" "$arq2"
do
   echo $arquivo
   modTime=$(ls -l $save_dir/$arquivo --time-style=long-iso | grep -oE [0-9]{4}-[0-9]{2}-[0-9]{2}[[:space:]][0-9]{2}:[0-9]{2})
   resultado=1
   while [ $resultado -eq 1 ]
   do
      wget --wait=5 --random-wait --verbose --output-file=$save_dir/$arquivo.log --timestamping  --directory-prefix=$save_dir fah-web.stanford.edu/$arquivo
      if [ $? -eq 0 ]
      then
         modTime1=$(ls -l $save_dir/$arquivo --time-style=long-iso | grep -oE [0-9]{4}-[0-9]{2}-[0-9]{2}[[:space:]][0-9]{2}:[0-9]{2})
         let resultado=0
         if [ "$modTime" = "$modTime1" ]
         then
            let resultado=1
            sleep 30
         else
            bzip2 -t $save_dir/$arquivo
            if [ $? -ne 0 ]
               then
               sleep 10
               let resultado=1
            fi
         fi
      fi
   done
done
