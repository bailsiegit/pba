<?php
//Rev 1 19/11/2025
//this page displays the volunteers activities
//the user can select volunteers by year or by role
//volunteers can be added when the year view is shown
//activity entries can be deleted by authorised users
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
//download volunteer List
if(isset($_POST['downloadvolunteer'])) 
{
	$formrole = htmlentities($_POST['selectedrole']);
	$formyear = htmlentities($_POST['selectedyear']);
	$titles = array("Year", "Volunteer Role", "First Name", "Last Name", "Phone", "Email", "Address", "Suburb");
	$path = 'downloads/pbavolunteerlist.csv';
	$emailsfile = fopen($path, "w");
	fputcsv($emailsfile, $titles); //put titles in output file
		require('../connecttopba.php');
		if(strlen($formrole) == 1) // if role is not selected then show roles the year
		{
			$q = "SELECT years.YearText, volunteers.Role, members.FirstName, members.LastName, 
			members.Mobile, members.Email, members.Numberandstreet, members.Suburb 
			FROM volunteers 
			INNER JOIN members ON volunteers.MembId = members.MemberId
			INNER JOIN years ON volunteers.Year = years.YearId 
			WHERE volunteers.Year = ?";
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "i", $formyear);
		}
		else // if role is selected, find role for all years
		{
			$q = "SELECT years.YearText, volunteers.Role, members.FirstName, members.LastName, 
			members.Mobile, members.Email, members.Numberandstreet, members.Suburb 
			FROM volunteers 
			INNER JOIN members ON volunteers.MembId = members.MemberId
			INNER JOIN years ON volunteers.Year = years.YearId 
			WHERE volunteers.Role = ? 
			ORDER BY years.YearId DESC";
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "s", $formrole);
		}
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		//$r = mysqli_query($link, $q);

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
	//mysqli_free_result($r);
	mysqli_stmt_close($stmt); // close sql
	mysqli_close($link); // close database
	header('Location: pbadownloadcsv.php?fn=pbavolunteerlist');
	//exit;

}
?>

<?php
//add person to displayed volunteers
if(isset($_POST['addperson']) && $_POST['selectname'] > 0 && !empty($_POST['volunteerrole']) && $_POST['selectedrole'] < 1)
{
	$formperson = htmlentities($_POST['selectname']);
	$formrole = (!empty($_POST['customvolrole'])) ? htmlentities($_POST['customvolrole']) : htmlentities($_POST['volunteerrole']);
	$formyear = htmlentities($_POST['selectedyear']);
	require('../connecttopba.php');
	
	//IGNORE will skip error if new entry is a duplicate
	$q = "INSERT IGNORE INTO volunteers (MembId, Role, Year) VALUES (?, ?, ?)";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "isi", $formperson, $formrole, $formyear);
	mysqli_stmt_execute($stmt);
			
	mysqli_close($link);
	$formrole = "a"; //ensure refresh shows year view
}
?>

<?php

if(isset($_GET['yid']))
{
	$formyear = htmlentities($_GET['yid']);
}

//table to layout top area
echo '<table style="width:100%;"><tr>';
echo '<td style="width:50%; border:0px; background-color:white;">';
// page header
echo '<h2>Display Volunteers List</h2>';

//add the sub menu	
require('pbaincludes/pbaactivitiesmenu.php');
?>
<!--search criteria form-->
<div><br>
<form action="pbaactivityvolunteers.php" method = "POST">
Select volunteers by year: 

