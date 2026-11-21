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
//add new grade to grades table
if(isset($_POST['addgradename']) && !empty($_POST['newgradename']))
{
	$formnewgrade = htmlentities(trim($_POST['newgradename']));
	$q = "INSERT INTO grades (GradeName, Status) VALUES (?, 'Active')"; 
	require('../connecttopba.php');
	$r = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($r, "s", $formnewgrade);
	mysqli_stmt_execute($r);
	mysqli_close($link);
	if($r){echo '<p style="color:red">New grade added</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>
<?php
// change status of grade
if(isset($_POST['changegradestatus']))
{
	$formgrade = htmlentities($_POST['selectgrade']);
	$q = "SELECT Status FROM grades WHERE GradeId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formgrade);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	$pbastatus = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$currstatus = $pbastatus['Status'];
	$newstatus = ($currstatus == "Active") ? "Inactive" : "Active";
	$q = "UPDATE grades SET Status = '$newstatus' WHERE GradeId = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formgrade);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);	
	if(mysqli_affected_rows($link) > 0){echo '<p style="color:red">Grade status changed</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>


This page is used to add grades and set Active/Inactive status of existing entries.
<hr>
<form action="pbagradestable.php" method="POST">
<h3>Grades</h3>
<h4>Add new grade</h4>
<span style="color:red;">Please check the Status list before entering a new grade.<br>
The grade may exist, but be Inactive.</span><br>
Grade Name: 
<input type="text" name="newgradename" size="40" placeholder="Enter new grade name">

<input type="submit" name="addgradename" value="Add">

<br>
<h4>Change Status</h4>
Select grade:<br>
<select name="selectgrade" id="selectgrade">
<?php
// get all grades for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM grades ORDER BY GradeName';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build grades combo box from above query
while($pbagrades = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$gradeid = $pbagrades['GradeId'];
	$pbagrade = $pbagrades['GradeName'];
	$pbastatus = $pbagrades['Status'];

		echo '<option value = ' . $gradeid . '>' . $pbagrade . '/'. $pbastatus.'</option>';
}

?>
</select>
<input type="submit" value="Change Status" name="changegradestatus">

<br><br>

</form>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
const select = document.getElementById("isgradeaward");

select.addEventListener("change", function () {

    const option = this.options[this.selectedIndex];
    const isgrade = option.value == "1";

	const hierachy = document.getElementById("showhierachy");
    const hierlist = document.getElementById("awardhierachy");

	hierachy.style.display = isgrade ? "block" : "none";
	//notgrade.style.display = isgrade ? "none" : "block";

    if (!isgrade){
	hierlist.value = "0";} //value for Individual - gradeId
	//else
	//grade.value = "0"; //reset grade list
});		
</script>
</body></html>