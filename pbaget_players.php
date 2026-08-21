<?php
//Rev 1 5/8/2026
//this page is called from either the loading of pbaactivityteams.php
//or the javascript on that page
//this page then limits the players combo box in the add award area to only  
//the members of the selected team
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');
//if called from pageload use existing variables else use GET values
$year = (isset($_GET['yid'])) ? (int) $_GET['yid'] : $formyear;
$getteam = (isset($_GET['tid'])) ? (int)$_GET['tid'] : $formteam;
//find teams that have members for selected year
	$q = "SELECT mb.FirstName, mb.LastName, mb.MemberID FROM members mb
	JOIN teammembers tm ON mb.MemberID = tm.MembId
	WHERE tm.YearId = $year AND TeamId = $getteam
	ORDER BY LastName, FirstName";
	require('../connecttopba.php');
	$r = mysqli_query($link, $q);
	echo '<option value="0">Select player...</option>';
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		echo '<option value="'.$row['MemberID'].'">'.$row['LastName'].', '.$row['FirstName'].'</option>';
	}
?>
