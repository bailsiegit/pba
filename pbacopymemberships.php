<?php
// Rev 2 17/3/2026 - change names list from select element to table
//this page is for copying a membership group from one year to the next
//the person is copied across to the new membership
//when done the new membership group is displayed

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
if(isset($_POST['copymembership']))
{
	// get from membership details in case no names have been selected
	$formmembership = htmlentities($_POST['frommembership']);
	$formyear = htmlentities($_POST['fromyear']);
	
	if(!isset($_POST['checked']))
	{
		echo '<span style="color:red">Please make a selection or go </span><a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbaactivitymemberships.php?yid='.$formyear.'&mid='.$formmembership.'">Back</a>';
	}
	else
	{
		require('../connecttopba.php');
		//clean data input
		$selectedcopies = $_POST['checked'];
		$selectedmembership = htmlentities($_POST['selectedmembership']);
		$selectedyear = htmlentities($_POST['selectedyear']);
		//work through selectedcopies array creating new records
		foreach($selectedcopies as $value)
		{
			//get existing record to learn MemberID
			$cleanvalue = htmlentities($value); //members id
			$cleanstartdate = isset($_POST[$cleanvalue]) ? htmlentities($_POST[$cleanvalue]) : "0000-00-00";
			$receiptindex = 'rc'.$cleanvalue;
			$cleanreceipt = isset($_POST[$receiptindex]) ? $_POST[$receiptindex] : '';

			//create new record
			$q = "INSERT IGNORE INTO memberships (MembId, start, Mtype, Year, receipt) VALUES (?, ?, ?, ?,?)";
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "isiis", $cleanvalue, $cleanstartdate, $selectedmembership, $selectedyear, $cleanreceipt);
			mysqli_stmt_execute($stmt);
			$r = mysqli_stmt_get_result($stmt);
		}
		// once new records completed show new membership group
		header('Location: pbaactivitymemberships.php?yid='.$selectedyear.'&mid='.$selectedmembership.'');
		exit();
	}
}
//save GET values if sent
if(isset($_GET['yid']))
{
	$formmembership = htmlentities($_GET['actid']);
	$formyear = htmlentities($_GET['yid']);
	// show back button to the from membership page
	echo '<a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbaactivitymemberships.php?yid='.$formyear.'&mid='.$formmembership.'">Back</a>';
}
//table to layout top area
echo '<table style="width:100%;"><tr>';
echo '<td style="width:50%; border:0px; background-color:white;">';
// page header
//get from-membership year to show on page
$qyear = "SELECT YearText FROM years WHERE YearId = ?";
require('../connecttopba.php');
$stmt = mysqli_prepare($link, $qyear);
mysqli_stmt_bind_param($stmt, "i", $formyear);
mysqli_stmt_execute($stmt);
$ryear = mysqli_stmt_get_result($stmt);
$yeartext = mysqli_fetch_assoc($ryear);
//get from-membership name to show on page
$qmembership = "SELECT Type FROM membertypes WHERE MemBTypeId = ?";
$stmt = mysqli_prepare($link, $qmembership);
mysqli_stmt_bind_param($stmt, "i", $formmembership);
mysqli_stmt_execute($stmt);
$rmembership = mysqli_stmt_get_result($stmt);
$membershipname = mysqli_fetch_assoc($rmembership);

echo '<h3>'.$yeartext['YearText'].' - '.$membershipname['Type'].'</h3>';
echo '<h2>Copy membership List to</h2>';
echo "<h4>Select target membership:</h4>";

?>
<!--search criteria form-->
<form action="pbacopymemberships.php" method = "POST">
<!--hide frommembership info to retain it after in $_POST-->
<input type="hidden" name="frommembership" value="<?php echo $formmembership; ?>">
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
<!-- //create membership select combo box -->
<select name="selectedmembership" id="selectedmembership">

<?php
// populate memberships combo box
$q = "SELECT * FROM membertypes ORDER BY Type";
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
$pbamemberships = mysqli_fetch_array($r, MYSQLI_ASSOC);

//build membership selection combo
while($pbamemberships = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	if($pbamemberships['MemBTypeId'] == $formmembership)
	{
		//select the from membership as that is likely or close in the list of memberships
		echo '<option value = '.$pbamemberships['MemBTypeId'].' selected="selected">'.$pbamemberships['Type'].'</option>';
	}
	else
	{
		echo '<option value='.$pbamemberships['MemBTypeId'].'>'.$pbamemberships['Type'].'</option>';
	}
}

?>
</select>
<br>
	
</td><td style="width:50%; background-color:white; border:0px;">
	
</td></tr></table>

<hr>
<table><tr><td style="border:0px; background-color:white;"> <!--table to layout lower page-->
<table><tr><th>Select</th><th>Name</th><th>Start</th> <!--table to layout names list-->

<?php
// load membership details

$membershipQuery = "
SELECT
	mb.FirstName,
	mb.LastName,
	ms.MembId
FROM
	memberships ms
INNER JOIN members mb
ON ms.MembId = mb.MemberID
WHERE ms.Year = ?
AND ms.Mtype = ?
ORDER BY mb.LastName, mb.FirstName
";
require('../connecttopba.php');
$stmt = mysqli_prepare($link, $membershipQuery);
mysqli_stmt_bind_param($stmt, "ii", $formyear, $formmembership);
mysqli_stmt_execute($stmt);
$membershipResult = mysqli_stmt_get_result($stmt);

if (!$membershipResult) 
{
	die('Membership query failed: ' . mysqli_error($link));
}
if (mysqli_num_rows($membershipResult) > 0) 
{	
	while ($membership = mysqli_fetch_assoc($membershipResult)) 
	{
		echo '<tr><td style="border:0px; background-color:white;"><input type="checkbox" name="checked[]" value="'.$membership['MembId'].'"></td>';
		echo '<td style="border:0px; background-color:white;">'.$membership['FirstName'].' '.$membership['LastName'].'</td>';
		echo '<td style="border:0px; background-color:white;"><input type="date" name="'.$membership['MembId'].'" value=""></td>';
		echo '<td style="border:0px; background-color:white;"><input type="text" name="rc'.$membership['MembId'].'" value=""></td></tr>';
	}
	echo '</select>';
}
?>
<br><br>
</table>
</td>
<td style="border:0px; background-color:white;">
<br><br><br>
<input type="submit" value="Copy" name="copymembership">
</form>
</td></tr></table>
<?php
include('pbaincludes/pbafooter.html');
?>

</body></html>