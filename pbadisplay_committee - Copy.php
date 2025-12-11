<?php
//Rev 1 19/11/2025
//this page is called either directly or from javascript by the committee activity page
//this page creates the table of results for display on that page
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');

//if (!isset($_GET['yid'])) 
//{
//    die('No year parameter received.');
//}

if (isset($_GET['yid']) && isset($_GET['cid'])) // are parameters sent via GET parameters from java function
{
    $getyear = (int) $_GET['yid'];
	$getcommittee = (int)$_GET['cid'];
}
else //otherwise, they must be in the calling page already
{
	$getyear = $y;
	$getcommittee = $c;
}
// get the text of the selected year for display on the results page
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

// get the text name of the selected committee for display on the results page
$qcommittee = "SELECT CommitteeName FROM committees WHERE CommitteeId = ?";
$stmt = mysqli_prepare($link, $qcommittee);
mysqli_stmt_bind_param($stmt, "i", $getcommittee);
mysqli_stmt_execute($stmt);
$rcommittee = mysqli_stmt_get_result($stmt);
$committeename = mysqli_fetch_assoc($rcommittee);
    
$committeeQuery = 'SELECT rl, mbid, members.FirstName, members.LastName FROM
	(SELECT Role, committees.CommitteeName, MembId FROM ((committeememb 
	INNER JOIN years ON committeememb.Year = years.YearId)
	INNER JOIN committees ON committeememb.CommId = committees.CommitteeId) 
	WHERE committeememb.Year = ? AND committeememb.CommId = ?) 
	committeedata (rl, cn, mbid) 
	INNER JOIN members ON committeedata.mbid = members.MemberID';
$stmt = mysqli_prepare($link, $committeeQuery);
mysqli_stmt_bind_param($stmt, "ii", $getyear, $getcommittee);
mysqli_stmt_execute($stmt);
$committeeResult = mysqli_stmt_get_result($stmt);	

if (!$committeeResult) 
{
   die('committee query failed: ' . mysqli_error($link));
}
echo '<table style="width:100%;"><tr>';
echo '<td style="width:50%; background:white; border:0px;"><h4>'.$yeartext['YearText'].' - '.$committeename['CommitteeName'].'</h4></td>';
echo '<td style="width:50%; background:white; border:0px;">
	<a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbadocslist.php?yid='.$getyear.'&actid=cm&refid='.$getcommittee.'">Committee Documents</a>';
	if($_SESSION['accesslevel'] > 3)
	{
		echo '   <a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbacopycommittee.php?yid='.$getyear.'&actid='.$getcommittee.'">Copy Committee</a>';
	}
	echo '</td></tr></table>';
echo '<p> </p><table width="90%">';
if($_SESSION['accesslevel'] > 3)
{
	echo '<tr><th>Name</th><th>Role</th><th>Delete</th></tr>'; //show delete column header
}
else
{
	echo '<tr><th>Name</th><th>Role</th></tr>';
}
if (mysqli_num_rows($committeeResult) > 0) 
{
	while ($committee = mysqli_fetch_assoc($committeeResult)) 
	{
		if($_SESSION['accesslevel'] > 3)
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$committee['mbid'].'">'.$committee['FirstName'].' '.$committee['LastName'].'</a></td>
				<td>'.$committee['rl'].'</td><td><a onclick="return confirm(\'Are you sure?\');" class="buttonlink" href="pbadeleterecords.php?yid='.$getyear.'&cid='.$getcommittee.'&pid='.$committee['mbid'].'">Delete</a></td></tr>';
		}
		else
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$committee['mbid'].'">'.$committee['FirstName'].' '.$committee['LastName'].'</a></td>
				<td>'.$committee['rl'].'</td></tr>';
		}
		
	}
}		
else 
{
	echo '<option value="">No committees found in '.$yeartext['YearText'].'</option>';
}
echo '</table>';

?>
