<?php
//Rev 1 19/11/2025
//this page is callled from the dlete buttons on the various activity pages
//the selected record is deleted and the same activity list is refreshed on screen
session_start();
//delete award record
if(isset($_GET['aid'])) //check the task is to delete an award
{
	$awardid = htmlentities($_GET['aid']);
	$yearid = htmlentities($_GET['yid']);
	$personid = htmlentities($_GET['pid']);
	$q = "DELETE FROM awardwinners WHERE AwardId = ? AND YearId = ? AND MembId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "iii", $awardid, $yearid, $personid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	require('pbalogin_tools.php');
	load("pbaactivityawards.php?yid=$yearid");
	exit();
}

if(isset($_GET['tid'])) //check task is to delete a team member
{
	$yearid = htmlentities($_GET['yid']);
	$personid = htmlentities($_GET['pid']);
	$teamid = htmlentities($_GET['tid']);
	$q = "DELETE FROM teammembers WHERE TeamId = ? AND Year = ? AND MembId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "iii", $teamid, $yearid, $personid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	require('pbalogin_tools.php');
	load("pbaactivityteams.php?yid=$yearid&tid=$teamid");
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
	require('pbalogin_tools.php');
	load("pbaactivitymemberships.php?yid=$yearid&mid=$membtypeid");
	exit();
}

if(isset($_GET['cid'])) //check task is to delete a committee member
{
	$yearid = htmlentities($_GET['yid']);
	$personid = htmlentities($_GET['pid']);
	$commid = htmlentities($_GET['cid']);
	$q = "DELETE FROM committeememb WHERE CommId = ? AND Year = ? AND MembId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "iii", $commid, $yearid, $personid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	require('pbalogin_tools.php');
	load("pbaactivitycommittees.php?yid=$yearid&cid=$commid");
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
	require('pbalogin_tools.php');
	load("pbaactivityemployee.php?yid=$yearid");
	exit();
}

if(isset($_GET['vid'])) //check task is to delete an volunteer
{
	$volid = htmlentities($_GET['vid']);
	$yearid = htmlentities($_GET['yid']);
	$q = "DELETE FROM volunteers WHERE VolId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $volid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	require('pbalogin_tools.php');
	load("pbaactivityvolunteers.php?yid=$yearid");
	exit();
}
?>