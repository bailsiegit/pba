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
<?php
include("pbaincludes/pbaadminmenu.php");
?>
<hr>

<br>
Please make a selection from the above menu.
<br>
<br>
<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>