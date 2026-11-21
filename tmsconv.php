<?php
//process team members
$teammembers = false;
//processaward winners
$awardwinners = false;
//process team results
$teamresults = false;
//process documents
$documents = true;

require('../connecttopba.php');

if($teammembers)
{
	$q = "SELECT * FROM teamcomv";
	$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
	while($nextrow = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		echo 'Team Row Id is '.$nextrow['TMId'].' and New team is '.$nextrow['NewTeam'];
		$q1 = 'UPDATE teammembers SET TeamId = '.$nextrow['NewTeam'].' WHERE TeamMembId = '.$nextrow['TMId'];
		$r1 = mysqli_query($link, $q1);
	}
}

if($awardwinners)
{
	$q = "SELECT * FROM awardconv";
	$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
	while($nextrow = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		echo ' Award Row Id is '.$nextrow['AwardWinnerId'].' and New team is '.$nextrow['NewTeam'];
		$q1 = 'UPDATE awardwinners SET TeamId = '.$nextrow['NewTeam'].' WHERE AwardWinId = '.$nextrow['AwardWinnerId'];
		$r1 = mysqli_query($link, $q1);
	}
}

if($teamresults)
{
	$q = "SELECT * FROM resultsconv";
	$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
	while($nextrow = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		echo ' Results Row Id is '.$nextrow['TResultsId'].' and New team is '.$nextrow['NewTeam'];
		$q1 = 'UPDATE awardwinners SET TeamId = '.$nextrow['NewTeam'].' WHERE TResultsId = '.$nextrow['TResultsId'];
		$r1 = mysqli_query($link, $q1);
	}
}

if($documents)
{
	$q = "SELECT * FROM docsconv";
	$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
	while($nextrow = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		echo ' Docs Row Id is '.$nextrow['DocumentId'].' and New team is '.$nextrow['NewTeam'];
		$q1 = 'UPDATE documents SET ActivityRef = '.$nextrow['NewTeam'].' WHERE DocumentId = '.$nextrow['DocumentId'];
		$r1 = mysqli_query($link, $q1);
	}
}