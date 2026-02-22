<?php
//Rev 2 19/02/2026 general update
//this page displays the incidents
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
if($_SESSION['accesslevel'] < 2)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<?php
//!!incident needs the ability to print from the edit page!!

?>

<?php
//add incident and refresh list
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
	Name:
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
<input type="text" id="discdetails" name="discdetails" size="40" placeholder="Brief summary of incident"><br>
<span style="color:red; font-size:0.8em;">Very brief summary. Details to be included in attached document/s</span><br>
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