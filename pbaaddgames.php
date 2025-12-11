<?php
// Rev 1 19/11/2025
//this page is called from the teams activity page
//it shows the games played for a team member and allows for adding to that total
//on close the user is returned to the same team display
session_start();
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();
	load(); //redirect to login page
}
$page_title = 'People';
include('pbaincludes/pbaheader.html');

$_SESSION['timeoutstart'] = time();

?>

<?php
if($_SESSION['accesslevel'] < 3)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<?php

if(isset($_GET['pid']))
{
	$formteam = htmlentities($_GET['tid']);
	$formyear = htmlentities($_GET['yid']);
	$formpid = htmlentities($_GET['pid']);
	$formgp = htmlentities($_GET['gp']);
}

// page header
echo '<h2>Add Played Games</h2>';

//add the sub menu	
require('pbaincludes/pbaactivitiesmenu.php');

?>
<?php
if(isset($_POST['update'])) //has form been submitted
{
	$formteam = htmlentities($_POST['team']);
	$formyear = htmlentities($_POST['year']);
	$formpid = htmlentities($_POST['person']);
	$formplayed = (int)htmlentities($_POST['played']);
	//$formplayed = empty($formplayed) ? 0 : $formplayed;
	$addedgames = (int)htmlentities(trim($_POST['extragames']));
	$games = $formplayed + $addedgames;
	if($addedgames != 0)
	{
		require('../connecttopba.php');
		$q = "UPDATE teammembers SET GamesPlayed = ? WHERE TeamId = ? AND MembId = ? AND Year = ?";
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "iiii", $games, $formteam, $formpid, $formyear);
		mysqli_stmt_execute($stmt);
	}

//redirect back to team list
require('pbalogin_tools.php');
load("pbaactivityteams.php?tid=$formteam&yid=$formyear");
}
?>
<div>
<hr>
<?php
//find persons name
require('../connecttopba.php');

$q = 'SELECT FirstName, LastName FROM members WHERE MemberID = ?';
$stmt = mysqli_prepare($link, $q);
mysqli_stmt_bind_param($stmt, "i", $formpid);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);

while($row = mysqli_fetch_array($r, MYSQLI_ASSOC)){
$person = $row['FirstName'].' '.$row['LastName'];
}

$q = 'SELECT TeamName FROM teams WHERE TeamId = ?';
$stmt = mysqli_prepare($link, $q);
mysqli_stmt_bind_param($stmt, "i", $formteam);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);

while($row = mysqli_fetch_array($r, MYSQLI_ASSOC)){
$team = $row['TeamName'];
}

$q = 'SELECT YearText FROM years WHERE YearId = ?';
$stmt = mysqli_prepare($link, $q);
mysqli_stmt_bind_param($stmt, "i", $formyear);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);

while($row = mysqli_fetch_array($r, MYSQLI_ASSOC)){
$year = $row['YearText'];
}

?>
<form action="pbaaddgames.php" method = "POST">
<input type="hidden" name="person" value="<?php if(isset($formpid)) echo $formpid;?>">
<input type="hidden" name="year" value="<?php if(isset($formyear)) echo $formyear;?>">
<input type="hidden" name="team" value="<?php if(isset($formteam)) echo $formteam;?>">
<input type="hidden" name="played" value="<?php if(isset($formgp)) echo $formgp;?>">
<!--Add
<input type="number" name="extragames"><br>-->
<?php
//echo ' for '.$person.' in <br>'.$team.' of '.$year;
echo $person.' has played <b>'.$formgp.'</b> games for the <br>';
echo $year.' '.$team.'<br>';
echo 'How many would you like to add? <input type"number" name="extragames"> ';
?>

<input type="submit" value="Update" name="update">
</form>
</div>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>