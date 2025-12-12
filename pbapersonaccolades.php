<?php
//Rev 1 12/12/2025
//this age is part of the people group
//this page lists all the volunteer roles the person has had

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
$q = 'SELECT *, salutations.Salutation AS salutationtext FROM members 
INNER JOIN salutations ON members.Salutation = salutations.Salutationid WHERE members.MemberID = ?';
$stmt = mysqli_prepare($link, $q);
mysqli_stmt_bind_param($stmt, "i", $pid);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
//$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
$person = mysqli_fetch_array($r, MYSQLI_ASSOC);
// add persons name at top of page
echo '<h2>'.$person['FirstName'].' '.$person['LastName'].'</h2>';

//add sub menu
require('pbaincludes/pbapersonmenu.php');

// get a list of all the accolades the person has received
$q = 'SELECT Accolade, Details, years.YearText FROM accolades 
INNER JOIN years ON accolades.YearId = years.YearId
 WHERE MembId = ? ORDER BY YearText DESC';
$stmt = mysqli_prepare($link, $q);
mysqli_stmt_bind_param($stmt, "i", $pid);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
//$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
if(mysqli_num_rows($r)<1)
{
	echo '<p><br>No record of '.$person['FirstName'].' receiving an accolade.<br><br></p>';
}
else
{
	echo '<p> </p><table width="90%">';
		
	echo '<tr><th width="15%">Year</th><th width="30%">Accolade</th><th>Deatils</tr>';
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		echo '<tr><td>'.$row['YearText'].'</td><td>' . $row['Accolade'] . '</td><td>'.$row['Details'].'</td></tr>';
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