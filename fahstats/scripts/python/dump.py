#!/usr/bin/env python

import os, subprocess as sub

#os.system('pg_dump -U kakaostats -C fahstats > /fahstats/bak/dump/fahstats.dump 2>> /fahstats/scripts/vacuum.Err.log')
sub.call(["pg_dump",
          "-U kakaostats",
          "-C", "fahstats",
          "-f /fahstats/bak/dump/fahstats.dump",
          "2>> /fahstats/scripts/dump.Err.log"])
