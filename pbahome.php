<?php
session_start();

$page_title = 'Home';
include('pbaincludes/pbaheader.html');

?>



<?php

if(isset($_SESSION['userid']))
{
	echo '<h1 style="text-align:center;"><br>Hi, '.$_SESSION["first_name"].'
		<br><br>Welcome to PBA Database</h1>';
	echo '<p style="text-align:center;"><br><br>Please make a selection from the menu.<br><br></p>';
}
else
{
	echo '<p style="text-align:center;"><br><br>Please <a href="pbalogin.php"> login</a> to get started.<br><br></p>';
}

?>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>