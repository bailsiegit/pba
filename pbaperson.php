<?php
// Rev 1 19/11/2025
//this page is called from various places to show the details of a person
// Any personal data fields added or removed from this page must also be
// changed on pbaeditperson.php, pbaaddperson.php

# this page displays the details of a single person
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
# check the user has access to this page
if($_SESSION['accesslevel'] < 1)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<?php
//check if last page added records
if(isset($_GET['adds']))
{
	$adds = htmlentities($_GET['adds']);
	if($adds < 2)
	{
		$message = $adds . ' activity record has been added.';
	}else{
		$message = $adds . ' activity records have been added.';
	}
	echo '<p style="color:red;">'.$message.'</p>';
}
# save the person id if it passed via GET parameter
if(isset($_GET['pid']))
{
	$pid = htmlentities($_GET['pid']);
}
# get the all data plus the salutation text for the person
require('../connecttopba.php');
$q = 'SELECT *, members.Salutation AS memSal, salutations.Salutation AS salutationtext FROM members 
INNER JOIN salutations ON members.Salutation = salutations.SalutationId WHERE members.MemberID = ?';
$stmt = mysqli_prepare($link, $q);
mysqli_stmt_bind_param($stmt, "i", $pid);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
$person = mysqli_fetch_array($r, MYSQLI_ASSOC);

//get games played by selected person
$games = 0;

$qgp = 'SELECT SUM(GamesPlayed) AS games FROM teammembers WHERE MembId = ?';
$stmt = mysqli_prepare($link, $qgp);
mysqli_stmt_bind_param($stmt, "i", $pid);
mysqli_stmt_execute($stmt);
$rgp = mysqli_stmt_get_result($stmt);
$games = mysqli_fetch_array($rgp, MYSQLI_ASSOC);

mysqli_close($link);

# display the persons name in large font at the top
echo '<table style="width:80%;">';
echo '<tr><td style="background-color:white; border:0px;"><h2>'.$person['FirstName'].' '.$person['LastName'].'</h2> ';
if($games['games'] > 0){ echo $games['games'].' games played';}
echo '</td>';
echo '<td  style="background-color:white; border:0px; vertical-align:bottom;">';
// show edit button if authorised
if($_SESSION['accesslevel'] > 2)
{
echo '<button><a style="text-decoration:none; color:black; font-weight:bold;" href="pbaeditperson.php?pid='.$pid.'&sid='.$person['memSal'].'">';
echo 'Update Details</a></button>';
}
echo '</td></tr></table>';

# add the menu item to see activity records for the person	
require('pbaincludes/pbapersonmenu.php');

# display all the details for the person
	echo '<p> </p><table width="90%">';
		
	echo '<tr><th width="15%">Details</th><th width="30%"></th><th width="15%"></th><th width="30%"></th></tr>';
	
	echo '<tr><td style="text-align:right; width:25%">Salutation: </td><td>' . $person['Salutation'] . '</td></tr>';
	echo '<tr><td style="text-align:right;">First Name: </td><td>' . $person['FirstName'] . '</td></tr>';
	echo '<tr><td style="text-align:right;">Last Name: </td><td>' . $person['LastName'] . '</td></tr>';
	echo '<tr><td style="text-align:right;">Date of Birth: </td><td>';
	// show full DOB if authorised
	if($_SESSION['accesslevel'] > 1)
	{
		if($person['Birthdate'] != "0000-00-00"){echo date("d/m/Y", strtotime($person['Birthdate']));}
	}
	else
	{
		if($person['Birthdate'] != "0000-00-00"){echo date("Y", strtotime($person['Birthdate']));}
	}
	// show all details if authorised
	if($_SESSION['accesslevel'] > 1)
	{
	echo '</td><td>Postal Address</td></tr>';
	//echo '<tr><td style="text-align:right;">Address: </td><td>' . $person['Numberandstreet'] . '</td>
	//		<td style="text-align:right;">Postal Address: </td><td>' . $person['PostAddressLine1'] . '</td></tr>';
	echo '<tr><td style="text-align:right;">Address: </td><td>' . $person['Numberandstreet'] . '</td>
			<td style="text-align:right;">Address: </td><td>' . $person['PostStreet'] . '</td></tr>';
	echo '<tr><td style="text-align:right;">Suburb: </td><td>' . $person['Suburb'] . '</td>
			<td style="text-align:right;">Suburb: </td><td>' . $person['PostSuburb'] . '</td></tr>';
	echo '<tr><td style="text-align:right;">State: </td><td>' . $person['State'] . '</td>
			<td style="text-align:right;">State: </td><td>' . $person['PostState'] . '</td></tr>';
	echo '<tr><td style="text-align:right;">Postcode: </td><td>' . $person['Postcode'] . '</td>
			<td style="text-align:right;">Postcode: </td><td>' . $person['PostPostcode'] . '</td></tr>';
	echo '<tr><td style="text-align:right;">Country: </td><td>' . $person['Country'] . '</td></tr>';
	echo '<tr><td style="text-align:right;">Phone: </td><td>' . $person['HomePhone'] . '</td></tr>';
	echo '<tr><td style="text-align:right;">Mobile: </td><td>' . $person['Mobile'] . '</td></tr>';
	echo '<tr><td style="text-align:right;">Email: </td><td>' . $person['Email'] . '</td></tr>';
	}
	else
	{
		echo '</td></tr>';
	}
	if($person['Newsletter']==1){
		echo '<tr><td style="text-align:right;">Newsletter: </td><td>&#x2611</td></tr>';}
	else {echo '<tr><td style="text-align:right;">Newsletter: </td><td>&#x2610</td></tr>';}
	if($person['Tag']==1){
		echo '<tr><td style="text-align:right;">Tagged: </td><td>&#x2611</td></tr>';}
	else {echo '<tr><td style="text-align:right;">Tagged: </td><td>&#x2610</td></tr>';}
	if($person['InactivePerson']==1){
		echo '<tr><td style="text-align:right;">InActive: </td><td>&#x2611</td></tr>';}
	else {echo '<tr><td style="text-align:right;">InActive: </td><td>&#x2610</td></tr>';}
	echo '</table>';



?>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>