<?php
//Rev 3 19/02/2026 - incidents added
//this page is callled from the dlete buttons on the various activity pages
//the selected record is deleted and the same activity list is refreshed on screen
session_start();
//delete award record
if(isset($_GET['aid'])) //check the task is to delete an award recipient
{
	$awardid = htmlentities($_GET['aid']);
	$yearid = htmlentities($_GET['yid']);
	$personid = htmlentities($_GET['pid']);
	$q = "DELETE FROM awardwinners WHERE AwardWinId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $awardid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	header("Location: pbaactivityawards.php?yid=$yearid");
	exit();
}

if(isset($_GET['tmid'])) //check task is to delete a team member
{
	$yearid = htmlentities($_GET['yid']);
	$teamid = htmlentities($_GET['tid']);
	$teammemberid = htmlentities($_GET['tmid']);
	$q = "DELETE FROM teammembers WHERE TeamMembId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $teammemberid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	header("Location: pbaactivityteams.php?yid=$yearid&tid=$teamid");
	exit();
}

if(isset($_GET['msid'])) //check task is to delete a membership
{
	$yearid = htmlentities($_GET['yid']);
	$membershipid = htmlentities($_GET['msid']);
	$membtypeid = htmlentities($_GET['mid']);
	$q = "DELETE FROM memberships WHERE MshipId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $membershipid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	header("Location: pbaactivitymemberships.php?yid=$yearid&mid=$membtypeid");
	exit();
}

if(isset($_GET['cid'])) //check task is to delete a committee member
{
	$yearid = htmlentities($_GET['yid']);
	$commid = htmlentities($_GET['cid']);
	$commmembid = htmlentities($_GET['cmid']);
	$q = "DELETE FROM committeememb WHERE CommMembId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $commmembid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	header("Location: pbaactivitycommittees.php?yid=$yearid&cid=$commid");
	exit();
}

if(isset($_GET['eid'])) //check task is to delete an employee
{
	$empid = htmlentities($_GET['eid']);
	$yearid = htmlentities($_GET['yid']);
	$q = "DELETE FROM employees WHERE EmpId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $empid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	header("Location: pbaactivityemployee.php?yid=$yearid");
	exit();
}

if(isset($_GET['vid'])) //check task is to delete a volunteer
{
	$volid = htmlentities($_GET['vid']);
	$yearid = htmlentities($_GET['yid']);
	$q = "DELETE FROM volunteers WHERE VolId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $volid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	header("Location: pbaactivityvolunteers.php?yid=$yearid");
	exit();
}

if(isset($_GET['acid'])) //check task is to delete an accolade
{
	$accid = htmlentities($_GET['acid']);
	$yearid = htmlentities($_GET['yid']);
	$q = "DELETE FROM accolades WHERE AccoladeId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $accid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	header("Location: pbaactivityaccolades.php?yid=$yearid");
	exit();
}

// delete incident
if(isset($_GET['inid']))
{
	$incidentid = htmlentities($_GET['inid']);
	//check if any documents have been attached
	$q = 'SELECT DocumentId FROM documents WHERE Activity = "in" AND ActivityRef = ?';
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $incidentid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	// if no docs, delete record
	if(mysqli_num_rows($r) < 1)
	{
		$q = "DELETE FROM incident WHERE IncidentId = ?";
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $incidentid);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
	}
	//reload incident list page
	header("Location: pbaactivityincident.php");
	exit();
}

// delete team entry
if(isset($_GET['teid']))
{
	$teamentryid = htmlentities($_GET['teid']);
	$entryyear = htmlentities($_GET['yid']);
	//check if there are any related records
	$q = "SELECT DocumentId FROM documents WHERE Activity = 'tm' AND ActivityRef = ? AND YearId = ?
	UNION
	SELECT TResultsId FROM teamresults WHERE TeamId = ? AND YearId = ?
	UNION
	SELECT AwardId FROM awardwinners WHERE TeamId = ? AND YearId = ?
	UNION
	SELECT TeamMembId FROM teammembers WHERE TeamId = ? AND YearId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "iiiiiiii", $teamentryid, $entryyear, $teamentryid, $entryyear, $teamentryid, $entryyear, $teamentryid, $entryyear);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	// if no related records then delete
	if(mysqli_num_rows($r) < 1)
	{
		$q = "DELETE FROM teamentries WHERE TeamEntryId = ?";
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $teamentryid);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
	}
	//reload incident list page
	header("Location: pbateamentries.php?yid=$entryyear");
	exit();
}
?>