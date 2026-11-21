<?php
//Rev 1 19/11/2025
//this page is part of the people group
//this page list all the teams that the person has been on

session_start();
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	session_unset();
	session_destroy();
	header('Location: pbalogin.php'); //redirect to login page
	exit();
}
$page_title = 'People';
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

if(isset($_GET['pid']))
{
	$pid = htmlentities($_GET['pid']);
}

require('../connecttopba.php');
// get the persons name to put at the top of the page
$q = 'SELECT FirstName, LastName FROM members 
WHERE members.MemberID = ?';
$stmt = mysqli_prepare($link, $q);
mysqli_stmt_bind_param($stmt, "i", $pid);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
$person = mysqli_fetch_array($r, MYSQLI_ASSOC);
// put the persons name at the top of the page
echo '<h2>'.$person['FirstName'].' '.$person['LastName'].'</h2>';

//add the sub menu	
require('pbaincludes/pbapersonmenu.php');

// get a list of all the teams the person has been on
$q = 'SELECT 
tm.Role, 
tm.GamesPlayed, 
y.YearText, 
y.YearId, 
tm.TeamId, 
tt.TeamName 
FROM teammembers tm
JOIN years  y ON tm.YearId = y.YearId
JOIN teamstext tt ON tm.TeamId = tt.TeamEntryId 
WHERE tm.MembId = ? 
ORDER BY YearText DESC';
$stmt = mysqli_prepare($link, $q);
mysqli_stmt_bind_param($stmt, "i", $pid);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($r)<1)
{
	echo '<p><br>No record of '.$person['FirstName'].' being on a team.<br><br></p>';
}
else
{
	echo '<p> </p><table width="90%">';
		
	echo '<tr><th width="15%">Year</th><th width="30%">Team</th><th width="15%">Role</th><th>Games</th></tr>';
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		echo '<tr><td>'.$row['YearText'].'</td><td><a href="pbaactivityteams.php?tid='.$row['TeamId'].'&yid='.$row['YearId'].'">' . $row['TeamName'] . '</a></td>
			<td>'.$row['Role'].'</td><td>'.$row['GamesPlayed'].'</tr>';
	}
		echo '</table>';
}

mysqli_close($link);
?>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>