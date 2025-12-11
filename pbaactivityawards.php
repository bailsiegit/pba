<?php
//Rev 3 11/12/2025
//this page is part of the activity group
//it displays award winners either by award of by year
//it provides for adding award winners
session_start();
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in or session expired
{
	// if not logged in or session timed out redirect to login page
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();	
	load(); //redirect to login page
}
$page_title = 'Activity'; //set page title for browser tab
include('pbaincludes/pbaheader.html'); //start with header and main menu

$_SESSION['timeoutstart'] = time(); // reset session timer

?>

<?php
// check if user access allows for page viewing
if($_SESSION['accesslevel'] < 1)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<?php
// check if GET parameters exist from delete records
if(isset($_GET['yid']))
{
	$addyear = htmlentities($_GET['yid']); // clean GET data
}
//add new award recipient to database
// check name and award are set to usable values
if(isset($_POST['addedname']) && $_POST['addedname'] > 0 && isset($_POST['addedaward']) && $_POST['addedaward'] > 0)
{
	// clean POST data
	$addperson = htmlentities(trim($_POST['addedname']));
	$addaward = htmlentities(trim($_POST['addedaward']));
	$addyear = htmlentities(trim($_POST['selectedyear']));
	$addcomment = htmlentities(trim($_POST['addedcomment']));
	// prepare sql to add award use IGNORE to avoid errors from duplicate attempt
	$q = "INSERT IGNORE INTO awardwinners (AwardId, YearId, MembId, Comments) VALUES (?, ?, ?, ?)";
	require('../connecttopba.php');
	// execute prepared sql to add record
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "iiis", $addaward, $addyear, $addperson, $addcomment);
	mysqli_stmt_execute($stmt);
	mysqli_close($link);
}
?>
<!-- page header-->
<table style="width:100%;"><tr>
<td style="background:white; width:50%; border:0px;"><h2>Display Awards List</h2>
<?php
//add the sub menu
require('pbaincludes/pbaactivitiesmenu.php');
?>
<!--search criteria form-->

Select an award or a year<br>

<form action="pbaactivityawards.php" method = "POST">
<!-- //create year select combo box -->
<select name="selectedyear" id="selectedyear" style="margin:5px;" onclick="displayByYear()">	
<?php
// get all years for combo box
require('../connecttopba.php');
// get years data from table
$q = 'SELECT * FROM years ORDER BY YearText';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build years combo box from above query
while($pbayears = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$yearid = $pbayears['YearId'];
	$pbayear = $pbayears['YearText'];
	//make combobox sticky to current year or last selected Year
	if(!isset($addyear) && date("Y") == $pbayear  ) //if no year specified, set to current year when found
	{
		$addyear = $yearid; //this is set to get the correct awards for display
		echo '<option value = ' . $yearid . ' selected="selected">' . $pbayear . '</option>';
	}
	elseif(isset($addyear) && $addyear == $yearid)
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
 to see all award recipients for that year.
<br>
<!-- //create award select combo box -->
<select name="selectedaward" id="selectedaward" style="margin:5px;" onchange="displayByAward()">
<option value = "0" selected="selected">Select an award...</option> <!--first row of combo provides instruction-->
<?php
// get all awards for combo box

require('../connecttopba.php');
$q = 'SELECT * FROM awards WHERE Status = "Active" ORDER BY AwardName';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build awards combo box from above query
while($pbaawards = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$awardid = $pbaawards['AwardID'];
	$pbaaward = $pbaawards['AwardName'];
	if(isset($_POST['award']))
	{
		$formaward = htmlentities($_POST['award']);
	}
	//make sticky combobox
	if(isset($formaward) && $formaward == $awardid)
	{
		echo '<option value = ' . $awardid . ' selected="selected">' . $pbaaward . '</option>';
	}
	else
	{
		echo '<option value = ' . $awardid . '>' . $pbaaward . '</option>';
	}
}
?>
</select>
 to see all recipients of that award.
</td><td style="background:white; width:50%; border:0px;">
<div id="addwinner">
<?php
//show add person area if authorised
if($_SESSION['accesslevel'] > 2)
{
?>
	<!-- //create award select combo box for adding award recipient-->
	<br>Award: 
	<select name="addedaward" id="addedaward" style="margin:5px;">
	<option value = "0" selected="selected">Select an award...</option>
<?php
	// get all awards for combo box
	require('../connecttopba.php');
	$q = 'SELECT * FROM awards WHERE Status = "Active" ORDER BY AwardName';
	$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

	// build awards combo box from above query
	while($pbaawards = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		$awardid = $pbaawards['AwardID'];
		$pbaaward = $pbaawards['AwardName'];
		//make sticky combobox
		if(isset($formaward) && $formaward == $awardid)
		{
			echo '<option value = ' . $awardid . ' selected="selected">' . $pbaaward . '</option>';
		}
		else
		{
			echo '<option value= '. $awardid . '>' . $pbaaward . '</option>';
		}
	}
?>
	</select>
	<!-- add awad comment input-->
	<br>Comment: <input type="text" name="addedcomment" size="40">
	<!--add name select combo-->
	<br>Name:
	<select name="addedname" id="addedname">
	<option value="0">Select person...</option>
<?php
	// get all name from table
	$q = "SELECT FirstName, LastName, MemberID FROM members WHERE InactivePerson = 0 ORDER BY LastName, FirstName";
	require('../connecttopba.php');
	$r = mysqli_query($link, $q);
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		echo '<option value="'.$row['MemberID'].'">'.$row['LastName'].', '.$row['FirstName'].'</option>';
	}
?>
	</select>
	<input type="submit" value="Add Award Winner" name="addperson" style="margin:5px 1px 1px 5px;">
	</div>
	</form>
<?php
}
?>	
	</td></tr></table> 

<hr>
<div id="displaydata"> <!--javascript creates display code for this div on change of award or year default is current year awards-->
<?php
//get all award winners for the current year as default display
if(isset($addyear) && $addyear > 0)
{
	$yid = $addyear;
	include("pbadisplay_awards.php");
}

?>
</div>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
function displayByYear() {
	document.getElementById("selectedaward").value = "0";
    var year = document.getElementById("selectedyear").value;
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "pbadisplay_awards.php?yid=" + year, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
			if(year < 1) {
				document.getElementById("displaydata").innerHTML = "";
			}
			else {
				document.getElementById("displaydata").innerHTML = xhr.responseText;
			}
        }
    };
    xhr.send();
	document.getElementById("addwinner").style.visibility="visible";
}
</script>
<script>
function displayByAward() {
    var award = document.getElementById("selectedaward").value;
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "pbadisplay_awards.php?aid=" + award, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
			if(award < 1) {
				document.getElementById("displaydata").innerHTML = "";
			}
			else {
				document.getElementById("displaydata").innerHTML = xhr.responseText;
			}
        }
    };
    xhr.send();
	document.getElementById("addwinner").style.visibility="hidden";
}
</script>
</body></html>