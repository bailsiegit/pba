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
//add new teamname to teamnames table
if(isset($_POST['addteamnamename']) && !empty($_POST['newteamnamename']))
{
	$formnewteamname = htmlentities(trim($_POST['newteamnamename']));
	$q = "INSERT INTO teamnames (TeamName, Status) VALUES (?, 'Active')"; 
	require('../connecttopba.php');
	$r = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($r, "s", $formnewteamname);
	mysqli_stmt_execute($r);
	mysqli_close($link);
	if($r){echo '<p style="color:red">New team name added</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>
<?php
// change status of teamname
if(isset($_POST['changeteamnamestatus']))
{
	$formteamname = htmlentities($_POST['selectteamname']);
	$q = "SELECT Status FROM teamnames WHERE TeamNameId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formteamname);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	$pbastatus = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$currstatus = $pbastatus['Status'];
	$newstatus = ($currstatus == "Active") ? "Inactive" : "Active";
	$q = "UPDATE teamnames SET Status = '$newstatus' WHERE TeamNameId = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formteamname);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	if(mysqli_affected_rows($link) > 0){echo '<p style="color:red">Team name status changed</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>


This page is used to add team names and set Active/Inactive status of existing entries.<br>
Team names are used to identify different teams in the same grade.
<hr>
<form action="pbateamnamestable.php" method="POST">
<h3>Team Names</h3>
<h4>Add new team name</h4>
<span style="color:red;">Please check the Status list before entering a new team name.<br>
The team name may exist, but be Inactive.</span><br>
Team Name: 
<input type="text" name="newteamnamename" size="40" placeholder="Enter new team name name">

<input type="submit" name="addteamnamename" value="Add">

<br>
<h4>Change Status</h4>
Select team name:<br>
<select name="selectteamname" id="selectteamname">
<?php
// get all teamnames for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM teamnames ORDER BY TeamNameId';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build teamnames combo box from above query
while($pbateamnames = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$teamnameid = $pbateamnames['TeamNameId'];
	$pbateamname = $pbateamnames['TeamName'];
	$pbastatus = $pbateamnames['Status'];

		echo '<option value = ' . $teamnameid . '>' . $pbateamname . '/'. $pbastatus.'</option>';
}

?>
</select>
<input type="submit" value="Change Status" name="changeteamnamestatus">

<br><br>

</form>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
		
</script>
</body></html>