#!/bin/bash
cd /fahstats/scripts
tail -n 50000 kakaoStats.log > tmpfile
cat tmpfile > kakaoStats.log
tail -n 5000 fahstats.sql.Err.log > tmpfile
cat tmpfile > fahstats.sql.Err.log
rm tmpfile
