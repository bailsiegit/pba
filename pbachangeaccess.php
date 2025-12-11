<?php
//Rev 1 19/11/2025
//this page is for changing the access level of users

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
<form name="changeaccess" action="pbachangeaccess.php" method="POST"> 
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

<?php
}
else
{
	echo "You do not have permission to access this page.";
	echo "<br><br>";
}
?>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>