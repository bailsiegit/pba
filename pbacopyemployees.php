<?php
//Rev 2 16/3/2026 - changed names list from select element to table
//this page is for copying employees from one year to the next
//the person and role is copied across to the new year
//when done the new year employees are displayed

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
echo '<pre>';
print_r($_POST);
echo '</pre>';
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
if(isset($_POST['copyemployee']))
{
	// get from employee details in case no names have been selected
	$formyear = htmlentities($_POST['fromyear']);
	
	if(!isset($_POST['checked']))
	{
		echo '<span style="color:red">Please make a selection or go </span><a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbaactivityemployee.php?yid='.$formyear.'">Back</a>';
	}
	else
	{
		require('../connecttopba.php');
		//clean data input
		$selectedcopies = $_POST['checked'];
		$selectedyear = htmlentities($_POST['selectedyear']);
		//work through selectedcopies array creating new records
		foreach($selectedcopies as $value)
		{
			if(!empty($value['id']))
			{
			$cleanvalue = htmlentities($value['id']); //this is the persons memberid
			$cleanrole = htmlentities(trim($value['rl']));

			//create new record
			$q = "INSERT IGNORE INTO employees (MembId, Role, Year) VALUES (?, ?, ?)";
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "isi", $cleanvalue, $cleanrole, $selectedyear);
			mysqli_stmt_execute($stmt);
			$r = mysqli_stmt_get_result($stmt);
			}
		}
		// once new records completed show new employee
		//header('Location: pbaactivityemployee.php?yid='.$selectedyear.'');
		//exit();
	}
}
//save GET values if sent
if(isset($_GET['yid']))
{
	$formyear = htmlentities($_GET['yid']);
	// show back button to the from employee page
	echo '<a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbaactivityemployee.php?yid='.$formyear.'">Back</a>';
}
//table to layout top area
echo '<table style="width:100%;"><tr>';
echo '<td style="width:50%; border:0px; background-color:white;">';
// page header
//get from-employee year to show on page
$qyear = "SELECT YearText FROM years WHERE YearId = ?";
require('../connecttopba.php');
$stmt = mysqli_prepare($link, $qyear);
mysqli_stmt_bind_param($stmt, "i", $formyear);
mysqli_stmt_execute($stmt);
$ryear = mysqli_stmt_get_result($stmt);
$yeartext = mysqli_fetch_assoc($ryear);

echo '<h3>'.$yeartext['YearText'].' - Employees</h3>';
echo '<h2>Copy Employee List to:</h2>';
?>
<!--search criteria form-->
<form action="pbacopyemployees.php" method = "POST">
<!--hide from employee info to retain it after in $_POST-->
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
		// set current to selected as this is the most likely requirement
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
<div style="height:80px; overflow-y:auto;">
<table><tr><th>Select</th><th>Name</th><th>Role</th> <!--table to layout names and roles-->

<?php
// load employee details
$employeeQuery = "
SELECT
	mb.FirstName,
	mb.LastName,
	em.MembId,
	em.Role
FROM employees em
INNER JOIN members mb
ON em.MembId = mb.MemberID
WHERE
	em.Year = ?
ORDER BY mb.LastName, mb.FirstName
";
require('../connecttopba.php');
$stmt = mysqli_prepare($link, $employeeQuery);
mysqli_stmt_bind_param($stmt, "i", $formyear);
mysqli_stmt_execute($stmt);
$employeeResult = mysqli_stmt_get_result($stmt);

if (!$employeeResult) 
{
	die('employee query failed: ' . mysqli_error($link));
}
$id = 'id';
$rl = 'rl';
if (mysqli_num_rows($employeeResult) > 0) 
{	
	$i = 0; //array row number
	while ($employee = mysqli_fetch_assoc($employeeResult)) 
	{
		echo '<tr><td style="border:0px; background-color:white;"><input type="checkbox" name="checked['.$i.']['.$id.']" value="'.$employee['MembId'].'"></td>';
		echo '<td style="border:0px; background-color:white;">'.$employee['FirstName'].' '.$employee['LastName'].'</td>';
		echo '<td style="border:0px; background-color:white;"><input type="text" name="checked['.$i.']['.$rl.']" value="'.$employee['Role'].'"></td></tr>';
		$i++;
	}
	
}
?>
<br><br>
</table>
</div>
</td>
<td style="border:0px; background-color:white;">
<br><br><br>
<input type="submit" value="Copy" name="copyemployee">
</form>
</td></tr></table>
<?php
include('pbaincludes/pbafooter.html');
?>

</body></html>