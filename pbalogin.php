<?php
//Rev 2 21/4/2026 - added GET checks for reduced version to show within activity pages
//this is a simple login screen
$page_title = "Login";
if(!isset($_GET['disp']))
{
	include('pbaincludes/pbaheader.html');
}
if(isset($errors) && !empty($errors)) #if any errors, list on screen
{
	echo '<p id="err_msg">Oops! There was a problem:<br>';
	foreach($errors as $msg)
	{
		echo " - $msg<br>";
	}
	echo 'Please try again or <a href="pbaregister.php">Register</a>';
}

if(!isset($_GET['disp']))
{
	echo '<h1>Login</h1>';
}
else
{
	echo '<h4>Session expired. Please login.</h4>';
}

if(!isset($_GET['disp']))
{
	echo '<p>or register <a href="pbaregister.php">here.</a></p>';
}
?>
<form action="pbalogin_action.php" method="POST">
<p>
Email Address: <input type="text" name = "email">
</p><p>
Password: <input type="password" name="pass">
</p><p>
<input type="submit" value="Login">
</p>
</form>

<?php
if(!isset($_GET['disp']))
{
	include('pbaincludes/pbafooter.html');
}
?>

<script>
//this is a test script file
</script>
</body></html>