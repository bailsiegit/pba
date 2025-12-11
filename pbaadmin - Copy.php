<?php
//Rev 1 19/11/2025
//this page is a list of administrative tasks
//the tasks displayed depends on the user access level
//all users can see the change password area
session_start();
//check that user is logged in and has not been idle for too long
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();
	load(); //redirect to login page
}
$page_title = 'Admin';
include('pbaincludes/pbaheader.html');

$_SESSION['timeoutstart'] = time(); //reset login timeout marker

?>
<h2>Admin</h2>
<hr>

<?php
if($_SESSION['accesslevel'] > 1)
{ 
	//check user has access to get email lists
	//if email list button active
	if(isset($_POST['emaillist']))
	{
		$eg = $_POST['emailgroup']; //$eg is 1 for newsletter and 2 for others
		$titles = array("FirstName", "LastName", "Email");
		$emailsfile = fopen("pbaemails.csv", "w");
		fputcsv($emailsfile, $titles); //put titles in output file
		if(isset($eg) && $eg == 1)
		{
			require('../connecttopba.php');
			$r = mysqli_query($link, "SELECT FirstName, LastName, Email FROM members WHERE Newsletter > 0",MYSQLI_STORE_RESULT);
			while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
			{
				fputcsv($emailsfile, $row);
			}
		fclose($emailsfile);
		mysqli_free_result($r);
		mysqli_close($link);
		
		}
		elseif(isset($eg) && $eg == 2)
		{
			require('../connecttopba.php');
			$r = mysqli_query($link, "SELECT FirstName, LastName, Email FROM members WHERE Tag > 0",MYSQLI_STORE_RESULT);
			while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
			{
				fputcsv($emailsfile, $row);
			}
		fclose($emailsfile);
		mysqli_free_result($r);
		mysqli_close($link);
		}
	}
	elseif(isset($_POST['cleartags']))
	{
		require('../connecttopba.php');
		$r = mysqli_query($link, "UPDATE members SET Tag = 0");
		if($r)
		{
			echo '<p style="color:red">All tags cleared</p><br>';
		}
	}
?>
	Create Email List
	<br><br>
	<form action="pbaadmin.php" method="POST">
	<input type="radio" name="emailgroup" id="news" value="1">
	<label for="news">Newsletter</label><br>
	<input type="radio" name="emailgroup" id="tag" value="2">
	<label for="tag">Tagged</label><br><br>
	<input type="submit" name="emaillist" value="Select">
<?php
	if(isset($_POST['emailgroup']))
	{
		echo '<button><a href="pbaemails.csv" style="color:black; text-decoration:none;" download>
		Export Results</a></button>';
	}
?>
	<br><br>
<?php
	if($_SESSION['accesslevel'] > 2)
	{
?>
		<input type="submit" name="cleartags" value="Clear Tags">
		&nbsp (clear all tagged people before starting a new group)
<?php
	}
?>
	</form>
<?php
}
echo "<br><hr><br>";
?>

<?php

//if form to change password has been submitted
if(isset($_POST['updatepassword']))
{
	$pwerrors = 0;
	$pwerrors = !empty($_POST['currentpassword']) ? $pwerrors : $pwerrors + 1; //ensure current password has been entered
	$pwerrors = !empty($_POST['newpassword1']) ? $pwerrors : $pwerrors + 1; //ensure new password has been entered
	$pwerrors = !empty($_POST['newpassword2']) ? $pwerrors : $pwerrors + 1; //ensure new password is confirmed
	$pwerrors = $_POST['newpassword1'] == $_POST['newpassword2'] ? $pwerrors : $pwerrors + 1; //ensure new passwords are the same
	$cpw = htmlentities($_POST['currentpassword']);
	$newpw = htmlentities(trim($_POST['newpassword1']));
	/*$pwerrors = strlen($newpw) > 9 ? $pwerrors : $pwerrors + 1; //ensure new password is at least 10 characters
	$pwerrors = preg_match('/[A-Z]/', $newpw) ?  $pwerrors : $pwerrors + 1; //ensure new pw has capital letter`
	$pwerrors = preg_match('/[a-z]/', $newpw) ?  $pwerrors : $pwerrors + 1; //ensure new pw has lowercaseletter
	$pwerrors = preg_match('/[0-9]/', $newpw) ?  $pwerrors : $pwerrors + 1; //ensure new pw has number
	$pwerrors = preg_match('/[\W_]/', $newpw) ?  $pwerrors : $pwerrors + 1; //ensure new pw has special character*/
	
	if($pwerrors == 0) //no errors found
	{
		require('../connecttopba.php');
		$q = 'SELECT * FROM pbausers WHERE userid = '.$_SESSION['userid']; //get current users data
		$q .= ' AND password = SHA2(?,256)';
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "s", $cpw);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);

		$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
		if(mysqli_num_rows($r) == 1) //if current password is correct 1 row will be returned
		{
			// check if password has been Reset if so, restore previous access level
			if($row['pwreset'] > 0)
			{
				$reset = $row['pwreset'];
				$user = $_SESSION['userid'];
				$q = "UPDATE pbausers SET accesslevel = $reset, pwreset = 0, password = SHA2(?, 256) WHERE userid = ?";
				
				$_SESSION['accesslevel'] = $reset; //update current access level
			}
			else
			{			
				$user = $_SESSION['userid'];
				$q = "UPDATE pbausers SET userpassword= SHA2(?, 256) WHERE userid = ?"; //update password
			}
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "si", $newpw, $user);
			mysqli_stmt_execute($stmt);
			echo '<p style="color:red">Your password has been updated</p>'; //confirmation message
		}
	}
	else
	{
		echo '<p style="color:red">Please ensure that all update password fields are correct</p>'; //if any fields are empty or new passwords don't match
	}
}
?>
Change Password<br><br>
<form name="changepassword" action="pbaadmin.php" method="POST">
	<label for="currentpassword">Password: </label>
	<input type="password" name="currentpassword"><br><br>
	<label for="newpassword1">New Password: </label>
	<input type="password" name="newpassword1">
	<label for="newpassword2">&nbsp Confirm Password: </label>
	<input type="password" name="newpassword2"><br><br>
	<div style="font-size:0.7em; color:red">Password needs to contain uppercase and lowercase letter, number and special character and be at least 10 characters.</div> 	
	<input type="submit" name="updatepassword" value="Change Password">
