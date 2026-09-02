<?php
//Rev 3 1/9/2026 - added forgotten password link
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
Password: <input type="password" name="pass"><br>
<a href="mailto:pbadata@fastmail.com.au?subject=Forgottten%20Password&body=Hi%0D%0AI%20have%20forgotten%20my%20password.%0D%0APlease%20reset%20my%20password."
 style="font-size:0.8em; color:red; decoration:none">Forgotten your password?</a>
</p>
<p>
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