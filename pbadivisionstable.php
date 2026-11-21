<?php
//Rev 2 14/8/2026 - added is grade? and pick hierachy to match new awards structure
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
//add new division to divisions table
if(isset($_POST['adddivisionname']) && !empty($_POST['newdivisionname']))
{
	$formnewdivision = htmlentities(trim($_POST['newdivisionname']));
	$q = "INSERT INTO divisions (DivisionName, Status) VALUES (?, 'Active')"; 
	require('../connecttopba.php');
	$r = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($r, "s", $formnewdivision);
	mysqli_stmt_execute($r);
	mysqli_close($link);
	if($r){echo '<p style="color:red">New division added</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>
<?php
// change status of division
if(isset($_POST['changedivisionstatus']))
{
	$formdivision = htmlentities($_POST['selectdivision']);
	$q = "SELECT Status FROM divisions WHERE DivisionId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formdivision);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	$pbastatus = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$currstatus = $pbastatus['Status'];
	$newstatus = ($currstatus == "Active") ? "Inactive" : "Active";
	$q = "UPDATE divisions SET Status = '$newstatus' WHERE DivisionId = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formdivision);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	if(mysqli_affected_rows($link) > 0){echo '<p style="color:red">Division status changed</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>


This page is used to add divisions and set Active/Inactive status of existing entries.
<hr>
<form action="pbadivisionstable.php" method="POST">
<h3>Divisions</h3>
<h4>Add new division</h4>
<span style="color:red;">Please check the Status list before entering a new division.<br>
The division may exist, but be Inactive.</span><br>
Division: 
<input type="text" name="newdivisionname" size="40" placeholder="Enter new division name">

<input type="submit" name="adddivisionname" value="Add">

<br>
<h4>Change Status</h4>
Select division:<br>
<select name="selectdivision" id="selectdivision">
<?php
// get all divisions for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM divisions ORDER BY DivisionId';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build divisions combo box from above query
while($pbadivisions = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$divisionid = $pbadivisions['DivisionId'];
	$pbadivision = $pbadivisions['DivisionName'];
	$pbastatus = $pbadivisions['Status'];

		echo '<option value = ' . $divisionid . '>' . $pbadivision . '/'. $pbastatus.'</option>';
}

?>
</select>
<input type="submit" value="Change Status" name="changedivisionstatus">

<br><br>

</form>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
		
</script>
</body></html>