</form>
<br><hr><br>
	
<?php
if($_SESSION['accesslevel'] > 3) //check user has permissions for this page
{
		//if form to change user access level submitted update user record
	if(isset($_POST['accesschange']) && isset($_POST['access']) && isset($_POST['userid'])){
		$nal = $_POST['access'];
		$user = $_POST['userid'];
		$q = "UPDATE pbausers SET accesslevel = ? WHERE userid = ?";
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "ii", $nal, $user);
		mysqli_stmt_execute($stmt);
		
		echo '<p style="color:red">User access updated</p>'; // show confirmation message
	}
	?>
Change User Access<br><br>
<form name="changeaccess" action="pbaadmin.php" method="POST"> 
	<select name="userid">

<?php
	require('../connecttopba.php');
	$q = "SELECT * FROM pbausers ORDER BY lastname, firstname"; // get all users
	$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
	echo '<option value="0">Select user...</option>';
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		// for each user, add a row to the combo box
		$pid = $row['userid'];
		$fullname = $row['lastname'] . ', ' . $row['firstname'].' - '.$row['email'];		
		ECHO '<option value = ' . $pid . '>' . $fullname . '</option>'; //combo value is user id and display is users name and email
	}
?>
	</select>
	
<br><br>
<input type="radio" id="na" name="access" value="0">
<label for="na">No Access</label><br>
<input type="radio" id="ro" name="access" value="1">
<label for="ro">Basic</label><br>
<input type="radio" id="ro" name="access" value="2">
<label for="ro">Read Only</label><br>
<input type="radio" id="rw" name="access" value="3">
<label for="rw">Read/Write</label><br>
<input type="radio" id="god" name="access" value="4">
<label for="god">God</label><br>
<br><input type="submit" name="accesschange" value="Change Access">
</form>
<br><hr><br>
<?php
		//if form to reset password submitted update user record
if(isset($_POST['pwreset']) && isset($_POST['resetpw']) && isset($_POST['pwuserid']))
{
	$npw = htmlentities($_POST['resetpw']);
	$user = htmlentities($_POST['pwuserid']);
	//find users current access level
	$q = "SELECT accesslevel FROM pbausers WHERE userid = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $user);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	
	$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$accesslevel = $row['accesslevel'];
	// update temp password and set pwreset to old access level and set access to nil takes away access while password is in doubt
	$q = "UPDATE pbausers SET accesslevel = 0, pwreset = ? , password = SHA2(?, 256) WHERE userid = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "isi", $accesslevel, $npw, $user);
	mysqli_stmt_execute($stmt);

	echo '<p style="color:red">User password reset</p>'; // show confirmation message
}
?>

Reset User Password<br><br>
<form Name="resetpassword" action="pbaadmin.php" method="POST">
	<select name="pwuserid">

<?php
	require('../connecttopba.php');
	$q = "SELECT * FROM pbausers ORDER BY lastname, firstname"; // get all users
	$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
	echo '<option value="0">Select user...</option>';
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		// for each user, add a row to the combo box
		$pid = $row['userid'];
		$fullname = $row['lastname'] . ', ' . $row['firstname'].' - '.$row['email'];		
		ECHO '<option value = ' . $pid . '>' . $fullname . '</option>'; //combo value is user id and display is users name and email
	}
?>
	</select><br><br>
	<label for="resetpw">New Password</label>
	<input type="text" name="resetpw">
	<br><br>
	<input type="submit" name="pwreset" value="Reset Password">
</form>
<?php
}
?>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>