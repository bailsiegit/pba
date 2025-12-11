<?php
//Rev 1 19/11/2025
//this page is called from either the loading of pbaactivityteams.php
//or the javascript on that page
//this page then limits the teams combo box to only those teams 
//that have members for the selected year
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');
//if called from pageload use existing variables else use GET values
$year = (isset($_GET['yid'])) ? (int) $_GET['yid'] : $ty;
$getteam = (isset($_GET['tid'])) ? (int)$_GET['tid'] : $tn;

$qyear = "SELECT YearText FROM years WHERE YearId = ?";
$stmt = mysqli_prepare($link, $qyear);
mysqli_stmt_bind_param($stmt, "i", $year);
mysqli_stmt_execute($stmt);
$ryear = mysqli_stmt_get_result($stmt);
if (!$ryear) 
{
    die('Year query failed: ' . mysqli_error($link));
}
$yeartext = mysqli_fetch_assoc($ryear);
//find teams that have members for selected year
$teamQuery = "SELECT teammembers.TeamId, TeamName FROM (teams 
INNER JOIN teammembers ON teams.TeamId = teammembers.TeamId) 
WHERE teammembers.Year = ? GROUP BY TeamName";
$stmt = mysqli_prepare($link, $teamQuery);
mysqli_stmt_bind_param($stmt, "i", $year);
mysqli_stmt_execute($stmt);
$teamResult = mysqli_stmt_get_result($stmt);
if (!$teamResult) 
{
    die('Team query failed: ' . mysqli_error($link));
}
    
if (mysqli_num_rows($teamResult) > 0) //if teams are found populate combo box
{
	echo '<option value="0">Select team...</option>';
    while ($team = mysqli_fetch_assoc($teamResult)) 
	{
		$selected = ($getteam == $team['TeamId']) ? ' selected="selected"' : '';
        echo '<option value="' . $team['TeamId'] . '"'.$selected.'>' . $team['TeamName'] . '</option>';
    }
} 
else 
{
    echo '<option value="">No teams found in '.$yeartext['YearText'].'</option>';
}

?>
