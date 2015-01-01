#!/usr/bin/env python

import psycopg2 as dbd, setup
from urllib import urlopen

query = """
select n_time
from teams_production
where active_members >= 50 and n_time not in (-1)
order by active_members desc
;"""

def main():

    connection = dbd.connect(setup.connStr["backend"])
    cursor = connection.cursor()
    cursor.execute(query)
    linhas = cursor.fetchall()
    
    for linha in linhas:
      team_number = linha[0]
      print team_number
#      urlopen('http://kakaostats.com/tsum.php?t=%s' % team_number).read()

    cursor.close()
    connection.close()

main()
