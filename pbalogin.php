<?php
//Rev 1 19/11/2025
//this is a simple login screen
$page_title = "Login";
include('pbaincludes/pbaheader.html');

if(isset($errors) && !empty($errors)) #if any errors, list on screen
{
	echo '<p id="err_msg">Oops! There was a problem:<br>';
	foreach($errors as $msg)
	{
		echo " - $msg<br>";
	}
	echo 'Please try again or <a href="pbaregister.php">Register</a>';
}
?>

<h1>Login</h1>
<p>or register <a href="pbaregister.php">here.</a></p>
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
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>