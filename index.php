<?php
/*
	Copyright 2013 Alex Z. (mrzmanwiki@gmail.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/
//require_once('../template/template.php');
//templatetop( "Admins willing to make difficult blocks" );
require_once('/home/alexz/commonphp/GlobalFunctions.php');
date_default_timezone_set('UTC');
require( '/home/alexz/commonphp/mysql.php' );
$db = mysql_connect( 'enwiki.labsdb', $my_user, $my_pass );
mysql_select_db( 'enwiki_p', $db );
$res = mysql_query("(SELECT user_name, 
(SELECT MAX(rc_timestamp) FROM recentchanges_userindex WHERE rc_user_text=user_name) AS ts,
(SELECT GROUP_CONCAT(ug_group SEPARATOR ', ') FROM user_groups WHERE ug_user=user_id) AS groups
FROM pagelinks JOIN user ON user_name=REPLACE(SUBSTRING_INDEX(pl_title, '/', 1), '_', ' ') JOIN user_groups ON ug_user=user_id 
WHERE ug_group='sysop' AND pl_namespace IN (2,3) AND pl_from=4679843) 
UNION 
(SELECT user_name, 
(SELECT MAX(rc_timestamp) FROM recentchanges_userindex WHERE rc_user_text=user_name) AS ts,
(SELECT GROUP_CONCAT(ug_group SEPARATOR ', ') FROM user_groups WHERE ug_user=user_id) AS groups
FROM page JOIN categorylinks ON cl_from=page_id JOIN user ON user_name=REPLACE(SUBSTRING_INDEX(page_title, '/', 1), '_', ' ') JOIN user_groups ON ug_user=user_id 
WHERE ug_group='sysop' AND cl_to='Wikipedia_administrators_willing_to_make_difficult_blocks' AND page_namespace IN (2,3)) 
ORDER BY ts DESC", $db);

$now = new DateTime;
		?>
		<p>This page shows a list of admins willing to make difficult blocks, ordered by the time of their last action.</p>
		<p>The list comes from 
<a href='http://en.wikipedia.org/wiki/Wikipedia:Admins_willing_to_make_difficult_blocks' title="Wikipedia:Admins willing to make difficult blocks">Wikipedia:Admins willing to make difficult blocks</a>
and the associated <a href='http://en.wikipedia.org/wiki/Category:Wikipedia_administrators_willing_to_make_difficult_blocks' title='Category:Wikipedia administrators willing to make difficult blocks'>category</a>.</p>
		<p>The "last action" time is the time since their last action logged in recent changes. This page was loaded at <?php
echo $now->format( 'G:i:s, j F Y' );
		?> (UTC).</p>
		<table style="width:60%">
		<tr><th style="width:33%">Admin</th><th style="width:33%">Last action</th><th style="width:34%">User groups</th></tr>
<?php
function dsAppend( &$diffstring, $time, $interval ) {
	if ( $time ) {
		if ( $diffstring ) {
			$diffstring .= ', ';
		}
		$diffstring .= "$time $interval";
		if ( $time > 1 ) {
			$diffstring .= 's';
		}
	}
}
$index = 0;
while ( $row = mysql_fetch_assoc($res) ) {
	$index++;
	$encuser = wfUrlencode( $row['user_name'] );
	$htmluser = htmlspecialchars( $row['user_name'], ENT_QUOTES );
	$userpage = "<a href='http://en.wikipedia.org/wiki/User:$encuser' title='$htmluser'>$htmluser</a>";
	$talk = "<a href='http://en.wikipedia.org/wiki/User_talk:$encuser' title='Talk'>talk</a>";
	$contribs = "<a href='http://en.wikipedia.org/wiki/Special:Contributions/$encuser' title='Contribs'>contribs</a>";
	$email = "<a href='http://en.wikipedia.org/wiki/Special:Emailuser/$encuser' title='Email'>email</a>";
	$userlink = "$userpage ($talk | $contribs | $email)";
	$then = date_create( $row['ts'] );
	$groups = htmlspecialchars( $row['groups'], ENT_QUOTES );
	$diff = $then->diff( $now );
	$diffstring = '';
	dsAppend( $diffstring, $diff->m, 'month' );
	dsAppend( $diffstring, $diff->d, 'day' );
	dsAppend( $diffstring, $diff->h, 'hour' );
	dsAppend( $diffstring, $diff->i, 'minute' );
	dsAppend( $diffstring, $diff->s, 'second' );
	$diffstring ? $diffstring .= ' ago': null;
	$style = '';
	if ( $index%2 == 1 ) {
		$style = ' style="background-color: #E8E8ED;"';
	}
	echo "<tr$style><td>$userlink</td><td>$diffstring</td><td>$groups</td></tr>\n";	
}
echo "</table>";
//templatebottom();