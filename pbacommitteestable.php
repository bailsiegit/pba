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
//add new committee to committees table
if(isset($_POST['addcommittee']) && !empty($_POST['newcommitteename']))
{
	$formnewcommittee = htmlentities(trim($_POST['newcommitteename']));
	$formcommitteedesc = htmlentities(trim($_POST['newcommitteedesc']));
	$q = "INSERT INTO committees (CommitteeName, CommDesc, Status) VALUES (?, ?, 'Active')"; 
	require('../connecttopba.php');
	$r = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($r, "ss", $formnewcommittee, $formcommitteedesc);
	mysqli_stmt_execute($r);
	mysqli_close($link);
	if($r){echo '<p style="color:red">New committee added</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>
<?php
// change status of committee
if(isset($_POST['changecommitteestatus']))
{
	$formcommittee = htmlentities($_POST['selectcommittee']);
	$q = "SELECT Status FROM committees WHERE CommitteeId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formcommittee);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	$pbastatus = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$currstatus = $pbastatus['Status'];
	$newstatus = ($currstatus == "Active") ? "Inactive" : "Active";
	$q = "UPDATE committees SET Status = '$newstatus' WHERE CommitteeId = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formcommittee);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	if($r){echo '<p style="color:red">Committee status changed</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>

This page is used to add new committees and set Active/Inactive status of existing entries.
<hr>
<form action="pbacommitteestable.php" method="POST">

<h3>Committees</h3>
<h4>Add new committee</h4>
<span style="color:red;">Please check the Status list before entering a new committee name.<br>
The committee name may exist, but be Inactive.</span><br>
Committee Name: 
<input type="text" name="newcommitteename" size="40" placeholder="Enter new committee name"><br>
Description (optional): 
<input type="text" name="newcommitteedesc" size="40">
<input type="submit" name="addcommittee" value="Add">

<br>
<h4>Change Status</h4>
Select committee:<br>
<select name="selectcommittee" id="selectcommittee">
<?php
// get all committees for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM committees ORDER BY CommitteeName';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build commmittees combo box from above query
while($pbacommittees = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$committeeid = $pbacommittees['CommitteeId'];
	$pbacommittee = $pbacommittees['CommitteeName'];
	$pbastatus = $pbacommittees['Status'];

		echo '<option value = ' . $committeeid . '>' . $pbacommittee . '/'. $pbastatus.'</option>';
}

?>
</select>
<input type="submit" value="Change Status" name="changecommitteestatus">

</form>
<br>
<?php
include('pbaincludes/pbafooter.html');
?>

<script>
	
</script>
</body></html>