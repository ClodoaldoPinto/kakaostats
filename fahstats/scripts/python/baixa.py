#! /usr/bin/env python
#
# Copyright (C) 2006 Clodoaldo Pinto Neto <cpn@fahstats.com>
# http://fahstats.com http://forum.fahstats.com
#
# This file is part of fahstats.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2,
# or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
#
import pycurl, StringIO, re, time, os, subprocess, sys

def time_str2int(strtime):
   time_tuple = time.strptime(strtime, '%a, %d %b %Y %H:%M:%S %Z')
   return int(time.mktime(time_tuple))

def aguarda_update(url, file_path):

   try:
      file_mtime = os.stat(file_path)[8]
   except OSError:
      file_mtime = 0

   b = StringIO.StringIO()
   c = pycurl.Curl()
   c.setopt(pycurl.URL, url)
   c.setopt(pycurl.HEADER, True)
   c.setopt(pycurl.NOBODY, True)
   c.setopt(pycurl.WRITEFUNCTION, b.write)
   while True:
      c.perform()
      sio = b.getvalue()
      b.seek(0)
      b.truncate()
      pat = r'^Last-Modified:\s*(.*)$'
      last_modified = (re.findall(pat, sio, re.M + re.I)[0]).strip()
      last_modified = time_str2int(last_modified)
      if last_modified > file_mtime:
         b.close()
         c.close()
         print
         break
      time.sleep(10)

   print sio
   print """\
   HEAD
   Time: %s
   File time: %s
   Last modified: %s
   Difference min: %s
   """ % (time.ctime(), file_mtime, last_modified,
      (last_modified - file_mtime) / 60)

   pat = r'^Content-Length:\s*(.*)$'
   size = int((re.findall(pat, sio, re.M + re.I)[0]).strip())
   return size, last_modified
   
def get_file(size, url):
   
   if size < 50000: chunk_number = 1
   elif size < 1024 * 1024: chunk_number = 10
   else: chunk_number = 20
   chunk_size = size / chunk_number

   m = pycurl.CurlMulti()
   c = list()
   b = list()
   for i in range(chunk_number):
      start = chunk_size * i
      if i < chunk_number -1:
         end = start + chunk_size -1
      else:
         end = ''
      b.append(StringIO.StringIO())
      c.append(pycurl.Curl())
      c[i].setopt(pycurl.HTTPHEADER, ['Range: bytes=%s-%s' % (start, end)])
      c[i].setopt(pycurl.URL, url)
      c[i].setopt(pycurl.WRITEFUNCTION, b[i].write)
      m.add_handle(c[i])
   
   start = time.time()
   while True:
      ret, num_handles = m.perform()
      if ret != pycurl.E_CALL_MULTI_PERFORM: break
   while num_handles:
      ret = m.select(1.0)
      if ret == -1: continue
      while True:
         ret, num_handles = m.perform()
         if ret != pycurl.E_CALL_MULTI_PERFORM: break
   m.close()

   total_time = time.time() - start

   print """\
   GET
   Time: %s
   URL: %s
   Chunks: %s
   Download size: %s
   Total time seconds: %.2f
   KBytes/s: %.2f
   """ % (time.ctime(), url, chunk_number, size,
      total_time, size / total_time / 1024)

   return ''.join((x.getvalue() for x in b))

def download(server, file_name, file_dir):
   
   while True:
      file_path = os.path.join(file_dir, file_name)
      url = 'http://%s/%s' % (server, file_name)
      size, last_modified = aguarda_update(url, file_path)
      sio = get_file(size, url)
      open(file_path, 'wb').write(sio)
      os.utime(file_path, (last_modified, last_modified))
      print 'file size: %s download_size: %s difference: %s' \
         % (size, len(sio), size - len(sio))
      retcode = subprocess.call(['bzip2', '-t', file_path])
      if retcode == 0: break
      print 'Corrupted file'
      os.unlink(file_path)
   print '-' * 40

def main():
   
   file_dir = '/fahstats/arquivos'
   sys.stderr = open(os.path.join(file_dir,'download.log'), 'wb')
   sys.stdout = sys.stderr
   server = 'fah-web.stanford.edu'

   file_name = 'daily_team_summary.txt.bz2'
   download(server, file_name, file_dir)
      
   file_name = 'daily_user_summary.txt.bz2'
   download(server, file_name, file_dir)

main()

