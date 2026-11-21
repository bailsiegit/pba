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
// add award to awards table
if(isset($_POST['addaward']) && !empty($_POST['newawardname']))
{
	$formnewaward = htmlentities(trim($_POST['newawardname']));
	$formawarddesc = htmlentities(trim($_POST['newawarddesc']));
	$formisteam = (int) htmlentities(trim($_POST['isteamaward']));
	$formawardhier = (int) htmlentities(trim($_POST['awardhierachy']));
	//check if isteam has been updated
	if($formisteam < 1)
	{
		echo '<p style="color:red">Update failed. Is this a team player award?</p>';
	}
	elseif($formisteam == 1 && $formawardhier == 0)
	{
		echo '<p style="color:red">Update failed. Is this a team player award?</p>';
	}
	else
	{
		require('../connecttopba.php');
		//open slot in displayorder for new award if team award
		if($formisteam == 1)
		{
			$q = "SELECT * FROM awards WHERE DisplayOrder > $formawardhier ORDER BY DisplayOrder";
			$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
			while($slideawards = mysqli_fetch_array($r, MYSQLI_ASSOC))
			{
				$newdo = $slideawards['DisplayOrder'] + 1;
				$thisaward = $slideawards['AwardID'];
				$q2 = "UPDATE awards SET DisplayOrder = $newdo WHERE AwardID = $thisaward";
				$r2 = mysqli_query($link, $q2, MYSQLI_STORE_RESULT);
			}
		}
		
		//add new award
		$formawardhier = ($formisteam == 1) ? $formawardhier + 1 : $formawardhier;
		$formisteam = $formisteam > 1 ? "No" : "Yes";
		$q = "INSERT INTO awards (AwardName, AwardDesc, Status, IsTeamAward, DisplayOrder) VALUES (?, ?, 'Active', ?, ?)"; 
		$r = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($r, "sssi", $formnewaward, $formawarddesc, $formisteam, $formawardhier);
		mysqli_stmt_execute($r);
		mysqli_close($link);
		if($r){echo '<p style="color:red">New award added</p>';}
		else {echo '<p style="color:red">Update failed</p>';}
	}
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
	if(mysqli_affected_rows($link) > 0){echo '<p style="color:red">Award status changed</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>

This page is used to add new awards and set Active/Inactive status of existing entries.
<hr>
<form action="pbaawardstable.php" method="POST">

<h4>Add new award</h4>
<span style="color:red;">Please check the Status list before entering a award.<br>
The award may exist, but be Inactive.</span><br>
Award Name: 
<input type="text" name="newawardname" size="40" placeholder="Enter new award name"><br>
Description (optional): 
<input type="text" name="newawarddesc" size="40" style="margin-top:3px; margin-bottom:3px;"><br>
Will this be awarded to a player? 
<select name="isteamaward" id="isteamaward">
<option value="0">Make a selection</option>
<option value="1">Yes</option>
<option value="2">No</option>
</select><br>
<div id="showhierachy" style="display:none;">
Select the award that is of next highest significance from the list:

<?php
//get ordered list of existing team awards
$sql = "SELECT * FROM awards WHERE IsTeamAward = 'Yes' ORDER BY DisplayOrder";
require('../connecttopba.php');
$r = mysqli_query($link, $sql, MYSQLI_STORE_RESULT);
echo '<select name="awardhierachy" id="awardhierachy">';
echo '<option value="0">Make a selection</option>';
while ($awardh = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	echo '<option value = '.$awardh['DisplayOrder'].'>'.$awardh['DisplayOrder'].'. '.$awardh['AwardName'].'</option>';
}
?>
</select>
</div>
<input type="submit" name="addaward" value="Add Award">

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

</form>
<br>
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