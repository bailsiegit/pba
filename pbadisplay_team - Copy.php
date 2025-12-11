<?php
//Rev 1 19/11/2025
//this page is called either directly or via javascript from the team activity page
//this page creates the table of results to display in that page
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');

$getyear = (isset($_GET['yid'])) ? (int) $_GET['yid'] : $ty;
$getteam = (isset($_GET['tid'])) ? (int)$_GET['tid'] : $tn;

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

$qteam = "SELECT TeamName FROM teams WHERE TeamId = ?";
$stmt = mysqli_prepare($link, $qteam);
mysqli_stmt_bind_param($stmt, "i", $getteam);
mysqli_stmt_execute($stmt);
$rteam = mysqli_stmt_get_result($stmt);
$teamname = mysqli_fetch_assoc($rteam);
   
$teamQuery = "SELECT rl, gp, tn, mbid, members.FirstName, members.LastName, tmid, yrid FROM
	(SELECT Role, GamesPlayed, teams.TeamName, MembId, teammembers.TeamId, teammembers.Year FROM ((teammembers 
	INNER JOIN years ON teammembers.Year = years.YearId)
	INNER JOIN teams ON teammembers.TeamId = teams.TeamId) WHERE teammembers.Year = ? AND teammembers.TeamId = ?) 
	teamdata (rl, gp, tn, mbid, tmid, yrid) 
	INNER JOIN members ON teamdata.mbid = members.MemberID";
$stmt = mysqli_prepare($link, $teamQuery);
mysqli_stmt_bind_param($stmt, "ii", $getyear, $getteam);
mysqli_stmt_execute($stmt);
$teamResult = mysqli_stmt_get_result($stmt);

if (!$teamResult) 
{
	die('Team query failed: ' . mysqli_error($link));
}
if (mysqli_num_rows($teamResult) > 0) 
{	
	echo '<table style="width:100%;"><tr>';
	echo '<td style="width:50%; background:white; border:0px;"><h4>'.$yeartext['YearText'].' - '.$teamname['TeamName'].'</h4></td>';
	echo '<td style="width:50%; background:white; border:0px;">
	<a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbadocslist.php?yid='.$getyear.'&actid=tm&refid='.$getteam.'">Team Documents</a>';
	if($_SESSION['accesslevel'] > 3)
	{
		echo '   <a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbacopyteam.php?yid='.$getyear.'&actid='.$getteam.'">Copy Team</a>';
	}
	echo '</td></tr></table>';
	
	echo '<p> </p><table width="90%">';
	if($_SESSION['accesslevel'] > 3) // God access shows delete button column
	{
		echo '<tr><th width="40%">Name</th><th width="40%">Role</th><th>Games (click to add)</th><th>Delete</th></tr>';
	}
	elseif($_SESSION['accesslevel'] > 2)
	{
		echo '<tr><th width="40%">Name</th><th width="40%">Role</th><th>Games (click to add)</th></tr>';
	}
	else
	{
		echo '<tr><th width="40%">Name</th><th width="40%">Role</th><th>Games</th></tr>';
	}

		while ($team = mysqli_fetch_assoc($teamResult)) 
		{
			if($_SESSION['accesslevel'] > 3) // God access shows delete buton
			{
				echo '<tr><td><a href="pbaperson.php?pid='.$team['mbid'].'">'.$team['FirstName'].' '.$team['LastName'].'</a></td>
					<td>'.$team['rl'].'</td>
					<td><a href="pbaaddgames.php?pid='.$team['mbid'].'&tid='.$getteam.'&yid='.$getyear.'&gp='.$team['gp'].'">'.$team['gp'].'</a></td>
					<td><a onclick="return confirm(\'Are you sure?\');" class="buttonlink" href="pbadeleterecords.php?pid='.$team['mbid'].'&tid='.$team['tmid'].'&yid='.$team['yrid'].'">
					Delete</a></td></tr>';
			}
			elseif($_SESSION['accesslevel'] > 2)
			{
				echo '<tr><td><a href="pbaperson.php?pid='.$team['mbid'].'">'.$team['FirstName'].' '.$team['LastName'].'</a></td><td>'.$team['rl'].'</td>
					<td><a href="pbaaddgames.php?pid='.$team['mbid'].'&tid='.$getteam.'&yid='.$getyear.'&gp='.$team['gp'].'">'.$team['gp'].'</a>
					</td></tr>';
			}
			else
			{
				echo '<tr><td><a href="pbaperson.php?pid='.$team['mbid'].'">'.$team['FirstName'].' '.$team['LastName'].'</a></td><td>'.$team['rl'].'</td>
					<td>'.$team['gp'].'</td></tr>';
			}
		}
	echo '</table>';	
}
?>
