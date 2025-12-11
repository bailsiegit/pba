<?php
//Rev 2 11/12/2025
//this page is part of the activity group
//it displays a committee after the user selects a year and committee
//the list of committees available to select are year dependent
//it provides for the addition of members to the selected committee
//the first member of a committee must be added via the people area.
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
//download committee List
if(isset($_POST['downloadcommittee']) && $_POST['selectedcommittee'] > 0) 
{
	$formcommittee = htmlentities($_POST['selectedcommittee']);
	$formyear = htmlentities($_POST['selectedyear']);
	$titles = array("Year", "Committee", "FirstName", "LastName", "Phone", "Email", "Address", "Suburb");
	$path = 'downloads/pbacommitteelist.csv';
	$emailsfile = fopen($path, "w");
	fputcsv($emailsfile, $titles); //put titles in output file
		require('../connecttopba.php');
		$q = 'SELECT yr, cn, members.FirstName, members.LastName, members.Mobile, members.Email, members.Numberandstreet, members.Suburb 
		FROM	
		(SELECT years.YearText AS yr, 
		committees.CommitteeName AS cn, 
		MembId AS mbid 
		FROM committeememb 
		INNER JOIN years ON committeememb.Year = years.YearId
		INNER JOIN committees ON committeememb.CommId = committees.CommitteeId 
		WHERE committeememb.Year = ? AND committeememb.CommId = ?) 
		AS committeedata 
		INNER JOIN members ON committeedata.mbid = members.MemberID';
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "ii", $formyear, $formcommittee);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);

		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
		{
			$row['Mobile'] = (empty($row['Mobile'])) ? "" : "'".$row['Mobile'];
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
	header('Location: pbadownloadcsv.php?fn=pbacommitteelist');
}
?>

<?php
//add person to displayed committee
if(isset($_POST['addperson']) && $_POST['selectname'] > 0 && $_POST['selectedcommittee'] > 0)
{
	$formperson = htmlentities($_POST['selectname']);
	$formrole = htmlentities($_POST['addrole']);
	$formyear = htmlentities($_POST['selectedyear']);
	$formcommittee = htmlentities($_POST['selectedcommittee']);
	require('../connecttopba.php');
	//add record use IGNORE to prevent error if record is a duplicate
	$q = "INSERT IGNORE INTO committeememb (MembId, Role, CommId, Year) VALUES (?, ?, ?, ?)";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "isii", $formperson, $formrole, $formcommittee, $formyear);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	mysqli_close($link);
}
?>

<?php
//save GET values if sent
if(isset($_GET['cid']))
{
	$formcommittee = htmlentities($_GET['cid']);
	$formyear = htmlentities($_GET['yid']);
}

//table to layout top area
echo '<table style="width:100%;"><tr>';
echo '<td style="width:50%; border:0px; background-color:white;">';
// page header
echo '<h2>Display Committee List</h2>';

//add the sub menu	
require('pbaincludes/pbaactivitiesmenu.php');
?>
<!--search criteria form-->
<div>
<form action="pbaactivitycommittees.php" method = "POST">

<!-- //create year select combo box -->
<select name="selectedyear" id="selectedyear" onchange="loadCommittees()">	
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
<!-- //create committee select combo box -->
<?php
$cy = $formyear;
$cn = (isset($formcommittee)) ? $formcommittee : 0;
?>

<select name="selectedcommittee" id="selectedcommittee" onchange="displayCommittee()">

<?php
// make committees combo sticky and committee list correct

include('pbaget_committees.php');
?>

</select>
<br>
	
	</td><td style="width:50%; background-color:white; border:0px;">
<?php
if($_SESSION['accesslevel'] > 1) //only show download button if user has permission
{
	?>
	Download committee list.  <input type="submit" value="Download" name="downloadcommittee"><hr>
<?php
}
?>
<?php
if($_SESSION['accesslevel'] > 2) // read write and above get access to add memberships
{
?>
	<br>Name:
	<select name="selectname" id="selectname">
	<option value="0">Select person...</option>
<?php
	$q = "SELECT FirstName, LastName, MemberID FROM members WHERE InactivePerson = 0 ORDER BY LastName, FirstName";
	require('../connecttopba.php');
	$r = mysqli_query($link, $q);
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
	echo '<option value="'.$row['MemberID'].'">'.$row['LastName'].', '.$row['FirstName'].'</option>';
	}
	echo '</select>';
	echo '<br>Role: ';
	echo '<input type="text" name="addrole" size="35"> ';
	echo '<input type="submit" value="Add Person to Committee" name="addperson">';
}
?>
	
</td></tr></table>
</form>
</div>
<hr>


	
	
<div id="displaydata">
<?php
if(isset($_GET['cid']))
{
	$c = htmlentities($_GET['cid']);
	$y = htmlentities($_GET['yid']);
	include('pbadisplay_committee.php');
	unset($_GET['cid']);
	
}
elseif(isset($_POST['selectedyear']))
{
	$c = htmlentities($_POST['selectedcommittee']);
	$y = htmlentities($_POST['selectedyear']);
	include('pbadisplay_committee.php');
	unset($_POST['selectedyear']);
}
?>
</div>

<?php
include('pbaincludes/pbafooter.html');
?>

	<script>
    function loadCommittees() {
        var year = document.getElementById("selectedyear").value;
		var committee = document.getElementById("selectedcommittee").value;
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "pbaget_committees.php?java=1&yid=" + year + "&cid=" + committee, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById("selectedcommittee").innerHTML = xhr.responseText;
				if (committee > 0) {
					displayCommittee()
				}
            }
        };
        xhr.send();
		document.getElementById("displaydata").innerHTML = "";
    }
    </script>
	<script>
    function displayCommittee() {
        var year = document.getElementById("selectedyear").value;
		var add = document.getElementById("addperson");
		var committee = document.getElementById("selectedcommittee").value;
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "pbadisplay_committee.php?java=1&yid=" + year + "&cid=" + committee, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
				if(committee < 1) {
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