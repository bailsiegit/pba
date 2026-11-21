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
//add new season to seasons table
if(isset($_POST['addseasonname']) && !empty($_POST['newseasonname']))
{
	$formnewseason = htmlentities(trim($_POST['newseasonname']));
	$q = "INSERT INTO seasons (SeasonName, Status) VALUES (?, 'Active')"; 
	require('../connecttopba.php');
	$r = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($r, "s", $formnewseason);
	mysqli_stmt_execute($r);
	mysqli_close($link);
	if($r){echo '<p style="color:red">New season added</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>
<?php
// change status of season
if(isset($_POST['changeseasonstatus']))
{
	$formseason = htmlentities($_POST['selectseason']);
	$q = "SELECT Status FROM seasons WHERE SeasonId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formseason);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	$pbastatus = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$currstatus = $pbastatus['Status'];
	$newstatus = ($currstatus == "Active") ? "Inactive" : "Active";
	$q = "UPDATE seasons SET Status = '$newstatus' WHERE SeasonId = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formseason);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	if(mysqli_affected_rows($link) > 0){echo '<p style="color:red">Season status changed</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>


This page is used to add seasons and set Active/Inactive status of existing entries.
<hr>
<form action="pbaseasonstable.php" method="POST">
<h3>Seasons</h3>
<h4>Add new season</h4>
<span style="color:red;">Please check the Status list before entering a new season.<br>
The season may exist, but be Inactive.</span><br>
Season: 
<input type="text" name="newseasonname" size="40" placeholder="Enter new season name">

<input type="submit" name="addseasonname" value="Add">

<br>
<h4>Change Status</h4>
Select season:<br>
<select name="selectseason" id="selectseason">
<?php
// get all seasons for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM seasons ORDER BY SeasonId';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build seasons combo box from above query
while($pbaseasons = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$seasonid = $pbaseasons['SeasonId'];
	$pbaseason = $pbaseasons['SeasonName'];
	$pbastatus = $pbaseasons['Status'];

		echo '<option value = ' . $seasonid . '>' . $pbaseason . '/'. $pbastatus.'</option>';
}

?>
</select>
<input type="submit" value="Change Status" name="changeseasonstatus">

<br><br>

</form>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
		
</script>
</body></html>