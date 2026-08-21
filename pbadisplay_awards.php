<?php
//Rev 3 14/8/2026 - Added TeamName to display and rewrote SQL
//this page is called by java script from pbaactivityawards.php
//it produces the text file to fill the displaydata <div>
//it produces either a list of all award winners for a given year
//or a list of every winner of a given award depending on which script calls this page
if(!isset($yid))
{
	session_start();
}
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	session_unset();
	session_destroy();
	header('Location: pbalogin.php?disp=1');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');
//get all award winners for the selected year
if (isset($_GET['yid']) && is_numeric($_GET['yid']) || isset($yid)) 
{
    $getyear = (isset($yid)) ? $yid : (int) $_GET['yid'];
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
	$q = "SELECT 
    aw.Comments,
    a.AwardName,
    m.FirstName,
    m.LastName,
    aw.MembId,
    aw.AwardId,
    aw.YearId,
	t.TeamName
	FROM awardwinners aw
    JOIN years y ON aw.YearId = y.YearId
    JOIN awards a ON aw.AwardId = a.AwardID
	JOIN members m ON aw.MembId = m.MemberID
	JOIN teams t ON aw.TeamId = t.TeamId
    WHERE aw.YearId = ?
    ORDER BY t.TeamName, a.DisplayOrder";

	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	if ($stmt === false) 
	{
    die("Prepare failed: " . mysqli_error($link) . "<br>Query: $q");
	}
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
			echo '<tr><th>Award</th><th>Team</th><th>Winner</th><th>Comments</th><th>Delete</th></tr>';
		}
		else
		{
			echo '<tr><th>Award</th><th>Team</th><th>Winner</th><th>Comments</th></tr>';
		}
		while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
		{
			if($_SESSION['accesslevel'] > 3)
			{
				echo '<tr><td>'.$row['AwardName'].'</td><td>'.$row['TeamName'].'<td><a href="pbaperson.php?pid='.$row['MembId'].'">'.$row['FirstName'].' '.$row['LastName'].'</a></td><td>'.$row['Comments'].'</td>
				<td><a onclick="return confirm(\'Are you sure?\');" class="buttonlink" href="pbadeleterecords.php?pid='.$row['MembId'].'&aid='.$row['AwardId'].'&yid='.$row['YearId'].'">Delete</a></td></tr>';
			}
			else
			{
				echo '<tr><td>'.$row['AwardName'].'</td><td>',$row['TeamName'].'<td><a href="pbaperson.php?pid='.$row['MembId'].'">'.$row['FirstName'].' '.$row['LastName'].'</a></td><td>'.$row['Comments'].'</td></tr>';
			}
		}
			echo '</table>';
	}

mysqli_close($link);
}

//process award selection - all recipents of selected award
if (isset($_GET['aid']) && is_numeric($_GET['aid'])) 
{
	$getaward = (int) $_GET['aid'];
	$getteam = (int) $_GET['tid'];
	if($getaward < 1)
	{
		echo 'Please select an award.';
	}
	else{
		if($getteam == 0) // show all winners regargless of team
		{
			// get a list of all the people who have won the award		
			$q = "SELECT
			aw.Comments,
			a.AwardName, 
			y.YearText, 
			m.FirstName, 
			m.LastName, 
			aw.MembId,
			t.TeamName
			FROM awardwinners aw
			JOIN years y ON aw.YearId = y.YearId
			JOIN awards a ON aw.AwardId = a.AwardID 
			JOIN members m ON aw.MembId = m.MemberID
			JOIN teams t ON aw.TeamId = t.TeamId
			WHERE aw.AwardId = ?
			ORDER BY aw.YearId DESC, t.TeamName";
			require('../connecttopba.php');
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "i", $getaward);
		}
		else
		{
			// get a list of all the people who have won the award for the selected team	
			$q = "SELECT
			aw.Comments,
			a.AwardName, 
			y.YearText, 
			m.FirstName, 
			m.LastName, 
			aw.MembId,
			t.TeamName
			FROM awardwinners aw
			JOIN years y ON aw.YearId = y.YearId
			JOIN awards a ON aw.AwardId = a.AwardID 
			JOIN members m ON aw.MembId = m.MemberID
			JOIN teams t ON aw.TeamId = t.TeamId
			WHERE aw.AwardId = ? AND aw.TeamId = ?
			ORDER BY aw.YearId DESC";
			require('../connecttopba.php');
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "ii", $getaward, $getteam);
		}
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);

		if(mysqli_num_rows($r)<1)
		{
			echo '<p><br>No record of anybody receiving the selected award.<br><br></p>';
		}
		else
		{
			echo '<p> </p><table width="90%">';
			echo '<input type="hidden" value="<?php if(isset($award) echo $award;?>" name="award">'; //why is this here?
			echo '<tr><th>Year</th><th>Award</th><th>Team</th><th>Name</th><th>Comments</th></tr>';
			while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
			{
				echo '<tr><td>'.$row['YearText'].'</td><td>'.$row['AwardName'].'</td><td>'.$row['TeamName'].'</td><td><a href="pbaperson.php?pid='.$row['MembId'].'">'.$row['FirstName'].' '.$row['LastName'].'</a></td><td>'.$row['Comments'].'</td></tr>';
			}
			echo '</table>';
		}
	}

mysqli_close($link);
}
?>
