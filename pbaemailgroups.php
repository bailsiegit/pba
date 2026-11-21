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

This page is used to add new email groups and set Active/Inactive status of existing entries.
<hr>
<form action="pbatableupdate.php" method="POST">
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
const select = document.getElementById("isteamaward");

select.addEventListener("change", function () {

    const option = this.options[this.selectedIndex];
    const isTeam = option.value == "1";

	const hierachy = document.getElementById("showhierachy");
    const hierlist = document.getElementById("awardhierachy");

	hierachy.style.display = isTeam ? "block" : "none";
	//notteam.style.display = isTeam ? "none" : "block";

    if (!isTeam){
	hierlist.value = "0";} //value for Individual - TeamId
	//else
	//team.value = "0"; //reset team list
});		
</script>
</body></html>