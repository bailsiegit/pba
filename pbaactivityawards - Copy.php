<?php
//Rev 1 19/11/2025
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
// check if GET parameters exist
if(isset($_GET['aid']))
{
	$formaward = htmlentities($_GET['aid']); // clean GET data
}
//add new award recipient to database
// check name and award are set to usable values
if(isset($_POST['addedname']) && $_POST['addedname'] > 0 && isset($_POST['addedaward']) && $_POST['addedaward'] > 0)
{
	// clean POST data
	$addperson = htmlentities(trim($_POST['addedname']));
	$addaward = htmlentities(trim($_POST['addedaward']));
	$addyear = htmlentities(trim($_POST['addedyear']));
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

Select an award or a year
<form action="pbaactivityawards.php" method = "POST">

<!-- //create year select combo box -->
<select name="selectedyear" id="selectedyear" style="margin:5px;" onchange="displayByYear()">	
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
<?php
//show add person area if authorised
if($_SESSION['accesslevel'] > 2)
{?>
	<!-- //create year select combo box for add award winner-->
	Year: 
	<select name="addedyear" id="addedyear" style="margin:50px 5px 5px 5px;">	
<?php
	// get all year for combo box
	//if(isset($_POST['year']))
	//{
	//	$formyear = htmlentities($_POST['year']);
	//}
	require('../connecttopba.php');
	$q = 'SELECT * FROM years ORDER BY YearText';
	$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

	// build years combo box from above query
	while($pbayears = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		$yearid = $pbayears['YearId'];
		$pbayear = $pbayears['YearText'];
		//make combobox sticky to current year
		if(date("Y") == $pbayear)
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
	</form>
<?php
}
?>	
	</td></tr></table> 

<hr>
<div id="displaydata"> <!--javascript creates display code for this div on change of award or year default is current year awards-->
<?php
require('../connecttopba.php');
//get all award winners for the current year as default display
$thisyeartext = date("Y", time());
// query to get yearid for current year
$qyear = "SELECT YearText, YearId FROM years WHERE YearText = ?";
require('../connecttopba.php');
$stmt = mysqli_prepare($link, $qyear);
mysqli_stmt_bind_param($stmt, "s", $thisyeartext);
mysqli_stmt_execute($stmt);
$ryear = mysqli_stmt_get_result($stmt);
$thisyear = mysqli_fetch_assoc($ryear);
$thisyearid = $thisyear['YearId'];

// get a list of all the award winners for the year
$q = "SELECT cm, aw, members.FirstName, members.LastName, mbid, awid, yrid 
FROM
(SELECT 
Comments AS cm, 
awards.AwardName AS aw, 
MembId AS mbid, 
awards.AwardId AS awid, 
awardwinners.YearId AS yrid 
FROM awardwinners 
INNER JOIN years ON awardwinners.YearId = years.YearId 
INNER JOIN awards ON awardwinners.AwardId = awards.AwardID 
WHERE awardwinners.YearId = $thisyearid 
ORDER BY awardwinners.YearId DESC) 
AS awarddata  
INNER JOIN members ON awarddata.mbid = members.MemberID";

require('../connecttopba.php');
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
if(mysqli_num_rows($r)<1) //were any records returned
{
	echo '<p><br>No record of any awards in '.$thisyear['YearText'].'.<br><br></p>';
}
else
{
	echo '<p> </p><table width="90%">';
	if($_SESSION['accesslevel'] > 3) // God access level gets delete column
	{
		echo '<tr><th>Award</th><th>Winner</th><th>Comments</th><th>Delete</th></tr>';
	}
	else
	{
		echo '<tr><th>Award</th><th>Winner</th><th>Comments</th></tr>';
	}
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		if($_SESSION['accesslevel'] > 3) // God access can delete records and get belete button
		{
			echo '<tr><td>'.$row['aw'].'</td><td><a href="pbaperson.php?pid='.$row['mbid'].'">'.$row['FirstName'].' '.$row['LastName'].'</a></td><td>'.$row['cm'].'</td>
			<td><a onclick="return confirm(\'Are you sure?\');" class="buttonlink" href="pbadeleterecords.php?pid='.$row['mbid'].'&aid='.$row['awid'].'&yid='.$row['yrid'].'">Delete</a></td></tr>';
		}
		else // all other access levels only get display of data
		{
			echo '<tr><td>'.$row['aw'].'</td><td><a href="pbaperson.php?pid='.$row['mbid'].'">'.$row['FirstName'].' '.$row['LastName'].'</a></td><td>'.$row['cm'].'</td></tr>';
		}
	}
	echo '</table>';
}

mysqli_close($link);

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
    }
    </script>
</body></html>