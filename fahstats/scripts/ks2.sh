#!/bin/bash
echo "----------------------------------------------------------------------"
date
echo 'baixa.sh'
/fahstats/scripts/baixa.sh
date
cp -p /var/www/html/fahstats.com/arquivos/daily_team_summary.txt.bz2 /fahstats/bak/$(ls -l --time-style=+%y-%m-%d-%H:%M /var/www/html/fahstats.com/arquivos/daily_team_summary.txt.bz2 | grep -P -o "\d\d-\d\d-\d\d-\d\d:\d\d")_team.txt.bz2
cp -p /var/www/html/fahstats.com/arquivos/daily_user_summary.txt.bz2 /fahstats/bak/$(ls -l --time-style=+%y-%m-%d-%H:%M /var/www/html/fahstats.com/arquivos/daily_user_summary.txt.bz2 | grep -P -o "\d\d-\d\d-\d\d-\d\d:\d\d")_user.txt.bz2
