<?php
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
	
	if(empty($_POST['selectedcopy']))
	{
		echo '<span style="color:red">Please make a selection or go </span><a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbaactivitymemberships.php?yid='.$formyear.'&cid='.$formmembership.'">Back</a>';
	}
	else
	{
		require('../connecttopba.php');
		//clean data input
		$selectedcopies = $_POST['selectedcopy'];
		$selectedmembership = htmlentities($_POST['selectedmembership']);
		$selectedyear = htmlentities($_POST['selectedyear']);
		$strtoday = date("Y-m-d");
		//work through selectedcopies array creating new records
		foreach($selectedcopies as $value)
		{
			//get existing record to learn MemberID
			$cleanvalue = htmlentities($value);
			$q = "SELECT * FROM memberships WHERE MshipId = ?";
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "i", $cleanvalue);
			mysqli_stmt_execute($stmt);
			$r = mysqli_stmt_get_result($stmt);
			$record = mysqli_fetch_assoc($r);
			//create new record
			$q = "INSERT IGNORE INTO memberships (MembId, start, Mtype, Year) VALUES (?, ?, ?, ?)";
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "isii", $record['MembId'], $strtoday, $selectedmembership, $selectedyear);
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

<?php
// load membership details
$membershipQuery = "SELECT mbid, mn, st, msid, end, rc, members.FirstName, members.LastName FROM
	(SELECT Mtype, MembId, start, end, receipt, MshipId FROM ((memberships 
	INNER JOIN years ON memberships.Year = years.YearId)
	INNER JOIN membertypes ON memberships.Mtype = membertypes.MemBTypeId) 
	WHERE memberships.Year = ? AND memberships.Mtype = ?) 
	membershipdata (mn, mbid, st, end, rc, msid) 
	INNER JOIN members ON membershipdata.mbid = members.MemberID";
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
	echo '<select size="10" name="selectedcopy[]" id="selectedcopy" multiple>';
	while ($membership = mysqli_fetch_assoc($membershipResult)) 
	{
		echo '<option value='.$membership['msid'].'>'.$membership['FirstName'].' '.$membership['LastName'].'</option>';
	}
	echo '</select>';
}
?>
<br><br>

</td>
<td style="border:0px; background-color:white;">Use ctrl or cmd to select mutliple people to copy to the target membership.
<br><br><br>
<input type="submit" value="Copy" name="copymembership">
</form>
</td></tr></table>
<?php
include('pbaincludes/pbafooter.html');
?>

</body></html>