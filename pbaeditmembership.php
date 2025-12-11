<?php
//Rev 1 19/11/2025
//this page is called from the membership edit button
//on the membership activity page
//it is used to update the start and end dates and the receipt
//on update the membership list is refreshed with the new data
session_start();
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();
	load(); //redirect to login page
}
$page_title = 'People';
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

if(isset($_GET['msid']))
{
	$getmsid = htmlentities($_GET['msid']);
}

// page header
echo '<h2>Edit Membership</h2>';

//add the sub menu	
require('pbaincludes/pbaactivitiesmenu.php');
?>
<?php
if(isset($_POST['update'])) //has form been submitted
{
	$formmtype = htmlentities($_POST['mtype']);
	$formyear = htmlentities($_POST['year']);
	$formmsid = htmlentities($_POST['mshipid']);
	$formreceipt = htmlentities($_POST['receipt']);
	$formstart = ($_POST['start'] == "") ? "0000-00-00" : htmlentities($_POST['start']);
	$formend = ($_POST['end'] == "") ? "0000-00-00" : htmlentities($_POST['end']);
	$formreceipt = (strlen($_POST['receipt']) > 0) ? ', receipt = "'.htmlentities($_POST['receipt']).'" ' : ', receipt = NULL';
	require('../connecttopba.php');
	$q = "UPDATE memberships SET start = ?, end = ?$formreceipt WHERE MshipId = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "sssi", $formstart, $formend, $formreceipt, $formmsid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);

	if($r){echo 'Update successful';}


	//redirect back to memberships list
	require('pbalogin_tools.php');
	load("pbaactivitymemberships.php?mid=$formmtype&yid=$formyear");
	exit();
}
?>
<div>
<hr>
<?php
//find persons name
require('../connecttopba.php');


	$q = "SELECT mn, mbid, st, end, rc, members.FirstName, members.LastName, msid, yrid, yrtxt, mtid FROM 
	(SELECT membertypes.Type, MembId, start, end, receipt, MshipId, Year, years.YearText, Mtype FROM ((memberships 
	INNER JOIN years ON memberships.Year = years.YearId) 
	INNER JOIN membertypes ON memberships.Mtype = membertypes.MemBTypeId) 
	WHERE memberships.MshipId = ?) 
	membershipdata (mn, mbid, st, end, rc, msid, yrid, yrtxt, mtid) 
	INNER JOIN members ON membershipdata.mbid = members.MemberID";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $getmsid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	$row = mysqli_fetch_array($r, MYSQLI_ASSOC);

?>
<form action="pbaeditmembership.php" method = "POST">
<table><tr>
<td><input type="hidden" name="mshipid" value="<?php if(isset($getmsid)) echo $getmsid;?>">Name:</td>
<td><?php echo $row['FirstName'].' '.$row['LastName'];?></td></tr>
<td><input type="hidden" name="year" value="<?php if(isset($row['yrid'])) echo $row['yrid'];?>">Year:</td>
<td><?php echo $row['yrtxt'];?></td></tr>
<td><input type="hidden" name="mtype" value="<?php if(isset($row['mtid'])) echo $row['mtid'];?>">Member Type:</td>
<td><?php echo $row['mn'];?></td></tr>
<td>Start Date:</td><td>
<input type="date" name="start" value="<?php if(isset($row['st'])) echo date("Y-m-d",strtotime($row['st']));?>"></td></tr>
<td>End Date:</td><td><input type="date" name="end" value="<?php if(isset($row['end'])) echo date("Y-m-d",strtotime($row['end']));?>"></td></tr>
<td>Receipt:</td><td><input type="text" name="receipt" value="<?php if(isset($row['rc'])) echo $row['rc'];?>"></td></tr>
</table>

<input type="submit" value="Update" name="update">
</form>
</div>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>