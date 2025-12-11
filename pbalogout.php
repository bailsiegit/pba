<?php
//Rev1 19/11/2025
//this page is called from the logout main menu item
//this is a page to end the user session and logout
//confirmation of logout is shown
session_start();

if(!isset($_SESSION['userid']))
{
	require("pbalogin_tools.php");
	load();
}

$page_title = "Goodbye";
$_SESSION = array();
include('pbaincludes/pbaheader.html');

# clear session variables

session_unset();
session_destroy();

echo '<h1>Goodbye!</h1>
	<p>You are now logged out.</p>
	<p><a href="pbalogin.php">Login</a></p>';

?>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>