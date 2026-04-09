<?php
//Rev 1 19/11/2025
//this page is for copying a volunteer from one year to the next
//the person and role is copied across to the new year

session_start();
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();
	load(); //redirect to login page
}
$page_title = 'Activity';
include('pbaincludes/pbaheader.html');

$_SESSION['timeoutstart'] = time();

?>

<?php
if($_SESSION['accesslevel'] < 4)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<?php
//has copy been submitted
if(isset($_POST['copyteam']))
{
	$formyear = $_POST['fromyear'];
	
	if(!isset($_POST['checked']))
	{
		echo '<span style="color:red">Please make a selection or go </span><a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbaactivityvolunteers.php?yid='.$formyear.'">Back</a>';
	}
	else
	{
		require('../connecttopba.php');
		$selectedcopies = $_POST['checked'];
		foreach($selectedcopies as $value)
		{
			$cleanvalue = htmlentities($value);
			$cleanrole = htmlentities($_POST[$cleanvalue]);
			//create new record
			$q = "INSERT IGNORE INTO volunteers (MembId, Role, Year) VALUES (?, ?, ?)";
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "isi", $cleanvalue, $cleanrole, $formyear);
			mysqli_stmt_execute($stmt);
			$r = mysqli_stmt_get_result($stmt);
		}
		header('Location: pbaactivityvolunteers.php?yid='.$formyear.'');
		exit();
	}
}
//save GET values if sent
if(isset($_GET['yid']))
{
	$formyear = htmlentities($_GET['yid']);
	echo '<a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbaactivityvolunteers.php?yid='.$formyear.'">Back</a>';
}
//table to layout top area
echo '<table style="width:100%;"><tr>';
echo '<td style="width:50%; border:0px; background-color:white;">';
// page header
//get from-team year
$qyear = "SELECT YearText FROM years WHERE YearId = ?";
require('../connecttopba.php');
$stmt = mysqli_prepare($link, $qyear);
mysqli_stmt_bind_param($stmt, "i", $formyear);
mysqli_stmt_execute($stmt);
$ryear = mysqli_stmt_get_result($stmt);
$yeartext = mysqli_fetch_assoc($ryear);

echo '<h3>'.$yeartext['YearText'].' - Volunteers</h3>';
echo '<h2>Copy Volunteers to</h2>';

?>
<!--search criteria form-->
<form action="pbacopyvolunteers.php" method = "POST">
<!--hide from volunteer info to retain it after in $POST-->
<input type="hidden" name="fromyear" value="<?php echo $formyear; ?>">
<!-- //create year select combo box -->
<select name="selectedyear" id="selectedyear">	
<?php
// get all year for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM years ORDER BY YearText';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
$pbayears = mysqli_fetch_array($r, MYSQLI_ASSOC);

// build years combo box from above query
while($pbayears = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$yearid = $pbayears['YearId'];
	$pbayear = $pbayears['YearText'];
	//make combobox sticky to current year
	if(date("Y") == $pbayear)
	{
		//$formyear = $yearid; //this is set to get the correct teams for the combo - this years teams
		echo '<option value = ' . $yearid . ' selected="selected">' . $pbayear . '</option>'; //select the current year as that is most likely
	}
	else
	{
		echo '<option value = ' . $yearid . '>' . $pbayear . '</option>';
	}
}
?>
</select>

<br>
	
</td><td style="width:50%; background-color:white; border:0px;">

	
</td></tr></table>

<hr>
<table><tr><td style="border:0px; background-color:white;"> <!--table to layout lower page-->
<table><tr><th>Select</th><th>Name</th><th>Role</th> <!--table to layout names list-->

<?php
// load volunteer details
$volunteerQuery = "
SELECT
	mb.FirstName,
	mb.LastName,
	vl.MembId,
	vl.Role
FROM volunteers cm
INNER JOIN members mb
ON vl.MembId = mb.MemberID
WHERE
	vl.Year = ?
";
require('../connecttopba.php');
$stmt = mysqli_prepare($link, $volunteerQuery);
mysqli_stmt_bind_param($stmt, "i", $formyear);
mysqli_stmt_execute($stmt);
$volunteerResult = mysqli_stmt_get_result($stmt);

if (!$volunteerResult) 
{
	die('volunteer query failed: ' . mysqli_error($link));
}
if (mysqli_num_rows($volunteerResult) > 0) 
{	
	while ($volunteer = mysqli_fetch_assoc($volunteerResult)) 
	{
		echo '<tr><td style="border:0px; background-color:white;"><input type="checkbox" name="checked[]" value="'.$volunteer['MembId'].'"></td>';
		echo '<td style="border:0px; background-color:white;">'.$volunteer['FirstName'].' '.$volunteer['LastName'].'</td>';
		echo '<td style="border:0px; background-color:white;"><input type="text" name="'.$volunteer['MembId'].'" value="'.$volunteer['Role'].'"></td></tr>';
		
	}
	
}
?>
<br><br>
</table>

</td>
<td style="border:0px; background-color:white;">
<br><br><br>
<input type="submit" value="Copy" name="copyvolunteers">
</form>
</td></tr></table>
<?php
include('pbaincludes/pbafooter.html');
?>

</body></html>