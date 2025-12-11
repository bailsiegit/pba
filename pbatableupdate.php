<?php
//Rev 1 19/11/2025
//this page is called from the maintenance item on the main menu
//this page provides the ability to add to the base tables and
//also make items in each base table active or Inactive

session_start();
//check that user is logged in and has not been idle for too long
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();
	load(); //redirect to login page
}
$page_title = 'Tables';
include('pbaincludes/pbaheader.html');

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

<?php
//add new team to teams table
if(isset($_POST['addteamname']) && !empty($_POST['newteamname']))
{
	$formnewteam = htmlentities(trim($_POST['newteamname']));
	$formteamdesc = htmlentities(trim($_POST['newteamdesc']));
	$q = "INSERT INTO teams (TeamName, Descr, Status) VALUES (?, ?, 'Active')"; 
	require('../connecttopba.php');
	$r = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($r, "ss", $formnewteam, $formteamdesc);
	mysqli_stmt_execute($r);
	mysqli_close($link);
	if($r){echo '<p style="color:red">New team added</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>
<?php
// change status of team
if(isset($_POST['changeteamstatus']))
{
	$formteam = htmlentities($_POST['selectteam']);
	$q = "SELECT Status FROM teams WHERE TeamId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formteam);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	$pbastatus = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$currstatus = $pbastatus['Status'];
	$newstatus = ($currstatus == "Active") ? "Inactive" : "Active";
	$q = "UPDATE teams SET Status = '$newstatus' WHERE TeamId = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formteam);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	if($r){echo '<p style="color:red">Team status changed</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
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

<?php
// add award to awards table
if(isset($_POST['addaward']) && !empty($_POST['newawardname']))
{
	$formnewaward = htmlentities(trim($_POST['newawardname']));
	$formawarddesc = htmlentities(trim($_POST['newawarddesc']));
	$q = "INSERT INTO awards (AwardName, AwardDesc, Status) VALUES (?, ?, 'Active')"; 
	require('../connecttopba.php');
	$r = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($r, "ss", $formnewaward, $formawarddesc);
	mysqli_stmt_execute($r);
	mysqli_close($link);
	if($r){echo '<p style="color:red">New award added</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>
<?php
// change status of award
if(isset($_POST['changeawardstatus']))
{
	$formaward = htmlentities($_POST['selectaward']);
	$q = "SELECT Status FROM awards WHERE AwardID = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formaward);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	$pbastatus = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$currstatus = $pbastatus['Status'];
	$newstatus = ($currstatus == "Active") ? "Inactive" : "Active";
	$q = "UPDATE awards SET Status = '$newstatus' WHERE AwardID = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formaward);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	if($r){echo '<p style="color:red">Award status changed</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>
<?php
//add new email group to emailgroups table
if(isset($_POST['addemailgroup']) && !empty($_POST['newemailgroupname']))
{
	$formnewemailgroup = htmlentities(trim($_POST['newemailgroupname']));
	$formemailgroupdesc = htmlentities(trim($_POST['newemailgroupdesc']));
	$q = "INSERT INTO emailgroups (EGroupName, EGroupDesc) VALUES (?, ?)"; 
	require('../connecttopba.php');
	$r = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($r, "ss", $formnewemailgroup, $formemailgroupdesc);
	mysqli_stmt_execute($r);
	mysqli_close($link);
	if($r){echo '<p style="color:red">New emailgroup added</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>

This page is used to add entries to key data tables and set Active/Inactive status of existing entries.
<hr>
<form action="pbatableupdate.php" method="POST">
<h3>Membership Types</h3>
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


<br><hr>
<h3>Teams</h3>
<h4>Add new team</h4>
Team names must be in the format <span style="color:red">League </span><span style="color:blue">Age </span><span style="color:green">Sex 
</span><span style="color:orange">Division</span>.<br>
Examples:<br>
<span style="color:red">WABL </span><span style="color:blue">Under 16 </span><span style="color:green">Girls </span><span style="color:orange">Div 1</span><br>
<span style="color:red">District </span><span style="color:green">Men </span><span style="color:orange">B Grade</span><br>
<span style="color:red">SBL </span><span style="color:green">Women</span><br>
<br>
Team Name: 
<input type="text" name="newteamname" size="40" placeholder="Enter new team name"><br>
Description (optional): 
<input type="text" name="newteamdesc" size="40">
<input type="submit" name="addteamname" value="Add">

<br>
<h4>Change Status</h4>
Select team:<br>
<select name="selectteam" id="selectteam">
<?php
// get all teams for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM teams ORDER BY TeamName';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build teams combo box from above query
while($pbateams = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$teamid = $pbateams['TeamId'];
	$pbateam = $pbateams['TeamName'];
	$pbastatus = $pbateams['Status'];

		echo '<option value = ' . $teamid . '>' . $pbateam . '/'. $pbastatus.'</option>';
}

?>
</select>
<input type="submit" value="Change Status" name="changeteamstatus">

<br><hr>

<h3>Committees</h3>
<h4>Add new committee</h4>
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

<br><hr>

<h4>Add new award</h4>
Award Name: 
<input type="text" name="newawardname" size="40" placeholder="Enter new award name"><br>
Description (optional): 
<input type="text" name="newawarddesc" size="40">
<input type="submit" name="addaward" value="Add">

<br>
<h4>Change Status</h4>
Select award:<br>
<select name="selectaward" id="selectaward">
<?php
// get all awards for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM awards ORDER BY AwardName';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build awards combo box from above query
while($pbaawards = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$awardid = $pbaawards['AwardID'];
	$pbaaward = $pbaawards['AwardName'];
	$pbastatus = $pbaawards['Status'];

		echo '<option value = ' . $awardid . '>' . $pbaaward . '/'. $pbastatus.'</option>';
}

?>
</select>
<input type="submit" value="Change Status" name="changeawardstatus">

<br><hr>

<h3>Email Groups</h3>
<h4>Add new email group</h4>
Email Group Name: 
<input type="text" name="newemailgroupname" size="40" placeholder="Enter new committee name"><br>
Description (optional): 
<input type="text" name="newemailgroupdesc" size="40">
<input type="submit" name="addemailgroup" value="Add">

<br><br>

</form>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>