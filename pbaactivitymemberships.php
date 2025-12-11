<?php
//Rev 1 19/11/2025
//this page is part of the activity group
//it displays members after the selection of a year and membersip category
//members can be added to the displayed year and category
session_start();
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();
	load(); //redirect to login page
}
$page_title = 'Activity';
include('pbaincludes/pbaheader.html');

$_SESSION['timeoutstart'] = time();

?>

<?php
if($_SESSION['accesslevel'] < 1)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<?php
//download membership List
if(isset($_POST['downloadmembership']) && $_POST['selectedmembership'] > 0) {
	
	$formmembership = htmlentities($_POST['selectedmembership']);
	$formyear = htmlentities($_POST['selectedyear']);
	$titles = array("Year", "Membership Type", "FirstName", "LastName", "Phone", "Email", "Address", "Suburb");
	$path = 'downloads/pbamembershiplist.csv';
	$emailsfile = fopen($path, "w");
	fputcsv($emailsfile, $titles); //put titles in output file
		require('../connecttopba.php');
		$q = 'SELECT yr, mn, members.FirstName, members.LastName, members.Mobile, members.Email, members.Numberandstreet, members.Suburb FROM	
		(SELECT years.YearText, membertypes.Type, MembId FROM ((memberships 
		INNER JOIN years ON memberships.Year = years.YearId)
		INNER JOIN membertypes ON memberships.Mtype = membertypes.MemBTypeId) 
		WHERE memberships.Year = ? AND memberships.Mtype = ?) 
		membershipdata (yr, mn, mbid) 
		INNER JOIN members ON membershipdata.mbid = members.MemberID';
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "ii", $formyear, $formmembership);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
		{
			$row['Mobile'] = "'".$row['Mobile'];
			fputcsv($emailsfile, $row);
		}
	$footnotes1[] = "This file may contain sensitive personal information";
	$footnotes2[] = "Please abide by the PBA Privacy Policy.";
	fputcsv($emailsfile, []);
	fputcsv($emailsfile, $footnotes1);
	fputcsv($emailsfile, $footnotes2);
	fclose($emailsfile); // close csv file
	mysqli_stmt_close($stmt); // close sql
	mysqli_close($link); // close database
	header('Location: pbadownloadcsv.php?fn=pbamembershiplist');
	exit;

}
?>

<?php
//add person to displayed membership group
if(isset($_POST['addperson']) && $_POST['selectname'] > 0 && $_POST['selectedmembership'] > 0)
{
	$formperson = htmlentities($_POST['selectname']);
	$formyear = htmlentities($_POST['selectedyear']);
	$formmembership = htmlentities($_POST['selectedmembership']);
	require('../connecttopba.php');
	//check if new entry is a duplicate
	
	$q = "INSERT IGNORE INTO memberships (MembId, Mtype, Year) VALUES (?, ?, ?)";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "iii", $formperson, $formmembership, $formyear);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	mysqli_close($link);
}
?>

<?php
//save GET values if sent
if(isset($_GET['mid']))
{
	$formmembership = htmlentities($_GET['mid']);
	$formyear = htmlentities($_GET['yid']);
}
?>
<!--table to layout top area-->
<table style="width:100%;"><tr>
<td style="width:50%; border:0px; background-color:white;">
<!-- page header-->
<h2>Display Members List</h2>
<?php
//add the sub menu	
require('pbaincludes/pbaactivitiesmenu.php');
?>
<!--search criteria form-->
<div>
<form action="pbaactivitymemberships.php" method = "POST">

<!-- //create year select combo box -->
<select name="selectedyear" id="selectedyear" onchange="loadMemberships()">	
<?php
// get all years for combo box
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
<!-- //create membership select combo box -->
<select name="selectedmembership" id="selectedmembership" onchange="displayMembership()">

<?php
// make memberships combo sticky and membership list correct
$my = $formyear;
$mt = (isset($formmembership)) ? $formmembership : 0;
include('pbaget_membtypes.php');

?>

</select>
<br>
	
	</td><td style="width:50%; background-color:white; border:0px;">
<?php
if($_SESSION['accesslevel'] > 1) //only show download button if user has permission
{
	?>
	Download membership list.  <input type="submit" value="Download" name="downloadmembership"><hr>
<?php
}
?>
<?php
//show add person area if authorised
if($_SESSION['accesslevel'] > 2){?>

	<br>Name:
	<select name="selectname" id="selectname">
	<option value="0">Select person...</option>
	<?php
	$q = "SELECT FirstName, LastName, MemberID FROM members ORDER BY LastName, FirstName";
	require('../connecttopba.php');
	$r = mysqli_query($link, $q);
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC)){
	echo '<option value="'.$row['MemberID'].'">'.$row['LastName'].', '.$row['FirstName'].'</option>';
	}
echo '</select>';
echo '<input type="submit" value="Add Person to Members" name="addperson">';
}
?>
	
	</td></tr></table>
</form>
</div>
<hr>
<div id="displaydata">
<?php
$my = $formyear;
$mt = (isset($formmembership)) ? $formmembership : 0;
if($mt > 0)
{
	include('pbadisplay_membership.php');
}
?>
</div>	
<?php
include('pbaincludes/pbafooter.html');
?>

	<script>
    function loadMemberships() {
        var year = document.getElementById("selectedyear").value;
		var type = document.getElementById("selectedmembership").value;
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "pbaget_membtypes.php?java=1&yid=" + year + "&mid=" + type, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById("selectedmembership").innerHTML = xhr.responseText;
				if (type > 0) {
					displayMembership();
				}
            }
        };
        xhr.send();
		document.getElementById("displaydata").innerHTML = "";
    }
    </script>
	<script>
    function displayMembership() {
        var year = document.getElementById("selectedyear").value;
		var add = document.getElementById("addperson");
		var membership = document.getElementById("selectedmembership").value;
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "pbadisplay_membership.php?java=1&yid=" + year + "&mid=" + membership, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
				if(membership < 1) {
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