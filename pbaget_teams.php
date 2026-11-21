<?php
//Rev 2 3/6/2026 - odrered teams alphabetically
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

//$teamQuery = "SELECT teammembers.TeamId, TeamName FROM (teams 
//INNER JOIN teammembers ON teams.TeamId = teammembers.TeamId) 
//WHERE teammembers.YearId = ? GROUP BY TeamName ORDER BY TeamName";
//$stmt = mysqli_prepare($link, $teamQuery);
//mysqli_stmt_bind_param($stmt, "i", $year);
//mysqli_stmt_execute($stmt);
//$teamResult = mysqli_stmt_get_result($stmt);
echo "year is $year";
//find teams for the year
$teamQuery = "SELECT
te.TeamEntryId,
y.YearText,
l.LeagueName,
s.SeasonName,
gr.GradeName,
ge.GenderName,
d.DivisionName,
tn.TeamName
FROM teamentries te
JOIN years y ON te.YearId = y.YearId
JOIN leagues l ON te.LeagueId = l.LeagueId
JOIN seasons s ON te.SeasonId = s.SeasonId
JOIN grades gr ON te.GradeId = gr.GradeId
JOIN genders ge ON te.GenderId = ge.GenderId
JOIN divisions d ON te.DivisionId = d.DivisionId
JOIN teamnames tn ON te.TeamNameId = tn.TeamNameId
WHERE te.YearId = ?";
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
		$team['GradeName'] = ($team['GradeName'] == "None") ? "" : " ".$team['GradeName'];
		$team['DivisionName'] = ($team['DivisionName'] == "None") ? "" : " ".$team['DivisionName'];
		$team['TeamName'] = ($team['TeamName'] == "None") ? "" : " ".$team['TeamName'];
		$fullteamname = $team['LeagueName']." ".$team['SeasonName'].$team['GradeName']." ".$team['GenderName'].$team['DivisionName'].$team['TeamName'];
		$selected = ($getteam == $team['TeamEntryId']) ? ' selected="selected"' : '';
        echo '<option value="' . $team['TeamEntryId'] . '"'.$selected.'>' . $fullteamname . '</option>';
    }
} 
else 
{
    echo '<option value="">No teams found in '.$yeartext['YearText'].'</option>';
}

?>
