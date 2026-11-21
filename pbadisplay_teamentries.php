<?php
//Rev 5 21/8/2026 - added numberof documents to document button
//this page is called either directly or via javascript from the team activity page
//this page creates the table of results to display in that page
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	session_unset();
	session_destroy();
	header('Location: pbalogin.php?disp=1');
}

require('../connecttopba.php');

$getyear = (isset($_GET['yid'])) ? (int) $_GET['yid'] : $ty;

$qyear = "SELECT YearText FROM years WHERE YearId = ?";
$stmt = mysqli_prepare($link, $qyear);
mysqli_stmt_bind_param($stmt, "i", $getyear);
mysqli_stmt_execute($stmt);
$ryear = mysqli_stmt_get_result($stmt);

if (!$ryear) 
{
	die('Year query failed: ' . mysqli_error($link));
}
$yeartext = mysqli_fetch_assoc($ryear);

$qteam = "SELECT
te.TeamEntryId,
l.LeagueName,
s.SeasonName,
gr.GradeName,
ge.GenderName,
d.DivisionName,
t.TeamName 
FROM teamentries te
JOIN leagues l ON te.LeagueId = l.LeagueId
JOIN seasons s ON te.SeasonId = s.SeasonId
JOIN grades gr ON te.GradeId = gr.GradeId
JOIN genders ge ON te.GenderId = ge.GenderId
JOIN divisions d ON te.DivisionId = d.DivisionId
JOIN teamnames t ON te.TeamNameId = t.TeamNameId
 WHERE te.YearId = ?
 ORDER BY te.TeamEntryId";
$stmt = mysqli_prepare($link, $qteam);
mysqli_stmt_bind_param($stmt, "i", $getyear);
mysqli_stmt_execute($stmt);
$rteam = mysqli_stmt_get_result($stmt);
$numberofrows = mysqli_num_rows($rteam);
echo '<p> </p><table width="90%">';
	if($_SESSION['accesslevel'] > 3) // God access shows delete button column
	{
		echo '<tr><th>League</th><th>Season</th><th>Grade</th><th>Gender</th><th>Division</th><th>Name</th><th>Add/Delete</th></tr>';
	}
	elseif($_SESSION['accesslevel'] > 2)
	{
		echo '<tr><th>League</th><th>Season</th><th>Grade</th><th>Gender</th><th>Division</th><th>Name</th></tr>';
	}

if (mysqli_num_rows($rteam) > 0) 
{
	

		while ($team = mysqli_fetch_assoc($rteam)) 
		{
			if($_SESSION['accesslevel'] > 3) // God access shows delete buton
			{
				echo '<tr><td>'.$team['LeagueName'].'</td><td>'.$team['SeasonName'].'</td>
					<td>'.$team['GradeName'].'</td><td>'.$team['GenderName'].'</td><td>'.$team['DivisionName'].'</td><td>'.$team['TeamName'].'</td>
					<td><a onclick="return confirm(\'Are you sure you wish to delete '.$team['LeagueName'].' record?\');" class="buttonlink" href="pbadeleterecords.php?teid='.$team['TeamEntryId'].'&yid='.$getyear.'">
					Delete</a></td></tr>';
			}
			elseif($_SESSION['accesslevel'] > 2)
			{
				echo '<tr><td>'.$team['LeagueName'].'</td><td>'.$team['SeasonName'].'</td>
					<td>'.$team['GradeName'].'</td><td>'.$team['GenderName'].'</td><td>'.$team['DivisionName'].'</td><td>'.$team['TeamName'].'</td>
					</tr>';
			}
		}
}

$qdata = "SELECT
LeagueId,
SeasonId,
GradeId,
GenderId,
DivisionId,
TeamNameId
FROM teamentries te
 WHERE te.YearId = ?
 ORDER BY te.TeamEntryId";
$stmt = mysqli_prepare($link, $qdata);
mysqli_stmt_bind_param($stmt, "i", $getyear);
mysqli_stmt_execute($stmt);
$rdata = mysqli_stmt_get_result($stmt);
$numberofrows = mysqli_num_rows($rdata);

