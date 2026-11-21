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
//add new league to leagues table
if(isset($_POST['addleaguename']) && !empty($_POST['newleaguename']))
{
	$formnewleague = htmlentities(trim($_POST['newleaguename']));
	$q = "INSERT INTO leagues (LeagueName, Status) VALUES (?, 'Active')"; 
	require('../connecttopba.php');
	$r = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($r, "s", $formnewleague);
	mysqli_stmt_execute($r);
	mysqli_close($link);
	if($r){echo '<p style="color:red">New league added</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>
<?php
// change status of league
if(isset($_POST['changeleaguestatus']))
{
	$formleague = htmlentities($_POST['selectleague']);
	$q = "SELECT Status FROM leagues WHERE LeagueId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formleague);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	$pbastatus = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$currstatus = $pbastatus['Status'];
	$newstatus = ($currstatus == "Active") ? "Inactive" : "Active";
	$q = "UPDATE leagues SET Status = '$newstatus' WHERE LeagueId = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formleague);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	if(mysqli_affected_rows($link) > 0){echo '<p style="color:red">League status changed</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>


This page is used to add leagues and set Active/Inactive status of existing entries.
<hr>
<form action="pbaleaguestable.php" method="POST">
<h3>Leagues</h3>
<h4>Add new league</h4>
<span style="color:red;">Please check the Status list before entering a new league.<br>
The league may exist, but be Inactive.</span><br>
League: 
<input type="text" name="newleaguename" size="40" placeholder="Enter new league name">

<input type="submit" name="addleaguename" value="Add">

<br>
<h4>Change Status</h4>
Select league:<br>
<select name="selectleague" id="selectleague">
<?php
// get all leagues for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM leagues ORDER BY LeagueId';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build leagues combo box from above query
while($pbaleagues = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$leagueid = $pbaleagues['LeagueId'];
	$pbaleague = $pbaleagues['LeagueName'];
	$pbastatus = $pbaleagues['Status'];

		echo '<option value = ' . $leagueid . '>' . $pbaleague . '/'. $pbastatus.'</option>';
}

?>
</select>
<input type="submit" value="Change Status" name="changeleaguestatus">

<br><br>

</form>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
		
</script>
</body></html>