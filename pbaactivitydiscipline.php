<?php
//Rev 1 12/12/2025
//this page displays the disciplinary activities
//the user can only select activities by year
//activities can be added by authorised users
//activity entries can be deleted by authorised users
session_start();
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();
	load(); //redirect to login page
}
$page_title = 'Activity';
include('pbaincludes/pbaheader.html');

$_SESSION['timeoutstart'] = time();

?>

<?php
if($_SESSION['accesslevel'] < 1)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<?php
//!!incident needs an edit page and the ability to print from there!!
//download accolade List
if(isset($_POST['downloadaccolades'])) 
{
	$formrole = htmlentities($_POST['selectedaccolade']);
	$formyear = htmlentities($_POST['selectedyear']);
	$titles = array("Year", "Accolade", "First Name", "Last Name", "Phone", "Email", "Address", "Suburb");
	$path = 'downloads/pbavolunteerlist.csv';
	$emailsfile = fopen($path, "w");
	fputcsv($emailsfile, $titles); //put titles in output file
		require('../connecttopba.php');
		if(strlen($formrole) == 1) // if role is not selected then show roles the year
		{
			$q = "SELECT years.YearText, acolades.Accolade, accolades.Details, members.FirstName, members.LastName, 
			members.Mobile, members.Email, members.Numberandstreet, members.Suburb 
			FROM accolades 
			INNER JOIN members ON accolades.MembId = members.MemberId
			INNER JOIN years ON accolades.YearId = years.YearId 
			WHERE accolades.YearId = ?";
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "i", $formyear);
		}
		//else // if role is selected, find role for all years
		//{
		//	$q = "SELECT years.YearText, volunteers.Role, members.FirstName, members.LastName, 
		//	members.Mobile, members.Email, members.Numberandstreet, members.Suburb 
		//	FROM volunteers 
		//	INNER JOIN members ON volunteers.MembId = members.MemberId
		//	INNER JOIN years ON volunteers.Year = years.YearId 
		//	WHERE volunteers.Role = ? 
		//	ORDER BY years.YearId DESC";
		//	$stmt = mysqli_prepare($link, $q);
		//	mysqli_stmt_bind_param($stmt, "s", $formrole);
		//}
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		//$r = mysqli_query($link, $q);

		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
		{
			$row['Mobile'] = "'".$row['Mobile'];
			fputcsv($emailsfile, $row);
		}
	$footnotes1[] = "This file may contain sensitive personal information";
	$footnotes2[] = "Please abide by the PBA Privacy Policy.";
	fputcsv($emailsfile, []);
	fputcsv($emailsfile, $footnotes1);
	fputcsv($emailsfile, $footnotes2);
	fclose($emailsfile); // close csv file
	//mysqli_free_result($r);
	mysqli_stmt_close($stmt); // close sql
	mysqli_close($link); // close database
	header('Location: pbadownloadcsv.php?fn=pbaaccoladelist');
	//exit;

}
?>

<?php
//add person to displayed list
if(isset($_POST['addperson']) && $_POST['selectname'] > 0 && !empty($_POST['discdate']) && !empty($_POST['discdetails']))
{
	$formperson = htmlentities($_POST['selectname']);
	$formdate = htmlentities($_POST['discdate']);
	$formdetails = htmlentities($_POST['discdetails']);
	require('../connecttopba.php');
	//find YearId
	$q = 'SELECT YearId from years WHERE YearText = "'.date("Y", strtotime($_POST['discdate'])).'"';
	$r=mysqli_query($link, $q);
	$formyear = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$formyear = $formyear['YearId'];
	
	//IGNORE will skip error if new entry is a duplicate
	$q = "INSERT IGNORE INTO incident (MembId, IncidentDetails, IncidentDate, YearId) VALUES (?, ?, ?, ?)";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "issi", $formperson, $formdetails, $formdate, $formyear);
	mysqli_stmt_execute($stmt);
			
	mysqli_close($link);
}
?>

<?php

if(isset($_GET['yid']))
{
	$formyear = htmlentities($_GET['yid']);
}

//table to layout top area
echo '<table style="width:100%;"><tr>';
echo '<td style="width:50%; border:0px; background-color:white;">';
//add the sub menu	
require('pbaincludes/pbaactivitiesmenu.php');
// page header
echo '<h2 style="margin-bottom:0px; padding-bottom:0px;" >Display Incident List</h2>';
?>
<!--search criteria form-->
<div><br>
<form action="pbaactivityincident.php" method = "POST">
<select name="incidentview" id="incidentview" onChange="displayIncidentView()">
<option value="0">All</option>
<option value="1">Open Incidents</option>
<option value="2">Current Penalties</option>
</select>
<br>
</td><td style="width:50%; background-color:white; border:0px;">
<?php
//}
?>
<div id="addarea">
<?php
if($_SESSION['accesslevel'] > 2) // read write and above get access to add disciplinary activities
{
?>
	<br>Name:
	<select style="margin:5px" name="selectname" id="selectname">
	<option value="0">Select person...</option>
<?php
	$q = "SELECT FirstName, LastName, MemberID 
	FROM members 
	ORDER BY LastName, FirstName";
	require('../connecttopba.php');
	$r = mysqli_query($link, $q);
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
	echo '<option value="'.$row['MemberID'].'">'.$row['LastName'].', '.$row['FirstName'].'</option>';
	}
?>
</select>
<br>Incident: 
<input type="text" id="discdetails" name="discdetails" size="40" placeholder="Details of the incident"><br>
Incident Date: <input style="margin:5px 0px;" type="date" name="discdate" id="discdate">
<br>
<?php
	echo '<input type="submit" value="Add Incident" name="addperson">';
}
?>
</div>
</td></tr></table>
</form>
</div>
<hr>
<div id="displaydata">

<?php

include('pbadisplay_incident.php');


?>
</div>
<?php
include('pbaincludes/pbafooter.html');
?>

	<script>
    function displayIncidentView() {
        var view = document.getElementById("incidentview").value;
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "pbadisplay_incident.php?java=1&vid=" + view, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
				document.getElementById("displaydata").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
    </script>
</body></html>