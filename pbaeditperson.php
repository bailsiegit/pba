<?php
//Rev 1 19/11/2025
//this page is called from the pbaperson page 
//to update person details and returns to the pbaperson page showing updated info
// Any personal data fields added or removed from this page must also be
// changed on pbaperson.php, pbaaddperson.php

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
<!-- <h2>People</h2> -->

<?php
if($_SESSION['accesslevel'] < 2)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<?php
//GET is always the entry point for this page (from pbaperson)
if(isset($_GET['pid']))
{
	$pid = htmlentities(trim($_GET['pid']));
	require('../connecttopba.php');
	// get all data for member if id passed via GET
	$q = 'SELECT *, members.Salutation AS memSal, salutations.Salutation AS salutationtext FROM members 
		INNER JOIN salutations ON members.Salutation = salutations.Salutationid WHERE members.MemberID = ?';
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $pid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
	//save the row of data into variables so either get or post source can be used in the form
	$formmembid = $row['MemberID'];
	$formsal = $row['memSal'];
	$formfn = $row['FirstName'];
	$formln = $row['LastName'];
	$formdob = $row['Birthdate'];
	$formaddress1 = $row['Numberandstreet'];
	$formsuburb = $row['Suburb'];
	$formstate = $row['State'];
	$formcountry = $row['Country'];
	$formpostcode = $row['Postcode'];
	$formpaddress1 = $row['PostStreet'];
	$formpsuburb = $row['PostSuburb'];
	$formpstate = $row['PostState'];
	$formppostcode = $row['PostPostcode'];
	$formnews = $row['Newsletter'];
	$formtag = $row['Tag'];
	$forminactive = $row['InactivePerson'];
	$formphone = $row['HomePhone'];
	$formmobile = $row['Mobile'];
	$formemail = $row['Email'];
	//mysqli_free_result;
	//mysqli_close;
}
if(isset($_POST['savechanges']))
{
	$posted = 1; // flag for POSTed data rather than GET data
	// put the POST data in the same variables as the MySQL data for display in the form
	$formmembid = htmlentities(trim($_POST['membid']));
	$formsal = htmlentities(trim($_POST['selectedsalut']));
	$formfn = htmlentities(trim($_POST['firstname']));
	$formln = htmlentities(trim($_POST['lastname']));
	$formdob = htmlentities(trim($_POST['dob']));
	$formaddress1 = htmlentities(trim($_POST['address1']));
	$formsuburb = strtoupper(htmlentities(trim($_POST['suburb'])));
	$formstate = htmlentities(trim($_POST['state']));
	$formcountry = htmlentities(trim($_POST['country']));
	$formpostcode = htmlentities(trim($_POST['pcode']));
	$formpaddress1 = htmlentities(trim($_POST['paddress1']));
	$formpsuburb = strtoupper(htmlentities(trim($_POST['psuburb'])));
	$formpstate = htmlentities(trim($_POST['pstate']));
	$formppostcode = htmlentities(trim($_POST['ppcode']));
	if(isset($_POST['newsletter']))
		{$formnews = 1;}
	else
		{$formnews = 0;}
	if(isset($_POST['tagged'])){$formtag = 1;}else{$formtag = 0;}
	if(isset($_POST['inactive'])){$forminactive = 1;}else{$forminactive = 0;}
	$formphone = htmlentities(trim($_POST['phone']));
	$formmobile = htmlentities(trim($_POST['mobile']));
	$formemail = htmlentities(trim($_POST['email']));
	
	// write all data back to database
	// build SQL statement
	$sql = "UPDATE members SET Salutation = ?, FirstName = ?, LastName = ?, Birthdate = ?, ";
	$sql = $sql."Numberandstreet = ?, Suburb = ?, State = ?, Country = ?, Postcode = ?, ";
	$sql = $sql."PostStreet = ?, PostSuburb = ?, PostState = ?, PostPostcode = ?, ";
	$sql = $sql."Newsletter = ?, Tag = ?, InactivePerson = ?, HomePhone = ?, Mobile = ?, ";
	$sql = $sql."Email = ? WHERE MemberID = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $sql);
	mysqli_stmt_bind_param($stmt, "issssssssssssiiisssi", $formsal, $formfn, $formln, $formdob, $formaddress1, $formsuburb, $formstate, $formcountry,
	$formpostcode, $formpaddress1, $formpsuburb, $formpstate, $formppostcode, $formnews, $formtag, $forminactive, $formphone, $formmobile, 
	$formemail, $formmembid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	
	echo 'data write success';
	//redirect to read only page
	require('pbalogin_tools.php');
	load("pbaperson.php?pid=$formmembid");
}

?>

<table style="width:80%;">
<tr><td style="background-color:white; border:0px;"><h2>
<?php if(isset($row['FirstName'])) 
{
	echo $row['FirstName'].' '.$row['LastName'];
}
else
{
	echo 'New Person';
}
?>
</h2></td>
<td  style="background-color:white; border:0px; vertical-align:bottom;"></td>
</tr></table>


<!-- //create form for person editing and creating -->
<form action="pbaeditperson.php" method = "POST">
<table width="90%">
<tr><th width="15%">Details</th><th width="30%"></th><th width="15%"></th><th width="30%"></th></tr>
<tr><td style="text-align:right; width:25%">Salutation: </td>

<td>
<!-- //create salutation select combo box -->
<select name="selectedsalut" id="selectedsalut">
<?php
// get all salutation data for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM salutations';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build salutations combo box from above query
while($sals = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$sid = $sals['SalutationId'];
	$salutation = $sals['Salutation'];
	//make combobox sticky
	if(isset($formsal) && $formsal == $sid){
		echo '<option value = ' . $sid . ' selected="selected">' . $salutation . '</option>';}
	//elseif(isset($_GET['sid']) && $_GET['sid'] == $sid){
	//	echo '<option value = ' . $sid . ' selected="selected">' . $salutation . '</option>';}
	else{
		ECHO '<option value = ' . $sid . '>' . $salutation . '</option>';}
}

?>

</select></td></tr>
<tr><td style="text-align:right;"><input type="hidden" name="membid" value="<?php if(isset($formmembid))echo $formmembid;?>">First Name: </td><td><input type="text" name="firstname" value="<?php if(isset($formfn))echo $formfn;?>"></td>
		</tr>
<tr><td style="text-align:right;">Last Name: </td><td><input type="text" name="lastname" value="<?php if(isset($formln))echo $formln;?>"></td></tr>
<tr><td style="text-align:right;">Date of Birth: </td><td><input type="date" name="dob" value="<?php if(isset($formdob))echo date("Y-m-d",strtotime($formdob));?>"></td>
		<td>Postal Address</td></tr>
<tr><td style="text-align:right;">Address: </td><td><input type="text" name="address1" value="<?php if(isset($formaddress1))echo $formaddress1;?>"></td>
		<td style="text-align:right;">Address: </td><td><input type="text" name="paddress1" value="<?php if(isset($formpaddress1))echo $formpaddress1;?>"></td></tr>
<tr><td style="text-align:right;">Suburb: </td><td><input type="text" name="suburb" value="<?php if(isset($formsuburb))echo $formsuburb;?>"></td>
			<td style="text-align:right;">Suburb: </td><td><input type="text" name="psuburb" value="<?php if(isset($formpsuburb))echo $formpsuburb;?>"></td></tr>
<tr><td style="text-align:right;">State: </td><td><input type="text" name="state" value="<?php if(isset($formstate))echo $formstate;?>"></td>
			<td style="text-align:right;">State: </td><td><input type="text" name="pstate" value="<?php if(isset($formpstate))echo $formpstate;?>"></td></tr>
<tr><td style="text-align:right;">Postcode: </td><td><input type="text" name="pcode" value="<?php if(isset($formpostcode))echo $formpostcode;?>"></td>
			<td style="text-align:right;">Postcode: </td><td><input type="text" name="ppcode" value="<?php if(isset($formppostcode))echo $formppostcode;?>"></td></tr>
<tr><td style="text-align:right;">Country: </td><td><input type="text" name="country" value="<?php if(isset($formcountry))echo $formcountry;?>"></td></tr>
<tr><td style="text-align:right;">Phone: </td><td><input type="text" name="phone" value="<?php if(isset($formphone))echo $formphone;?>"></td></tr>
<tr><td style="text-align:right;">Mobile: </td><td><input type="text" name="mobile" value="<?php if(isset($formmobile))echo $formmobile;?>"></td>
<tr><td style="text-align:right;">Email: </td><td><input type="email" name="email" value="<?php if(isset($formemail))echo $formemail;?>"></td></tr>
<tr><td style="text-align:right;">Newsletter: </td><td><input type="checkbox" name="newsletter" <?php if(isset($formnews)&&$formnews)echo 'checked';?>></td></tr>
<tr><td style="text-align:right;">Tagged: </td><td><input type="checkbox" name="tagged" <?php if(isset($formtag)&&$formtag)echo 'checked';?>></td></tr>
<tr><td style="text-align:right;">InActive: </td><td><input type="checkbox" name="inactive" <?php if(isset($forminactive)&&$forminactive)echo 'checked';?>></td></tr>
</table>

<input type="submit" value="Save" name="savechanges">

</form>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>