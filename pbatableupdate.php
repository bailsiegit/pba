<?php
//Rev 2 14/8/2026 - added is team? and pick hierachy to match new awards structure
//this page is called from the maintenance item on the main menu
//this page provides the ability to add to the base tables and
//also make items in each base table active or Inactive

session_start();
//check that user is logged in and has not been idle for too long
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	session_unset();
	session_destroy();
	header('Location: pbalogin.php'); //redirect to login page
	exit();
}
$page_title = 'Tables';
include('pbaincludes/pbaheader.html');
include('pbaincludes/pbamaintenancemenu.php');
$_SESSION['timeoutstart'] = time(); //reset login timeout marker

?>
<h2>Table Maintenance</h2>

<?php
if($_SESSION['accesslevel'] < 4) //check user has permissions for this page
{
	//if not permitted show message and exit script
	echo '<br><br>You do not have permission to access this page.';
	include('pbaincludes/pbafooter.html');
	exit();
}
?>

Please select from the above menu to add entries to key data tables and set Active/Inactive status of existing entries.
<hr>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
const select = document.getElementById("isteamaward");

select.addEventListener("change", function () {

    const option = this.options[this.selectedIndex];
    const isTeam = option.value == "1";

	const hierachy = document.getElementById("showhierachy");
    const hierlist = document.getElementById("awardhierachy");

	hierachy.style.display = isTeam ? "block" : "none";
	//notteam.style.display = isTeam ? "none" : "block";

    if (!isTeam){
	hierlist.value = "0";} //value for Individual - TeamId
	//else
	//team.value = "0"; //reset team list
});		
</script>
</body></html>