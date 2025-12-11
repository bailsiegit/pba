<?php
//Rev 1 19/11/2025
//this page is called from either the loading of pbaactivitycommittees.php
//or the javascript on that page
//this page then limits the committees combo box to only those committees 
//that have members for the selected year
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');
//if called from page load use existing variables else use the GET values
$year = (isset($_GET['yid'])) ? (int) $_GET['yid'] : $formyear;
$getcommittee = (isset($_GET['cid'])) ? (int)$_GET['cid'] : $cn;
$qyear = "SELECT YearText FROM years WHERE YearId = ?"; // get year text for use if no committees found
$stmt = mysqli_prepare($link, $qyear);
mysqli_stmt_bind_param($stmt, "i", $year);
mysqli_stmt_execute($stmt);
$ryear = mysqli_stmt_get_result($stmt);
if (!$ryear) 
{
	die('Year query failed: ' . mysqli_error($link));
}
$yeartext = mysqli_fetch_assoc($ryear);
    
//find all committees for the yearid in the selectedyear combo (year parameter)
$committeeQuery = "SELECT committeememb.CommId, CommitteeName FROM (committees 
INNER JOIN committeememb ON committees.CommitteeId = committeememb.CommId) 
WHERE committeememb.Year = ? GROUP BY CommitteeName";
$stmt = mysqli_prepare($link, $committeeQuery);
mysqli_stmt_bind_param($stmt, "i", $year);
mysqli_stmt_execute($stmt);
$committeeResult = mysqli_stmt_get_result($stmt);

if (!$committeeResult) 
{
	die('Committee query failed: ' . mysqli_error($link));
}
    
if (mysqli_num_rows($committeeResult) > 0) //if any committees are found, display them in combo box
{
	echo '<option value="0">Select committee...</option>';
    while ($committee = mysqli_fetch_assoc($committeeResult)) 
	{
		$selected = ($getcommittee == $committee['CommId']) ? ' selected="selected"' : '';
        echo '<option value="' . $committee['CommId'] . '"'.$selected.'>' . $committee['CommitteeName'] . '</option>';
    }
} 
else 
{
    echo '<option value="0">No committees found in '.$yeartext['YearText'].'</option>';
}

?>
