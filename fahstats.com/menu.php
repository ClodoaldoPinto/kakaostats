<?php

$query = "select extract(
 epoch from
 (select \"datetime\" from processing_end) +
 '3 hours 3 minutes'::interval -
 current_timestamp(0)
 )::bigint * 1000 as ttu;";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$ttu = $line['ttu'];
$menu = array(
   'anchors' => True,
   'anchors_all_donors' => True,
   'anchors_all_teams' => True,
   'team' => True,
   'team_radar' => True,
   'team_new_members' => True,
   'team_milestones' => True,
   'team_summary' => True,
   'team_monthly' => True,
   'team_members' => True,
   'team_subteam' => True,
   'team_history' => True,
   'donor' => True,
   'donor_history' => True,
   'donor_summary' => True,
   'donor_radar' => True,
   'donor_milestones' => True
   );
$menu[$page_name] = False;
if (substr($page_name, 0, 7) == 'anchors') {
   $menu['donor'] = False;
   $menu['team'] = False;
}
elseif (substr($page_name, 0, 4) == 'team') {
   $menu['donor'] = False;
}
?>
<script type="text/javascript">
var time_to_update = <?php echo $ttu ?>;
var page_time = new Date().getTime();
time_of_update = new Date(page_time + time_to_update);
function timeToUpdate(time_of_update) {
  time_to_update = (time_of_update.getTime() -
    new Date().getTime()) / 1000;
  if (time_to_update < 0) time_to_update = 0;
  var hours_to_update = Math.floor(time_to_update / 3600);
  var minutes_to_update = Math.floor(
    (time_to_update - hours_to_update * 3600) / 60);
  var seconds_to_update = Math.floor(time_to_update) -
    hours_to_update * 3600 -  minutes_to_update * 60;
  return(hours_to_update + ':' +
    zd(minutes_to_update) + ':' + zd(seconds_to_update));
}
</script>
<table cellspacing="0" style=
  "width:100%;margin:0 0 0.5em;padding:0;">
<tr>
<td style="padding:0;margin:0;width:10em;">
<a href="/">
  <img src="/img/kakaostats.png"
    width="225"
    height="60"
    alt="KakaoStats"
    title="KakaoStats"
    style="margin:0;vertical-align:top;"
    >
  </a>
</td>
<td style="padding:0 0 0 4em;text-align:left;
  margin:0;background:url(/img/slice.png);">
<p style="margin:0;font-size:11px;padding:0;">
<span style="background:blanchedAlmond;padding:0 1em;">
Next update countdown
<span id="ttu" style="margin-left:0.5em;"></span>
</span>
</p>
<ul id="nav">
<li><a <?php if (!$menu['anchors'])
    echo 'class="disabled"';?>>Anchors</a>
  <ul>
    <li><a
      <?php if (!$menu['anchors_all_teams'])
      echo ' class="disabled"';
      else echo 'href="/"' ?>
      >All Teams</a></li>
    <li><a
      <?php if (!$menu['anchors_all_donors'])
      echo ' class="disabled"';
      else echo 'href="/ad.php"' ?>
      >All Donors</a></li>
  </ul>
</li>
<li><a
    <?php if (!$menu['team']) echo ' class="disabled"'; ?>
    >Team</a>
  <ul>
    <li><a
      <?php if (!$menu['team'] || !$menu['team_summary'])
      echo ' class="disabled"';
      else echo 'href="/tsum.php?t='.$team.'"' ?>
      >Summary</a></li>
    <li><a
      <?php if (!$menu['team'] || !$menu['team_members'])
      echo ' class="disabled"';
      else echo 'href="/t.php?t='.$team.'"' ?>
      >Members</a></li>
    <li><a
      <?php if (!$menu['team'] || !$menu['team_history'])
      echo ' class="disabled"';
      else echo 'href="/tp.php?t='.$team.'"' ?>
      >History</a></li>
    <li><a
      <?php if (!$menu['team'] || !$menu['team_radar'])
      echo ' class="disabled"';
      else echo 'href="/tr.php?t='.$team.'"' ?>
      >Radar</a></li>
    <li><a
      <?php if (!$menu['team'] || !$menu['team_new_members'])
      echo ' class="disabled"';
      else echo 'href="/ts.php?t='.$team.'"' ?>
      >Size</a></li>
    <li><a
      <?php if (!$menu['team'] || !$menu['team_monthly'])
      echo ' class="disabled"';
      else echo 'href="/mrt.php?t='.$team.'"' ?>
      >Monthly</a></li>
    <li><a
      <?php if (!$menu['team'] || !$menu['team_subteam'])
      echo ' class="disabled"';
      else echo 'href="/subteam.php?t='.$team.'"' ?>
      >Subteams</a></li>
    <li><a
      <?php if (!$menu['team'] || !$menu['team_milestones'])
      echo ' class="disabled"';
      else echo 'href="/tm.php?t='.$team.'"' ?>
      >Milestones</a></li>
  </ul>
</li>
<li><a
    <?php if (!$menu['donor']) echo ' class="disabled"'; ?>
    >Donor</a>
  <ul>
    <li><a
      <?php if (!$menu['donor'] || !$menu['donor_summary'])
      echo ' class="disabled"';
      else echo 'href="/usum.php?u='.$donor.'"' ?>
      >Summary</a></li>
    <li><a
      <?php if (!$menu['donor'] || !$menu['donor_history'])
      echo ' class="disabled"';
      else echo 'href="/up.php?u='.$donor.'"' ?>
      >History</a></li>
    <li><a
      <?php if (!$menu['donor'] || !$menu['donor_radar'])
      echo ' class="disabled"';
      else echo 'href="/u.php?u='.$donor.'"' ?>
      >Radar</a></li>
    <li><a
      <?php if (!$menu['donor'] || !$menu['donor_milestones'])
      echo ' class="disabled"';
      else echo 'href="/dm.php?u='.$donor.'"' ?>
      >Milestones</a></li>
  </ul>
</li>
<li>
  <a>Links</a>
  <ul>
    <li><a href="http://forum.kakaostats.com/">Forum</a></li>
    <li><a href="http://fahwiki.net/">Wiki</a></li>
    <li><a href="http://foldingforum.org/">Community</a></li>
  </ul>
</li>
</ul>
</td>
<td style="background:url(/img/slice.png);">
<a href="http://flattr.com/thing/396092/KakaoStats" target="_blank">
<img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a>
</td>
</tr>
</table>
