<?php
//Rev 1 19/11/2025
//this page is available to all users
//it allows the user to change their Password

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
<?php
include("pbaincludes/pbaadminmenu.php");
?>
<hr>

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
	$pwerrors = strlen($newpw) > 9 ? $pwerrors : $pwerrors + 1; //ensure new password is at least 10 characters
	$pwerrors = preg_match('/[A-Z]/', $newpw) ?  $pwerrors : $pwerrors + 1; //ensure new pw has capital letter`
	$pwerrors = preg_match('/[a-z]/', $newpw) ?  $pwerrors : $pwerrors + 1; //ensure new pw has lowercaseletter
	$pwerrors = preg_match('/[0-9]/', $newpw) ?  $pwerrors : $pwerrors + 1; //ensure new pw has number
	$pwerrors = preg_match('/[\W_]/', $newpw) ?  $pwerrors : $pwerrors + 1; //ensure new pw has special character
	
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
				$q = "UPDATE pbausers SET password= SHA2(?, 256) WHERE userid = ?"; //update password
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
<form name="changepassword" action="pbachangepassword.php" method="POST">
	<label for="currentpassword">Current Password: </label>
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
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>