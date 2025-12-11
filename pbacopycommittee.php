<?php
//Rev 1 19/11/2025
//this page is for copying a committee from one year to the next
//the person and role is copied across to the new committee
//when done the new committee is displayed

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
if(isset($_POST['copycommittee']))
{
	// get from committee details in case no names have been selected
	$formcommittee = htmlentities($_POST['fromcommittee']);
	$formyear = htmlentities($_POST['fromyear']);
	
	if(empty($_POST['selectedcopy']))
	{
		echo '<span style="color:red">Please make a selection or go </span><a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbaactivitycommittees.php?yid='.$formyear.'&cid='.$formcommittee.'">Back</a>';
	}
	else
	{
		require('../connecttopba.php');
		//clean data input
		$selectedcopies = $_POST['selectedcopy'];
		$selectedcommittee = htmlentities($_POST['selectedcommittee']);
		$selectedyear = htmlentities($_POST['selectedyear']);
		//work through selectedcopies array creating new records
		foreach($selectedcopies as $value)
		{
			//get existing record to learn MemberID and Role
			$cleanvalue = htmlentities($value);
			$q = "SELECT * FROM committeememb WHERE CommMembId = ?";
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "i", $cleanvalue);
			mysqli_stmt_execute($stmt);
			$r = mysqli_stmt_get_result($stmt);
			$record = mysqli_fetch_assoc($r);
			//create new record
			$q = "INSERT IGNORE INTO committeememb (MembId, Role, CommId, Year) VALUES (?, ?, ?, ?)";
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "isii", $record['MembId'], $record['Role'], $selectedcommittee, $selectedyear);
			mysqli_stmt_execute($stmt);
			$r = mysqli_stmt_get_result($stmt);
		}
		// once new records completed show new committee
		header('Location: pbaactivitycommittees.php?yid='.$selectedyear.'&cid='.$selectedcommittee.'');
		exit();
	}
}
//save GET values if sent
if(isset($_GET['yid']))
{
	$formcommittee = htmlentities($_GET['actid']);
	$formyear = htmlentities($_GET['yid']);
	// show back button to the from committee page
	echo '<a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbaactivitycommittees.php?yid='.$formyear.'&cid='.$formcommittee.'">Back</a>';
}
//table to layout top area
echo '<table style="width:100%;"><tr>';
echo '<td style="width:50%; border:0px; background-color:white;">';
// page header
//get from-committee year to show on page
$qyear = "SELECT YearText FROM years WHERE YearId = ?";
require('../connecttopba.php');
$stmt = mysqli_prepare($link, $qyear);
mysqli_stmt_bind_param($stmt, "i", $formyear);
mysqli_stmt_execute($stmt);
$ryear = mysqli_stmt_get_result($stmt);
$yeartext = mysqli_fetch_assoc($ryear);
//get from-committee name to show on page
$qcommittee = "SELECT CommitteeName FROM committees WHERE CommitteeId = ?";
$stmt = mysqli_prepare($link, $qcommittee);
mysqli_stmt_bind_param($stmt, "i", $formcommittee);
mysqli_stmt_execute($stmt);
$rcommittee = mysqli_stmt_get_result($stmt);
$committeename = mysqli_fetch_assoc($rcommittee);

echo '<h3>'.$yeartext['YearText'].' - '.$committeename['CommitteeName'].'</h3>';
echo '<h2>Copy Committee List to</h2>';
echo "<h4>Select target committee:</h4>";

?>
<!--search criteria form-->
<form action="pbacopycommittee.php" method = "POST">
<!--hide fromcommittee info to retain it after in $_POST-->
<input type="hidden" name="fromcommittee" value="<?php echo $formcommittee; ?>">
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
<!-- //create committee select combo box -->
<select name="selectedcommittee" id="selectedcommittee">

<?php
// populate committees combo box
$q = "SELECT * FROM committees ORDER BY CommitteeName";
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
$pbacommittees = mysqli_fetch_array($r, MYSQLI_ASSOC);

//build committee selection combo
while($pbacommittees = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	if($pbacommittees['CommitteeId'] == $formcommittee)
	{
		//select the from committee as that is likely or close in the list of committees
		echo '<option value = '.$pbacommittees['CommitteeId'].' selected="selected">'.$pbacommittees['CommitteeName'].'</option>';
	}
	else
	{
		echo '<option value='.$pbacommittees['CommitteeId'].'>'.$pbacommittees['CommitteeName'].'</option>';
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
// load committee details
$committeeQuery = "SELECT cmbid, rl, cn, mbid, members.FirstName, members.LastName, cmid, yrid FROM
	(SELECT committeememb.CommMembId, Role, committees.CommitteeName, MembId, committeememb.CommId, committeememb.Year FROM ((committeememb 
	INNER JOIN years ON committeememb.Year = years.YearId)
	INNER JOIN committees ON committeememb.CommId = committees.CommitteeId) WHERE committeememb.Year = ? AND committeememb.CommId = ?) 
	committeedata (cmbid, rl, cn, mbid, cmid, yrid) 
	INNER JOIN members ON committeedata.mbid = members.MemberID";
require('../connecttopba.php');
$stmt = mysqli_prepare($link, $committeeQuery);
mysqli_stmt_bind_param($stmt, "ii", $formyear, $formcommittee);
mysqli_stmt_execute($stmt);
$committeeResult = mysqli_stmt_get_result($stmt);

if (!$committeeResult) 
{
	die('Committee query failed: ' . mysqli_error($link));
}
if (mysqli_num_rows($committeeResult) > 0) 
{	
	echo '<select size="10" name="selectedcopy[]" id="selectedcopy" multiple>';
	while ($committee = mysqli_fetch_assoc($committeeResult)) 
	{
		if(empty($committee['rl']))
		{
			//if no role, show only names
			echo '<option value='.$committee['cmbid'].'>'.$committee['FirstName'].' '.$committee['LastName'].'</option>';
		}
		else
		{
			//if role exists add hyphen and role to names
			echo '<option value='.$committee['cmbid'].'>'.$committee['FirstName'].' '.$committee['LastName'].' - '.$committee['rl'].'</option>';
		}
	}
	echo '</select>';
}
?>
<br><br>

</td>
<td style="border:0px; background-color:white;">Use ctrl or cmd to select mutliple people to copy to the target committee.
<br><br><br>
<input type="submit" value="Copy" name="copycommittee">
</form>
</td></tr></table>
<?php
include('pbaincludes/pbafooter.html');
?>

</body></html>