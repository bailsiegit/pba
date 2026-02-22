<?php
//Rev 1 21/12/2025
//this page is called from the incident edit button
//on the incident activity page
//on update the incident list is refreshed with the new data
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
if($_SESSION['accesslevel'] < 3)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<?php

if(isset($_GET['iid']))
{
	$getiid = htmlentities($_GET['iid']);
}

// page header
echo '<h2>Edit Incident</h2>';

//add the sub menu	
require('pbaincludes/pbaactivitiesmenu.php');
?>
<?php
if(isset($_POST['update'])) //has form been submitted
{
	$formyear = htmlentities($_POST['year']);
	$formmiid = htmlentities($_POST['incidentid']);
	$formdetails = htmlentities($_POST['details']);
	$formtribdate = ($_POST['tribunaldate'] == "") ? "0000-00-00" : htmlentities($_POST['tribunaldate']);
	$formresult = htmlentities($_POST['tribunal']);
	$formexpdate = ($_POST['expiry'] == "") ? "0000-00-00" : htmlentities($_POST['expiry']);
	require('../connecttopba.php');
	$q = "UPDATE incident SET IncidentDetails = ?, TribunalDate = ?, TribunalResult = ?, ExpiryDate = ? WHERE IncidentId = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "ssssi", $formdetails, $formtribdate, $formresult, $formexpdate, $formmiid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);

	if($r){echo 'Update successful';}


	//redirect back to incident list
	require('pbalogin_tools.php');
	load("pbaactivityincident.php");
	exit();
}
?>
<div>
<hr>
<?php
//find persons name
require('../connecttopba.php');


	$q = "SELECT incident.*, members.FirstName, members.LastName 
	FROM incident
	INNER JOIN members ON members.MemberID = incident.MembId 
	WHERE incident.IncidentId = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $getiid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	$row = mysqli_fetch_array($r, MYSQLI_ASSOC);

?>
<form action="pbaeditincident.php" method = "POST">
Field below are a brief summary. Details in attached documents. 
<a class="buttonlink" href="pbainddocslist.php?actid=in&yid=<?php echo $row['YearId'];?>&refid=<?php echo $getiid;?>">Docs</a>
<table><tr>
<td><input type="hidden" name="incidentid" value="<?php if(isset($getiid)) echo $getiid;?>">Name:</td>
<td><?php echo $row['FirstName'].' '.$row['LastName'];?></td></tr>
<td><input type="hidden" name="year" value="<?php if(isset($row['YearId'])) echo $row['YearId'];?>">Date:</td>
<td><?php echo date("d/m/Y", strtotime($row['IncidentDate']));?></td></tr>
<td>Details:</td>
<td><textarea rows="4" cols="40" name="details"><?php if(isset($row['IncidentDetails'])) echo $row['IncidentDetails'];?></textarea></td></tr>
<td>Tribunal Date:</td><td>
<input type="date" name="tribunaldate" value="<?php if(isset($row['TribunalDate'])) echo $row['TribunalDate'];?>"></td></tr>
<td>Tribunal Result:</td>
<td><textarea rows="4" cols="40" name="tribunal"><?php if(isset($row['TribunalResult'])) echo $row['TribunalResult'];?></textarea></td></tr>
<td>Expiry Date:</td>
<td><input type="date" name="expiry" value="<?php if(isset($row['ExpiryDate'])) echo $row['ExpiryDate'];?>"></td></tr>

</table>

<input class="buttonlink" type="submit" value="Update" name="update">   

</form>
</div>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>