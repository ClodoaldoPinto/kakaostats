import psycopg2 as db
import os, sys, subprocess as sub
sys.path.append("/fahstats/scripts/python")
from setup import connStr

connection = db.connect(connStr["backend"])
cursor = connection.cursor()

query = """
select extract(hour from current_timestamp)::integer;
"""    
cursor.execute(query)
hour = cursor.fetchall()[0][0]
connection.close()

if hour in (1, 2, 3):
   #sub.call(["psql", "-e", "-U kakaostats", "-c vacuum", "fahstats", "2>> /fahstats/scripts/vacuum.Err.log"])
   os.system('psql -e -U kakaostats -c "vacuum" fahstats 2>> /fahstats/scripts/vacuum.Err.log')
   #sub.call(["pgdump", "-U kakaostats", "-C", "fahstats", "> /fahstats/bak/dump/fahstats.dump", "2>> /fahstats/scripts/dump.Err.log"])
   os.system('pg_dump -U kakaostats -C fahstats > /fahstats/bak/dump/fahstats.dump 2>> /fahstats/scripts/dump.Err.log')
   
os.system('psql -e -U kakaostats -c "analyze" fahstats')