<!-- //create year select combo box -->
<select name="selectedyear" id="selectedyear" onfocus="displayVolunteersByYear()" onchange="displayVolunteersByYear()">	
<?php
// get all year for combo box
if(isset($_POST['year'])) $formyear = htmlentities($_POST['year']);
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
mysqli_close($link);
?>
</select>
<br>
or<br>
Select volunteers by role: 
<!--// select volunteer by role-->
<select name="selectedrole" id="selectedrole" onchange="displayVolunteersByRole()">
<option value="0">Select a role...</option>
<?php
$q = "SELECT COUNT(MembId) as Qty, Role 
FROM volunteers
INNER JOIN members ON volunteers.MembId = members.MemberId
INNER JOIN years on volunteers.Year = years.YearId 
GROUP BY Role 
ORDER BY Qty DESC";
require('../connecttopba.php');
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
while($roles = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$selected = (isset($formrole) && $formrole == $roles['Role']) ? ' selected="selected"' : "";
	echo '<option value="'.$roles['Role'].'"'.$selected.'>'.$roles['Role'].'</option>';
}
?>
</select>
</td><td style="width:50%; background-color:white; border:0px;">
<?php
if($_SESSION['accesslevel'] > 1) //only show download button if user has permission
{
	?>
	Download volunteer list.  <input type="submit" value="Download" name="downloadvolunteer"><hr>
<?php
}
?>
<div id="addarea">
<?php
if($_SESSION['accesslevel'] > 2) // read write and above get access to add memberships
{
?>
	<br>Select view by year to add volunteers.<br>Name:
	<select style="margin:5px" name="selectname" id="selectname">
	<option value="0">Select person...</option>
<?php
	$q = "SELECT FirstName, LastName, MemberID 
	FROM members 
	ORDER BY LastName, FirstName";
	require('../connecttopba.php');
	$r = mysqli_query($link, $q);
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
	echo '<option value="'.$row['MemberID'].'">'.$row['LastName'].', '.$row['FirstName'].'</option>';
	}
	echo '</select>';
	echo '<br>Role: ';
?>
<select name="volunteerrole" id="selectvolrole" onchange="checkCustomOptionVol()">
<option value="0" selected="selected">Select role...</option>
    <?php
    require('../connecttopba.php');
    $q = 'SELECT Role FROM volunteers GROUP BY Role ORDER BY Role';
    $r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
    while ($volroles = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
        $thisrole = htmlspecialchars($volroles['Role']);
        echo '<option value="' . $thisrole . '">' . $thisrole . '</option>';
    }
    ?>
    <option value="custom">-- Add New Role --</option>
</select>

<input type="text" id="customvolrole" name="customvolrole" style="display:none;" placeholder="Enter new role">
<?php
	echo '<input type="submit" value="Add Person to Volunteers" name="addperson">';
}
?>
</div>
</td></tr></table>
</form>
</div>
<hr>
<div id="displaydata">

<?php
//process year selection - all volunteers for the selected year
$y = $formyear;
$rl = (isset($formrole)) ? $formrole : 0;
include('pbadisplay_volunteers.php');


?>
</div>
<?php
include('pbaincludes/pbafooter.html');
?>

	<script>
    function displayVolunteersByYear() {
		document.getElementById("selectedrole").value = "0"; // resets role combo if year is selected		
        var year = document.getElementById("selectedyear").value;
		var role = document.getElementById("selectedrole").value;
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "pbadisplay_volunteers.php?java=1&yid=" + year + "&rid=0", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
				document.getElementById("displaydata").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
		document.getElementById("addarea").style.visibility="visible";
    }
    </script>
		<script>
    function displayVolunteersByRole() {
        var role = document.getElementById("selectedrole").value;
		var year = document.getElementById("selectedyear").value;
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "pbadisplay_volunteers.php?java=1&rid=" + role + "&yid=" + year, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
				document.getElementById("displaydata").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
		document.getElementById("addarea").style.visibility="hidden";
    }
    </script>
	<script>
function checkCustomOptionVol() {
    var select = document.getElementById("selectvolrole");
    var customInput = document.getElementById("customvolrole");

    if (select.value === "custom") {
        customInput.style.display = "inline";  // Show input box
        customInput.focus();
    } else {
        customInput.style.display = "none";   // Hide input box
    }
}
</script>
</body></html>