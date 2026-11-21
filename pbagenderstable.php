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
//add new gender to genders table
if(isset($_POST['addgendername']) && !empty($_POST['newgendername']))
{
	$formnewgender = htmlentities(trim($_POST['newgendername']));
	$q = "INSERT INTO genders (GenderName, Status) VALUES (?, 'Active')"; 
	require('../connecttopba.php');
	$r = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($r, "s", $formnewgender);
	mysqli_stmt_execute($r);
	mysqli_close($link);
	if($r){echo '<p style="color:red">New gender added</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>
<?php
// change status of gender
if(isset($_POST['changegenderstatus']))
{
	$formgender = htmlentities($_POST['selectgender']);
	$q = "SELECT Status FROM genders WHERE GenderId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formgender);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	$pbastatus = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$currstatus = $pbastatus['Status'];
	$newstatus = ($currstatus == "Active") ? "Inactive" : "Active";
	$q = "UPDATE genders SET Status = '$newstatus' WHERE GenderId = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formgender);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	if(mysqli_affected_rows($link) > 0){echo '<p style="color:red">Gender status changed</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>


This page is used to add genders and set Active/Inactive status of existing entries.
<hr>
<form action="pbagenderstable.php" method="POST">
<h3>Genders</h3>
<h4>Add new gender</h4>
<span style="color:red;">Please check the Status list before entering a new gender.<br>
The gender may exist, but be Inactive.</span><br>
Gender: 
<input type="text" name="newgendername" size="40" placeholder="Enter new gender name">

<input type="submit" name="addgendername" value="Add">

<br>
<h4>Change Status</h4>
Select gender:<br>
<select name="selectgender" id="selectgender">
<?php
// get all genders for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM genders ORDER BY GenderId';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build genders combo box from above query
while($pbagenders = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$genderid = $pbagenders['GenderId'];
	$pbagender = $pbagenders['GenderName'];
	$pbastatus = $pbagenders['Status'];

		echo '<option value = ' . $genderid . '>' . $pbagender . '/'. $pbastatus.'</option>';
}

?>
</select>
<input type="submit" value="Change Status" name="changegenderstatus">

<br><br>

</form>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
		
</script>
</body></html>