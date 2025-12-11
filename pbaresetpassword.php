<?php
//Rev 1 19/11/2025
//this page is the administrator to reset a user password
//this would be used after a request from a user is received and verified

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
if($_SESSION['accesslevel'] > 3) //check user has permissions for this page
{
		//if form to reset password submitted update user record
	if(isset($_POST['pwreset']) && isset($_POST['newpw']) && isset($_POST['pwuserid']))
	{
		$npw = htmlentities($_POST['newpw']);
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
		$q = "UPDATE pbausers SET accesslevel = 0, badlogins = 0, pwreset = ? , password = SHA2(?, 256) WHERE userid = ?";
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "isi", $accesslevel, $npw, $user);
		mysqli_stmt_execute($stmt);

		echo '<p style="color:red">User password reset</p>'; // show confirmation message
	}
?>

Reset User Password<br><br>
<form Name="resetpassword" action="pbaresetpassword.php" method="POST">
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
	<label for="newpw">New Password</label>
	<input type="text" name="newpw">
	<br><br>
	<input type="submit" name="pwreset" value="Reset Password">
</form>
<?php
}
else
{
	echo "You do not have permission to access this page";
	echo "<br><br>";
}
?>

<?php
include('pbaincludes/pbafooter.html');
?>

</body></html>