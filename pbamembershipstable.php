<?php
//Rev 2 14/8/2026 - added is team? and pick hierachy to match new awards structure
//this page is called from the maintenance item on the main menu
//this page provides the ability to add to the base tables and
//also make items in each base table active or Inactive

session_start();
//check that user is logged in and has not been idle for too long
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	session_unset();
	session_destroy();
	header('Location: pbalogin.php'); //redirect to login page
	exit();
}
$page_title = 'Tables';
include('pbaincludes/pbaheader.html');
include('pbaincludes/pbamaintenancemenu.php');
$_SESSION['timeoutstart'] = time(); //reset login timeout marker

?>
<h2>Table Maintenance</h2>

<?php
if($_SESSION['accesslevel'] < 4) //check user has permissions for this page
{
	//if not permitted show message and exit script
	echo '<br><br>You do not have permission to access this page.';
	include('pbaincludes/pbafooter.html');
	exit();
}
?>

<?php
// add membership type to membertypes
if(isset($_POST['addmembtype']) && !empty($_POST['newmembtype']))
{
	$formnewmembtype = htmlentities(trim($_POST['newmembtype']));
	$formnewmembdesc = htmlentities(trim($_POST['newmembdesc']));
	$q = "INSERT INTO membertypes (Type, Description, Status) VALUES (?, ?, 'Active')"; 
	require('../connecttopba.php');
	$r = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($r, "ss", $formnewmembtype, $formnewmembdesc);
	mysqli_stmt_execute($r);
	mysqli_close($link);
	if($r){echo '<p style="color:red">New member type added</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>
<?php
// change status of membership type
if(isset($_POST['changemembstatus']))
{
	$formmembtype = htmlentities($_POST['selectmembtype']);
	$q = "SELECT Status FROM membertypes WHERE MemBTypeId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formmembtype);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	$pbastatus = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$currstatus = $pbastatus['Status'];
	$newstatus = ($currstatus == "Active") ? "Inactive" : "Active";
	$q = "UPDATE membertypes SET Status = '$newstatus' WHERE MemBTypeId = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formmembtype);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	if($r){echo '<p style="color:red">Membership type status changed</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>


This page is used to add new membership types and set Active/Inactive status of existing entries.
<hr>
<form action="pbamembershipstable.php" method="POST">
<h3>Membership Types</h3>
<span style="color:red;">Please check the Status list before entering a membership type.<br>
The membership type may exist, but be Inactive.</span><br>
<h4>Add new membership type</h4>
Type: 
<input type="text" name="newmembtype" size="21" placeholder="Enter new membership type"><br>
Description (optional): 
<input type="text" name="newmembdesc" size="40">
<input type="submit" name="addmembtype" value="Add">

<br>
<h4>Change Status</h4>
Select membership type:<br>
<select name="selectmembtype" id="selectmembtype">
<?php
// get all membership types for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM membertypes';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build teams combo box from above query
while($pbamemberships = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$membershipid = $pbamemberships['MemBTypeId'];
	$pbamembership = $pbamemberships['Type'];
	$pbastatus = $pbamemberships['Status'];

		echo '<option value = ' . $membershipid . '>' . $pbamembership . '/'. $pbastatus.'</option>';
}

?>
</select>
<input type="submit" value="Change Status" name="changemembstatus">
</form>
<br>

<?php
include('pbaincludes/pbafooter.html');
?>
</body></html>