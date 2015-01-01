/*
 * Copyright (C) 2006 Clodoaldo Pinto Neto <cpn@fahstats.com>
 * http://fahstats.com http://forum.fahstats.com
 *
 * This file is part of fahstats.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */
function ron(id) {
  ruleLineColor = id.style.backgroundColor;
  id.style.backgroundColor = "#ffd800";
  return;
  }
function roff(id) {
  id.style.backgroundColor = ruleLineColor;
  return;
  }
function scrollMe(event) {
  var st = event.currentTarget.scrollTop + (event.detail * 12);
  event.currentTarget.scrollTop = st < 0 ? 0 : st;
  event.preventDefault();
  }
function mOverCab(cab) {
  tira_cor();
  cab.style.backgroundColor="#ffd800";
  cab.style.cursor="pointer";
  }
function mOutCab(cab) {
  coloca_cor();
  if (cab.id != 'th' + th_classificado) cab.style.backgroundColor="";
  cab.style.cursor="";
  }
function tira_cor() {
  document.getElementById( "th" + th_classificado ).style.backgroundColor="";
  }
function coloca_cor() {
  document.getElementById( "th" + th_classificado ).style.backgroundColor="#ffd800";
  }
function zd(numero) {
  return (numero<10 ? "0" + numero : numero);
  }
function timeZone() {
  var data = new Date();
  var timeZone = data.getTimezoneOffset() / 60;
  if (timeZone > 0) var sinalTimeZone = "-";
  else if (timeZone < 0) var sinalTimeZone = "+";
  else return ("UTC");
  return ("UTC" + sinalTimeZone + Math.abs(timeZone));
  }
function wDate (date) {
  var year = date.substring(0, 4);
  var month = date.substring(5, 7);
  var day = date.substring(8, 10);
  var hour = date.substring(11, 13);
  var minute = date.substring(14, 16);
  var second = 0; //date.substring(17, 19);
  var data = new Date(Date.UTC(year, month -1, day, hour, minute, second));
  return (
    data.getHours() + ":" +
    zd(data.getMinutes())/* + " " +
    data.getFullYear() + "-" +
    zd(data.getMonth() +1) + "-" +
    zd(data.getDate())*/
    );
  }
function wDate2 (date) {
  var year = date.substring (0, 4);
  var month = date.substring (5, 7);
  var day = date.substring (8, 10);
  var hour = date.substring (11, 13);
  var minute = date.substring (14, 16);
  var data = new Date(Date.UTC(year, month -1, day, hour, minute));
  return (
    data.getFullYear() + "-" +
    zd(data.getMonth() +1) + "-" +
    zd(data.getDate()) + " " +
    zd(data.getHours()) + ":" +
    zd(data.getMinutes())
    );
  }
sfHover = function() {
 var sfEls = document.getElementById("nav").getElementsByTagName("li");
 for (var i=0; i<sfEls.length; i++) {
   sfEls[i].onmouseover=function() {
     this.className+=" sfhover";
   }
   sfEls[i].onmouseout=function() {
     this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
   }
 }
}
if (window.attachEvent) window.attachEvent("onload", sfHover);

if (!window.Node) {
  var Node = {
    ELEMENT_NODE: 1,
    ATTRIBUTE_NODE: 2,
    TEXT_NODE: 3,
    COMMENT_NODE: 8,
    DOCUMENT_NODE: 9,
    DOCUMENT_FRAGMENT_NODE: 11
  };
}
function ca() {
  d = [];
  d[0] = document.getElementById('g_div_0').childNodes;
  d[1] = document.getElementById('g_div_1').childNodes;
  
  e = 0;
  divs = document.getElementsByTagName('div');
  for (var i = 0; i < divs.length; i++) {
    if (divs[i].id.substring(0,6) == 'ad_div') {
      for (var k = 0; k < d[e].length; k++) {
        divs[i].appendChild(d[e][k].cloneNode(true));
      }
    }
    if (e++ == 1) e = 0;
  }
}
