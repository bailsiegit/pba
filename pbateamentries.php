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
// add new team entry
if(isset($_POST['addnewteam']))
{
	$formnewleague = htmlentities(trim($_POST['addleague']));
	$formnewseason = htmlentities(trim($_POST['addseason']));
	$formnewgrade = htmlentities(trim($_POST['addgrade']));
	$formnewgender = htmlentities(trim($_POST['addgender']));
	$formnewdivision = htmlentities(trim($_POST['adddivision']));
	$formnewteamname = htmlentities(trim($_POST['addteamname']));
	$formyear = htmlentities(trim($_POST['selectedyear']));
	$q = "INSERT IGNORE INTO teamentries (LeagueId, SeasonId, GradeId, GenderId, DivisionId, TeamNameId, YearId) VALUES (?, ?, ?, ?, ?, ?, ?)"; 
	require('../connecttopba.php');
	$r = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($r, "iiiiiii", $formnewleague, $formnewseason, $formnewgrade, $formnewgender, $formnewdivision, $formnewteamname, $formyear);
	mysqli_stmt_execute($r);
	mysqli_close($link);
	if(mysqli_stmt_affected_rows($r) > 0){echo '<p style="color:red">New team added</p>';}
	else {echo '<p style="color:red">Update failed</p>';}
}
?>

<?php
if(isset($_GET['yid']))
{
	$formyear = htmlentities($_GET['yid']);
}
?>


This page is used to add teams for each new season.<br>
Avoid using the "None" option wherever possible.<br><br>

<form action="pbateamentries.php" method = "POST">
<!-- //create year select combo box -->
<select name="selectedyear" id="selectedyear" onclick="displayTeams()">	
<?php
// get all year for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM years ORDER BY YearText';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build years combo box from above query
while($pbayears = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$yearid = $pbayears['YearId'];
	$pbayear = $pbayears['YearText'];
	//make combobox sticky to current year or last selected Year
	if(!isset($formyear) && date("Y") == $pbayear  ) //if no year specified, set to current year when found
	{
		$formyear = $yearid; //this is set to get the correct teams for the combo - this years teams
		echo '<option value = ' . $yearid . ' selected="selected">' . $pbayear . '</option>';
	}
	elseif(isset($formyear) && $formyear == $yearid)
	{
		echo '<option value = ' . $yearid . ' selected="selected">' . $pbayear . '</option>';
	}
	else
	{
		echo '<option value = ' . $yearid . '>' . $pbayear . '</option>';
	}
}
?>
</select>

<hr>
<div id="displaydata">
<?php
if(isset($formyear))
{
	$ty = $formyear;
	include('pbadisplay_teamentries.php');
}
?>
</div>
</form>
<?php
include('pbaincludes/pbafooter.html');
?>

<script>
    function displayTeams() {
        var year = document.getElementById("selectedyear").value;
		//var add = document.getElementById("addperson");
		//var team = document.getElementById("selectedteam").value;
		//var dnld = document.getElementById("showdownload");
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "pbadisplay_teamentries.php?java=1&yid=" + year, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
				document.getElementById("displaydata").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
</script>
</body></html>