<?php
//Rev 1 19/11/2025
//this page is for copying a team from one year to the next
//the person and role is copied across to the new team
//when doen the new team is displayed

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
if($_SESSION['accesslevel'] < 4)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<?php
//has copy been submitted
if(isset($_POST['copyteam']))
{
	// get from team details in case no names have been selected
	$formteam = htmlentities($_POST['fromteam']);
	$formyear = htmlentities($_POST['fromyear']);
	
	if(empty($_POST['selectedcopy']))
	{
		echo '<span style="color:red">Please make a selection or go </span><a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbaactivityteams.php?yid='.$formyear.'&tid='.$formteam.'">Back</a>';
	}
	else
	{
		require('../connecttopba.php');
		//clean data input
		$selectedcopies = $_POST['selectedcopy'];
		$selectedteam = htmlentities($_POST['selectedteam']);
		$selectedyear = htmlentities($_POST['selectedyear']);
		//work through selectedcopies array creating new records
		foreach($selectedcopies as $value)
		{
			//get existing record to learn MemberID and Role
			$cleanvalue = htmlentities($value);
			$q = "SELECT * FROM teammembers WHERE TeamMembId = ?";
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "i", $cleanvalue);
			mysqli_stmt_execute($stmt);
			$r = mysqli_stmt_get_result($stmt);
			$record = mysqli_fetch_assoc($r);
			//create new record
			$q = "INSERT IGNORE INTO teammembers (MembId, Role, TeamId, Year) VALUES (?, ?, ?, ?)";
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "isii", $record['MembId'], $record['Role'], $selectedteam, $selectedyear);
			mysqli_stmt_execute($stmt);
			$r = mysqli_stmt_get_result($stmt);
		}
		// once new records completed show new team
		header('Location: pbaactivityteams.php?yid='.$selectedyear.'&tid='.$selectedteam.'');
		exit();
	}
}
//save GET values if sent
if(isset($_GET['yid']))
{
	$formteam = htmlentities($_GET['actid']);
	$formyear = htmlentities($_GET['yid']);
	// show back button to the from team page
	echo '<a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbaactivityteams.php?yid='.$formyear.'&tid='.$formteam.'">Back</a>';
}
//table to layout top area
echo '<table style="width:100%;"><tr>';
echo '<td style="width:50%; border:0px; background-color:white;">';
// page header
//get from-team year to show on page
$qyear = "SELECT YearText FROM years WHERE YearId = ?";
require('../connecttopba.php');
$stmt = mysqli_prepare($link, $qyear);
mysqli_stmt_bind_param($stmt, "i", $formyear);
mysqli_stmt_execute($stmt);
$ryear = mysqli_stmt_get_result($stmt);
$yeartext = mysqli_fetch_assoc($ryear);
//get from-team name to show on page
$qteam = "SELECT TeamName FROM teams WHERE TeamId = ?";
$stmt = mysqli_prepare($link, $qteam);
mysqli_stmt_bind_param($stmt, "i", $formteam);
mysqli_stmt_execute($stmt);
$rteam = mysqli_stmt_get_result($stmt);
$teamname = mysqli_fetch_assoc($rteam);

echo '<h3>'.$yeartext['YearText'].' - '.$teamname['TeamName'].'</h3>';
echo '<h2>Copy Team List to</h2>';
echo "<h4>Select target team:</h4>";

?>
<!--search criteria form-->
<form action="pbacopyteam.php" method = "POST">
<!--hide fromteam info to retain it after in $_POST-->
<input type="hidden" name="fromteam" value="<?php echo $formteam; ?>">
<input type="hidden" name="fromyear" value="<?php echo $formyear; ?>">
<!-- //create year select combo box -->
<select name="selectedyear" id="selectedyear">	
<?php
// get all year for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM years ORDER BY YearText';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
$pbayears = mysqli_fetch_array($r, MYSQLI_ASSOC);

// build years combo box from above query
while($pbayears = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$yearid = $pbayears['YearId'];
	$pbayear = $pbayears['YearText'];
	//make combobox sticky to current year
	if(date("Y") == $pbayear)
	{
		// set current to selected as this is the most likely requirement
		echo '<option value = ' . $yearid . ' selected="selected">' . $pbayear . '</option>'; //select the current year as that is most likely
	}
	else
	{
		echo '<option value = ' . $yearid . '>' . $pbayear . '</option>';
	}
}
?>
</select>
<!-- //create team select combo box -->
<select name="selectedteam" id="selectedteam">

<?php
// populate teams combo box
$q = "SELECT * FROM teams ORDER BY TeamName";
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
$pbateams = mysqli_fetch_array($r, MYSQLI_ASSOC);

//build team selection combo
while($pbateams = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	if($pbateams['TeamId'] == $formteam)
	{
		//select the from team as that is likely or close in the list of teams
		echo '<option value = '.$pbateams['TeamId'].' selected="selected">'.$pbateams['TeamName'].'</option>';
	}
	else
	{
		echo '<option value='.$pbateams['TeamId'].'>'.$pbateams['TeamName'].'</option>';
	}
}

?>
</select>
<br>
	
</td><td style="width:50%; background-color:white; border:0px;">
	
</td></tr></table>

<hr>
<table><tr><td style="border:0px; background-color:white;"> <!--table to layout lower page-->

<?php
// load team details
$teamQuery = "SELECT tmbid, rl, gp, tn, mbid, members.FirstName, members.LastName, tmid, yrid FROM
	(SELECT teammembers.TeamMembId, Role, GamesPlayed, teams.TeamName, MembId, teammembers.TeamId, teammembers.Year FROM ((teammembers 
	INNER JOIN years ON teammembers.Year = years.YearId)
	INNER JOIN teams ON teammembers.TeamId = teams.TeamId) WHERE teammembers.Year = ? AND teammembers.TeamId = ?) 
	teamdata (tmbid, rl, gp, tn, mbid, tmid, yrid) 
	INNER JOIN members ON teamdata.mbid = members.MemberID";
require('../connecttopba.php');
$stmt = mysqli_prepare($link, $teamQuery);
mysqli_stmt_bind_param($stmt, "ii", $formyear, $formteam);
mysqli_stmt_execute($stmt);
$teamResult = mysqli_stmt_get_result($stmt);

if (!$teamResult) 
{
	die('Team query failed: ' . mysqli_error($link));
}
if (mysqli_num_rows($teamResult) > 0) 
{	
	echo '<select size="10" name="selectedcopy[]" id="selectedcopy" multiple>';
	while ($team = mysqli_fetch_assoc($teamResult)) 
	{
		if(empty($team['rl']))
		{
			//if no role, show only names
			echo '<option value='.$team['tmbid'].'>'.$team['FirstName'].' '.$team['LastName'].'</option>';
		}
		else
		{
			//if role exists add hyphen and role to names
			echo '<option value='.$team['tmbid'].'>'.$team['FirstName'].' '.$team['LastName'].' - '.$team['rl'].'</option>';
		}
	}
	echo '</select>';
}
?>
<br><br>

</td>
<td style="border:0px; background-color:white;">Use ctrl or cmd to select mutliple people to copy to the target team.
<br><br><br>
<input type="submit" value="Copy" name="copyteam">
</form>
</td></tr></table>
<?php
include('pbaincludes/pbafooter.html');
?>

</body></html>