if($numberofrows > 0)
{
	$x = mysqli_data_seek($rdata, $numberofrows-1); //goto end of results
	$lastrow = mysqli_fetch_assoc($rdata);
}

echo '<tr><td>';
echo '<select name="addleague" id="addleague">';

// get all leagues for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM leagues WHERE Status = "Active" ORDER BY LeagueId';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build leagues combo box from above query
while($pbaleagues = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$leagueid = $pbaleagues['LeagueId'];
	$pbaleague = $pbaleagues['LeagueName'];
	$sticky = (isset($lastrow) && $leagueid == $lastrow['LeagueId']) ? ' selected="selected"' : "";
	{
		echo '<option value = "' . $leagueid . '"'.$sticky.'>' . $pbaleague . '</option>';
	}
}

echo '</select></td>';
echo '<td>';
echo '<select name="addseason" id="addseason">';

// get all seasons for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM seasons WHERE Status = "Active" ORDER BY SeasonId';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build seasons combo box from above query
while($pbaseasons = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$seasonid = $pbaseasons['SeasonId'];
	$pbaseason = $pbaseasons['SeasonName'];
	$sticky = (isset($lastrow) && $seasonid == $lastrow['SeasonId']) ? ' selected="selected"' : "";
	{
		echo '<option value = "' . $seasonid . '"'.$sticky.'>' . $pbaseason . '</option>';
	}
}

echo '</select></td>';
echo '<td>';
echo '<select name="addgrade" id="addgrade">';

// get all grades for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM grades WHERE Status = "Active" ORDER BY GradeId';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build grades combo box from above query
while($pbagrades = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$gradeid = $pbagrades['GradeId'];
	$pbagrade = $pbagrades['GradeName'];
	$sticky = (isset($lastrow) && $gradeid == $lastrow['GradeId']) ? ' selected="selected"' : "";
	{
		echo '<option value = "' . $gradeid . '"'.$sticky.'>' . $pbagrade . '</option>';
	}
}

echo '</select></td>';
echo '<td>';
echo '<select name="addgender" id="addgender">';

// get all genders for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM genders WHERE Status = "Active" ORDER BY GenderId';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build genders combo box from above query
while($pbagenders = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$genderid = $pbagenders['GenderId'];
	$pbagender = $pbagenders['GenderName'];
	$sticky = (isset($lastrow) && $genderid == $lastrow['GenderId']) ? ' selected="selected"' : "";
	{
		echo '<option value = "' . $genderid . '"'.$sticky.'>' . $pbagender . '</option>';
	}
}

echo '</select></td>';
echo '<td>';
echo '<select name="adddivision" id="adddivision">';

// get all divisions for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM divisions WHERE Status = "Active" ORDER BY DivisionId';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build divisions combo box from above query
while($pbadivisions = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$divisionid = $pbadivisions['DivisionId'];
	$pbadivision = $pbadivisions['DivisionName'];
	$sticky = (isset($lastrow) && $divisionid == $lastrow['DivisionId']) ? ' selected="selected"' : "";
	{
		echo '<option value = "' . $divisionid . '"'.$sticky.'>' . $pbadivision . '</option>';
	}
}

echo '</select></td>';
echo '<td>';
echo '<select name="addteamname" id="addteamname">';

// get all teamnames for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM teamnames WHERE Status = "Active" ORDER BY TeamNameId';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build teamnames combo box from above query
while($pbateamnames = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$teamnameid = $pbateamnames['TeamNameId'];
	$pbateamname = $pbateamnames['TeamName'];
	$sticky = (isset($lastrow) && $teamnameid == $lastrow['TeamNameId']) ? ' selected="selected"' : "";
	{
		echo '<option value = "' . $teamnameid . '"'.$sticky.'>' . $pbateamname . '</option>';
	}
}

echo '</select></td>';
echo '<td><input type="submit" value="Add Team" name="addnewteam"></td>';
echo '</tr></table>';	

?>
