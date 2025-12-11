<?php
//Rev 1 19/11/2025
//this page is called by java script from pbaactivityawards.php
//it produces the text file to fill the displaydata <div>
//it produces either a list of all award winners for a given year
//or a list of every winner of a given award depending on which script calls this page
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');
//get all award winners for the selected year
if (isset($_GET['yid']) && is_numeric($_GET['yid'])) {
    $getyear = (int) $_GET['yid'];
	$qyear = "SELECT YearText, YearId FROM years WHERE YearId = ?";
	$stmt = mysqli_prepare($link, $qyear);
	mysqli_stmt_bind_param($stmt, "i", $getyear);
	mysqli_stmt_execute($stmt);
	$ryear = mysqli_stmt_get_result($stmt);
	
	if (!$ryear) {
		die('Year query failed: ' . mysqli_error($link));
	}
	$yeartext = mysqli_fetch_assoc($ryear);
    
    // get a list of all the award winners for the year
	$q = "SELECT cm, aw, members.FirstName, members.LastName, mbid, awid, yrid FROM
	(SELECT Comments, awards.AwardName, MembId, awardwinners.YearId, awards.AwardId FROM ((awardwinners 
	INNER JOIN years ON awardwinners.YearId = years.YearId)
	INNER JOIN awards ON awardwinners.AwardId = awards.AwardID) WHERE awardwinners.YearId = ? ORDER BY awardwinners.YearId DESC) 
	awarddata (cm, aw, mbid, yrid, awid) 
	INNER JOIN members ON awarddata.mbid = members.MemberID";
	
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $getyear);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	

	if(mysqli_num_rows($r)<1)
	{
		echo '<p><br>No record of any awards in '.$yeartext['YearText'].'.<br><br></p>';
	}
	else
	{
		echo '<p> </p><table width="90%">';
		if($_SESSION['accesslevel'] > 3)
		{
			echo '<tr><th>Award</th><th>Winner</th><th>Comments</th><th>Delete</th></tr>';
		}
		else
		{
			echo '<tr><th>Award</th><th>Winner</th><th>Comments</th></tr>';
		}
		while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
		{
			if($_SESSION['accesslevel'] > 3)
			{
				echo '<tr><td>'.$row['aw'].'</td><td><a href="pbaperson.php?pid='.$row['mbid'].'">'.$row['FirstName'].' '.$row['LastName'].'</a></td><td>'.$row['cm'].'</td>
				<td><a onclick="return confirm(\'Are you sure?\');" class="buttonlink" href="pbadeleterecords.php?pid='.$row['mbid'].'&aid='.$row['awid'].'&yid='.$row['yrid'].'">Delete</a></td></tr>';
			}
			else
			{
				echo '<tr><td>'.$row['aw'].'</td><td><a href="pbaperson.php?pid='.$row['mbid'].'">'.$row['FirstName'].' '.$row['LastName'].'</a></td><td>'.$row['cm'].'</td></tr>';
			}
		}
			echo '</table>';
	}

mysqli_close($link);
}

//process award selection - all recipents of selected award
if (isset($_GET['aid']) && is_numeric($_GET['aid'])) {
	$getaward = (int) $_GET['aid'];
	if($getaward < 1)
	{
		echo 'Please select an award.';
	}
	else{

		// get a list of all the people who have won the award
		$q = "SELECT cm, aw, yr, members.FirstName, members.LastName, mbid FROM
		(SELECT Comments, awards.AwardName, years.YearText, MembId FROM ((awardwinners 
		INNER JOIN years ON awardwinners.YearId = years.YearId)
		INNER JOIN awards ON awardwinners.AwardId = awards.AwardID) WHERE awardwinners.AwardId = ?) 
		awarddata (cm, aw, yr, mbid) 
		INNER JOIN members ON awarddata.mbid = members.MemberID ORDER BY yr DESC";
		
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $getaward);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);

		if(mysqli_num_rows($r)<1)
		{
			echo '<p><br>No record of the selected award.<br><br></p>';
		}
		else
		{
			echo '<p> </p><table width="90%">';
			echo '<input type="hidden" value="<?php if(isset($award) echo $award;?>" name="award">'; //why is this here?
			echo '<tr><th>Year</th><th>Award</th><th>Name</th><th>Comments</th></tr>';
			while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
			{
				echo '<tr><td>'.$row['yr'].'</td><td>'.$row['aw'].'</td><td><a href="pbaperson.php?pid='.$row['mbid'].'">'.$row['FirstName'].' '.$row['LastName'].'</a></td><td>'.$row['cm'].'</td></tr>';
			}
			echo '</table>';
		}
	}

mysqli_close($link);
}
?>
