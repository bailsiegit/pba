<?php
//Rev 1 19/11/2025
//this page is called either directly or via javascript from the volunteers activity page
//this page creates the table of results to display on that page
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');

$getyear = (isset($_GET['yid'])) ? (int) $_GET['yid'] : $y;
$getrole = (isset($_GET['rid'])) ? htmlentities($_GET['rid']) : $rl;
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
    

echo '<table style="width:100%;"><tr>';
echo '<td style="width:50%; background:white; border:0px;">';
if(strlen($getrole) == 1)//if $getrole is zero then display for selected year
{
	$volunteerQuery = "SELECT volunteers.Role, volunteers.VolId, members.MemberId, members.FirstName, 
	members.LastName 
	FROM members	 
	INNER JOIN volunteers ON members.MemberId = volunteers.MembId 
	WHERE volunteers.Year = ?";
	$stmt = mysqli_prepare($link, $volunteerQuery);
	mysqli_stmt_bind_param($stmt, "i", $getyear);
	echo '<h4>'.$yeartext['YearText'].' - Volunteers</h4>';
}
else //if $getrole has something other than zero, display for selected role
{
	$volunteerQuery = "SELECT years.YearText, volunteers.Role, volunteers.VolId, members.MemberId, members.FirstName, members.LastName 
	FROM members	 
	INNER JOIN volunteers ON members.MemberId = volunteers.MembId
	INNER JOIN years ON volunteers.Year = years.YearId 
	WHERE volunteers.Role = ? 
	ORDER BY years.YearId DESC";
	$stmt = mysqli_prepare($link, $volunteerQuery);
	if ($stmt === false) {
    die("Prepare failed: " . mysqli_error($link) . "<br>Query: $q");
}
	mysqli_stmt_bind_param($stmt, "s", $getrole);
	echo '<h4>'.$getrole.' - Volunteers</h4>';
}
	
mysqli_stmt_execute($stmt);
$volunteerResult = mysqli_stmt_get_result($stmt);
if (!$volunteerResult) 
{
   die('volunteer query failed: ' . mysqli_error($link));
}
echo '</td><td style="width:50%; background:white; border:0px;">  </td></tr></table>';
echo '<p> </p><table width="90%">';
if (mysqli_num_rows($volunteerResult) > 0) 
{
	if(strlen($getrole) > 1)
	{
		echo '<tr><th>Year</th><th>Name</th><th>Role</th></tr>'; //header for role based list
	}
	elseif($_SESSION['accesslevel'] > 3 && strlen($getrole) == 1)
	{
		echo '<tr><th>Name</th><th>Role</th><th>Delete</th></tr>'; //headers for year based list with delete
	}
	else
	{
		echo '<tr><th>Name</th><th>Role</th></tr>'; //headers for year based list
	}

	while ($volunteer = mysqli_fetch_assoc($volunteerResult)) 
	{
		if(strlen($getrole) > 1)
		{
			echo '<tr><td>'.$volunteer['YearText'].'</td>
				<td><a href="pbaperson.php?pid='.$volunteer['MemberId'].'">'.$volunteer['FirstName'].' '.$volunteer['LastName'].'</a></td>
				<td>'.$volunteer['Role'].'</td></tr>';
		}
		elseif($_SESSION['accesslevel'] > 3 && strlen($getrole) == 1)
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$volunteer['MemberId'].'">'.$volunteer['FirstName'].' '.$volunteer['LastName'].'</a></td>
				<td>'.$volunteer['Role'].'</td>
				<td><a onclick="return confirm(\'Are you sure?\');" class="buttonlink" href="pbadeleterecords.php?vid='.$volunteer['VolId'].'&yid='.$getyear.'">Delete</a></td></tr>';
		}
		else
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$volunteer['MemberId'].'">'.$volunteer['FirstName'].' '.$volunteer['LastName'].'</a></td>
				<td>'.$volunteer['Role'].'</td></tr>';
		}
	}		
}
else 
{
	echo '<option value="">No volunteers found in '.$yeartext['YearText'].'</option><br><br>';
}
echo '</table>';

?>
