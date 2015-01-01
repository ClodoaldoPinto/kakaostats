#!/usr/bin/python

import psycopg2 as db
import os, sys, subprocess as sub
path = '/fahstats/scripts/python'
if path not in sys.path:
   sys.path.append(path)
from setup import connStr

try:
   db.connect(connStr['frontend'])
   print 'passou'
except:
   os.system('pgpool restart -i')
   raise
