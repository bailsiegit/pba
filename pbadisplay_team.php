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

$qteam = 'SELECT 
	CONCAT(
	y.YearText, " - ",
	tt.TeamName) AS TeamName
	FROM teamentries te
	JOIN teamstext tt ON te.TeamEntryId = tt.TeamEntryId
	JOIN years y ON te.YearId = y.YearId
	WHERE te.TeamEntryId = ?';
$stmt = mysqli_prepare($link, $qteam);
mysqli_stmt_bind_param($stmt, "i", $getteam);
mysqli_stmt_execute($stmt);
$rteam = mysqli_stmt_get_result($stmt);
$teamname = mysqli_fetch_assoc($rteam);
$fullteamname = $teamname['TeamName'];

//get any team status records
$qstatus = "SELECT tro.TeamResultText FROM teamresultoptions tro
INNER JOIN teamresults tr
ON tr.TeamResultId = tro.TeamResultId WHERE tr.TeamId = ? ORDER BY tr.TeamResultId";
$stmt = mysqli_prepare($link, $qstatus);
mysqli_stmt_bind_param($stmt, "i", $getteam);
mysqli_stmt_execute($stmt);
$rresults = mysqli_stmt_get_result($stmt);

//get team awards
$qawards = "SELECT a.AwardName, m.FirstName, m.LastName
FROM awards a
JOIN awardwinners aw ON a.AwardID = aw.AwardID
JOIN members m ON aw.MembId = m.MemberID
WHERE aw.TeamId = ? ORDER BY a.DisplayOrder";
$stmt = mysqli_prepare($link, $qawards);
mysqli_stmt_bind_param($stmt, "i", $getteam);
mysqli_stmt_execute($stmt);
$rawards = mysqli_stmt_get_result($stmt);

//get team members   
$teamQuery = "SELECT
tm.TeamMembId,
tm.Role, 
tm.GamesPlayed, 
m.MemberID, 
m.FirstName, 
m.LastName 
FROM teammembers tm
JOIN members m ON tm.MembId = m.MemberID
WHERE tm.TeamId = ?";
$stmt = mysqli_prepare($link, $teamQuery);
mysqli_stmt_bind_param($stmt, "i", $getteam);
mysqli_stmt_execute($stmt);
$teamResult = mysqli_stmt_get_result($stmt);
//if(empty($teamResult))
//{
//	$_SESSION['emptyteam'] = true;
//}
//else
//{
//	$_SESSION['emptyteam'] = false;
//}
if (!$teamResult) 
{
	die('Team query failed: ' . mysqli_error($link));
}
//is there any documents attached to the team
$docsQuery = "SELECT DocName FROM Documents
WHERE Activity = 'tm' AND ActivityRef = ?";
$stmt = mysqli_prepare($link, $docsQuery);
mysqli_stmt_bind_param($stmt, "i", $getteam);
mysqli_stmt_execute($stmt);
$docsResult = mysqli_stmt_get_result($stmt);
if (!$docsResult) 
{
	die('Docs query failed: ' . mysqli_error($link));
}
$numberofdocs = mysqli_num_rows($docsResult);

if (mysqli_num_rows($teamResult) > 0) 
{	
	echo '<table style="width:100%;"><tr>';
	echo '<td style="width:50%; background:white; border:0px;"><h4 style="margin-bottom:2px;">'.$fullteamname.'</h4>';
	if(mysqli_num_rows($rresults) > 0)
	{
		while($results = mysqli_fetch_assoc($rresults))
		{
			$currentstatus = $results['TeamResultText'];
			if($currentstatus == "Champions")
			{
				echo '<span style="font-size:1.3em; font_weight:bold;">';
				echo $results['TeamResultText'];
				echo '!!</span>';
			}
			else
			{
				echo $results['TeamResultText'];
			}
			echo '<br>';
		}
	}
	echo '</td>';
	echo '<td style="width:50%; background:white; border:0px;">
	<a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbadocslist.php?yid='.$getyear.'&actid=tm&refid='.$getteam.'">Team Documents ('.$numberofdocs.')</a>';
	if($_SESSION['accesslevel'] > 3)
	{
		echo '   <a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbacopyteam.php?yid='.$getyear.'&actid='.$getteam.'">Copy Team</a>';
	}
	if(mysqli_num_rows($rawards) > 0)
	{
		echo '<br><br>';
		while($results = mysqli_fetch_assoc($rawards))
		{
			echo $results['AwardName'].' - '.$results['FirstName'].' '.$results['LastName'];
			echo '<br>';
		}
	}
				
	
	echo '</td></tr></table>';
	
	echo '<p> </p><table width="90%">';
	if($_SESSION['accesslevel'] > 3) // God access shows delete button column
	{
		echo '<tr><th width="40%">Name</th><th width="40%">Role</th><th>Games<br><span style="font-size:0.6em"> (click to add)</span></th><th>Delete</th></tr>';
	}
	elseif($_SESSION['accesslevel'] > 2)
	{
		echo '<tr><th width="40%">Name</th><th width="40%">Role</th><th>Games<br><span style="font-size:0.6em"> (click to add)</span></th></tr>';
	}
	else
	{
		echo '<tr><th width="40%">Name</th><th width="40%">Role</th><th>Games</th></tr>';
	}

		while ($team = mysqli_fetch_assoc($teamResult)) 
		{
			if($_SESSION['accesslevel'] > 3) // God access shows delete buton
			{
				echo '<tr><td><a href="pbaperson.php?pid='.$team['MemberID'].'">'.$team['FirstName'].' '.$team['LastName'].'</a></td>
					<td>'.$team['Role'].'</td>
					<td><a href="pbaaddgames.php?pid='.$team['MemberID'].'&tid='.$getteam.'&yid='.$getyear.'&gp='.$team['GamesPlayed'].'">'.$team['GamesPlayed'].'</a></td>
					<td><a onclick="return confirm(\'Are you sure?\');" class="buttonlink" href="pbadeleterecords.php?tmid='.$team['TeamMembId'].'&yid='.$getyear.'&tid='.$getteam.'">
					Delete</a></td></tr>';
			}
			elseif($_SESSION['accesslevel'] > 2)
			{
				echo '<tr><td><a href="pbaperson.php?pid='.$team['MemberID'].'">'.$team['FirstName'].' '.$team['LastName'].'</a></td><td>'.$team['Role'].'</td>
					<td><a href="pbaaddgames.php?pid='.$team['MemberID'].'&tid='.$getteam.'&yid='.$getyear.'&gp='.$team['GamesPlayed'].'">'.$team['GamesPlayed'].'</a>
					</td></tr>';
			}
			else
			{
				echo '<tr><td><a href="pbaperson.php?pid='.$team['MemberID'].'">'.$team['FirstName'].' '.$team['LastName'].'</a></td><td>'.$team['Role'].'</td>
					<td>'.$team['GamesPlayed'].'</td></tr>';
			}
		}
	echo '</table>';	
}
else
{
	echo "This team does not have any members.";
}
?>
