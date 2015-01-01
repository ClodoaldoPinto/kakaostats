#!/usr/bin/python

import time
import psycopg2 as db, sys, os
path = '/fahstats/scripts/python'
if path not in sys.path:
   sys.path.append(path)
from setup import connStr

print time.asctime(),
try:
   db.connect(connStr['teste'])
   print 'conectou'
except:
   os.system('/sbin/service pgpool stop')
   print 'parou',
   time.sleep(20)
   os.system('/sbin/service pgpool start')
   print 'iniciou'
