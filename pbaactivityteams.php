<?php
//Rev 1 19/11/2025
//this page is part of the activity group
//it displays teams when a year and team name is selected
//the teams pick list is dependent on the year selection
//pepole can be added to the seleced team and games played can be added
//people can have a role eg. coach captain manager
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
//download team List
if(isset($_POST['downloadteam']) && $_POST['selectedteam'] > 0) {
	
	$formteam = htmlentities($_POST['selectedteam']);
	$formyear = htmlentities($_POST['selectedyear']);
	$titles = array("FirstName", "LastName", "DOB", "Phone", "Email", "Games");
	$path = 'downloads/pbateamlist.csv';
	$emailsfile = fopen($path, "w");
	fputcsv($emailsfile, $titles); //put titles in output file
		require('../connecttopba.php');
		$q = 'SELECT members.FirstName, members.LastName, members.Birthdate, members.Mobile, members.Email, gp FROM	
		(SELECT GamesPlayed AS gp, 
		teams.TeamName AS tn, 
		MembId AS mbid 
		FROM teammembers 
		INNER JOIN years ON teammembers.Year = years.YearId
		INNER JOIN teams ON teammembers.TeamId = teams.TeamId 
		WHERE teammembers.Year = ? AND teammembers.TeamId = ?)  
		AS teamdata  
		INNER JOIN members ON teamdata.mbid = members.MemberID';
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "ii", $formyear, $formteam);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);

		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
		{
			$row['Mobile'] = "'".$row['Mobile']; // add lead ' to keep leading zero
			$row['Birthdate'] = $row['Birthdate'] == "0000-00-00" ? "" : $row['Birthdate']; // blank cell when no DOB
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
	header('Location: pbadownloadcsv.php?fn=pbateamlist');
	exit;

}
	
//add person to displayed team
if(isset($_POST['addperson']) && $_POST['selectname'] > 0 && $_POST['selectedteam'] > 0)
{
	$formperson = htmlentities($_POST['selectname']);
	$formrole = htmlentities($_POST['addrole']);
	$formyear = htmlentities($_POST['selectedyear']);
	$formteam = htmlentities($_POST['selectedteam']);
	require('../connecttopba.php');
	// use IGNORE to prevent errors if new record is a duplicate	
	$q = "INSERT IGNORE INTO teammembers (MembId, Role, TeamId, Year) VALUES (?, ?, ?, ?)";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "isii", $formperson, $formrole, $formteam, $formyear);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	mysqli_close($link);
}
//save GET values if sent
if(isset($_GET['tid']))
{
	$formteam = htmlentities($_GET['tid']);
	$formyear = htmlentities($_GET['yid']);
}
//table to layout top area
echo '<table style="width:100%;"><tr>';
echo '<td style="width:50%; border:0px; background-color:white;">';
// page header
echo '<h2>Display Team List</h2>';


//add the sub menu	
require('pbaincludes/pbaactivitiesmenu.php');
?>
<!--search criteria form-->
<div>
<form action="pbaactivityteams.php" method = "POST">

<!-- //create year select combo box -->
<select name="selectedyear" id="selectedyear" onchange="loadTeams()">	
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
<!-- //create team select combo box -->
<select name="selectedteam" id="selectedteam" onchange="displayTeam()">

<?php
// populate teams combo box
$ty = $formyear;
$tn = (isset($formteam)) ? $formteam : 0;

include('pbaget_teams.php');


?>
</select>
<br>
	
</td><td style="width:50%; background-color:white; border:0px;">
<?php
if($_SESSION['accesslevel'] > 1) //only show download button if user has permission
{
	?>
Download team list.  <input type="submit" value="Download" name="downloadteam"><hr>
<?php
}
?>

<?php
if($_SESSION['accesslevel'] > 2)
{
?>
	<br>Name:
	<select name="selectname" id="selectname">
	<option value="0">Select person...</option>
<?php
	$q = "SELECT FirstName, LastName, MemberID FROM members ORDER BY LastName, FirstName";
	require('../connecttopba.php');
	$r = mysqli_query($link, $q);
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		echo '<option value="'.$row['MemberID'].'">'.$row['LastName'].', '.$row['FirstName'].'</option>';
	}
	echo '</select>';
	echo '<br>Role: ';
	echo '<input type="text" name="addrole" size="35"> ';
	echo '<input type="submit" value="Add Person to Team" name="addperson">';
}
?>
	
</td></tr></table>
</form>
</div>
<hr>
<?php
// load team details
$ty = $formyear;
$tn = (isset($formteam)) ? $formteam : 0;
?>
<div id="displaydata">
<?php
if($tn > 0)
{
include('pbadisplay_team.php');
}
?>
</div>


<?php
include('pbaincludes/pbafooter.html');
?>

<script>
    function loadTeams() {
        var year = document.getElementById("selectedyear").value;
		var team = document.getElementById("selectedteam").value;
        var xhr = new XMLHttpRequest();
		xhr.open("GET", "pbaget_teams.php?java=1&yid=" + year + "&tid=" + team, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById("selectedteam").innerHTML = xhr.responseText;
				if (team > 0) {
					displayTeam();
				}
			}
        };
        xhr.send();
		document.getElementById("displaydata").innerHTML = "";
    }
    </script>
	<script>
    function displayTeam() {
        var year = document.getElementById("selectedyear").value;
		var add = document.getElementById("addperson");
		var team = document.getElementById("selectedteam").value;
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "pbadisplay_team.php?java=1&yid=" + year + "&tid=" + team, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
				if(team < 1) {
					document.getElementById("displaydata").innerHTML = "";
				}
				else {
					document.getElementById("displaydata").innerHTML = xhr.responseText;
				}
            }
        };
        xhr.send();
    }
	
    // Use event listener instead of overwriting window.onload
    //window.addEventListener("load", function() {
    //    loadTeams();
    //});
    </script>
</body></html>