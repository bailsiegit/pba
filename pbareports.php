<?php
//Rev 1 19/11/2025
//this page is called from the reports item on the main menu
//this page is a list of the available reports and has criteria input
//and report output is downloaded to the browser

session_start();
//check that user is logged in and has not been idle for too long
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();
	load(); //redirect to login page
}
$page_title = 'Reports';
include('pbaincludes/pbaheader.html');

$_SESSION['timeoutstart'] = time(); //reset login timeout marker

?>
<h2>Reports</h2>

<?php
if($_SESSION['accesslevel'] > 0)//check user has access to Reports
{

?>
	<h4>Create AGM Sign in Report</h4>
	<br><br>
	<form action="pbaagmreport.php" method="POST">
	Print report for year ending October 
	<!-- //create year select combo box -->
	<select name="selectedyear" id="selectedyear">	
<?php
	// get all year for combo box
	$stickyselect = 0;
	if(isset($_POST['year'])) $formyear = htmlentities($_POST['year']);
	require('../connecttopba.php');
	$q = 'SELECT * FROM years ORDER BY YearText';
	$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

	// build years combo box from above query
	while($pbayears = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		$yearid = $pbayears['YearId'];
		$pbayear = $pbayears['YearText'];
		//make combobox sticky to current year or last selected Year
		if(isset($formyear) && $formyear == $yearid)
		{
			$stickyselect = 1; //this is used to hold the sticky year rather than the current year
			echo '<option value = ' . $yearid . ' selected="selected">' . $pbayear . '</option>';
		}
		elseif((int)date("Y") - 1 == $pbayear && $stickyselect < 1)
		{
			$formyear = $yearid;
			echo '<option value = ' . $yearid . ' selected="selected">' . $pbayear . '</option>';
		}
		else
		{
			echo '<option value = ' . $yearid . '>' . $pbayear . '</option>';
		}
	}
	$stickyselect = 0; //reset stickyselect
	mysqli_close($link);
?>
	</select>
	<br>
	<input type="submit" name="agmlist" value="Download Report">

	</form>
<?php
}
echo "<br><hr><br>";
?>
	
<?php
if($_SESSION['accesslevel'] > 0) //check user has permissions for this page
{
?>
Member Activity Report<br><br>
<form name="activityreport" action="pbaactivityreport.php" method="POST"> 
	<select name="personid">

<?php
	require('../connecttopba.php');
	$q = "SELECT * FROM members ORDER BY LastName, FirstName"; // get all members
	$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
	echo '<option value="0">Select member...</option>';
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
		// for each member, add a row to the combo box
		$pid = $row['MemberID'];
		$fullname = $row['LastName'] . ', ' . $row['FirstName'];		
		echo '<option value = ' . $pid . '>' . $fullname . '</option>'; //combo value is member id and display is users name
	}
?>
	</select>
	
<br>
<input type="submit" name="dlactivityreport" value="Download Report">
</form>
<br>
<?php
}
?>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>