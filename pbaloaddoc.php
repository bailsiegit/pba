<?php
//Rev 1 19/1/2025
//this page is in the documents group 
//it provides an upload facility for general Documents
// general documents are those not associated with a specific activity
session_start();
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	load(); //redirect to login page
}
$page_title = 'Documents';
include('pbaincludes/pbaheader.html');

$_SESSION['timeoutstart'] = time();

?>

<?php
if($_SESSION['accesslevel'] < 2)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>
<?php
//echo '<pre>';
//print_r($_POST);
//echo 'GET';
//print_r($_GET);
//echo 'FILES';
//print_r($_FILES);
//echo '</pre>';
?>

<?php
// set up cross reference fields
$activity = array("cm" => "committees", "aw" => "awards", "tm" => "teams", "em" => "employees", "vl" => "volunteers", "mb" => "members");
$fields = array("cm" => "CommitteeName", "aw" => "AwardName", "tm" => "TeamName", "em" => "employees", "vl" => "volunteers", "mb" => "members");
$actref = array("cm" => "CommitteeId", "aw" => "AwardId", "tm" => "TeamId", "em" => "EmployeeId", "vl" => "VolunteerId", "mb" => "MemberId");


if(isset($_GET['yid']))
{
	$getyear = strtolower(trim($_GET['yid']));
	
	// confirm the GET reference exists in the lookup arrays
	if (!array_key_exists($getactivity, $activity)) {
    die("Invalid activity selection");
	}
	if (!array_key_exists($getactivity, $fields)) {
    die("Invalid activity field selection");
	}	
	if (!array_key_exists($getactivity, $actref)) {
    die("Invalid activity reference selection");
	}	
	
	$qyr = "SELECT * FROM years WHERE YearId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $qyr);
	mysqli_stmt_bind_param($stmt, "i", $getyear);
	mysqli_stmt_execute($stmt);
	$ryr = mysqli_stmt_get_result($stmt);
	$yrrow = mysqli_fetch_array($ryr, MYSQLI_ASSOC);
}

// process document Upload
if(isset($_POST['uploadfile']) && !empty($_POST['docname']) && !empty($_FILES['filname']['name']))
{
	//save all the POST values into same variables as GET to refresh page after upload
	$getyear = htmlentities($_POST['selectedyear']);
	$postfiltitle = htmlentities($_POST['docname']);
	$qyr = "SELECT * FROM years WHERE YearId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $qyr);
	mysqli_stmt_bind_param($stmt, "i", $getyear);
	mysqli_stmt_execute($stmt);
	$ryr = mysqli_stmt_get_result($stmt);
	$yrrow = mysqli_fetch_array($ryr, MYSQLI_ASSOC);

	// work out document number
	//$q = "SELECT MAX(DocIndex) as maxindex FROM documents WHERE Activity = 'gn'";
	$q = "SELECT MAX(DocIndex) as maxindex FROM documents WHERE Activity = 'gn'";
	$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
	$maxrow = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$maxfileindex = (is_null($maxrow['maxindex'])) ? 0 : $maxrow['maxindex'];
	$newfileindex = str_pad((string) $maxfileindex + 1, 6, "0", STR_PAD_LEFT);
	$tmpfullpath = $_FILES['filname']['tmp_name'];
	$fileextension = pathinfo($_FILES['filname']['name'], PATHINFO_EXTENSION);
	$newfilename = 'gn'.$newfileindex.'.'.$fileextension;
	$newfullpath = "C:\wamp64\www\\documents\\gn\\$newfilename";
	date_default_timezone_set("Australia/Perth");
	$lastsaved = date("Y-m-d H:i", time());
	rename($tmpfullpath, $newfullpath);
	$q = "INSERT INTO documents (DocName, DocIndex, FileName, FileType, Activity, ActivityRef, LastSaved, YearId) 
	VALUES (?, $newfileindex, '$newfilename', '$fileextension', 'gn', 0, '$lastsaved', ?)";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "si", $postfiltitle, $getyear);
	mysqli_stmt_execute($stmt);
	echo '<p style="color:red">Upload successful</p>';
}
elseif(isset($_POST['uploadfile'])) //if doc name and/or upload file not entered, refresh page
{
	// reset GET values from POST data to refresh page after POST failure
	$getyear = htmlentities($_POST['selectedyear']);
	// get text for year and activity
	//$qyr = "SELECT * FROM years WHERE YearId = $getyear";
	$qyr = "SELECT * FROM years WHERE YearId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $qyr);
	mysqli_stmt_bind_param($stmt, "i", $getyear);
	mysqli_stmt_execute($stmt);
	$ryr = mysqli_stmt_get_result($stmt);
	//$ryr = mysqli_query($link, $qyr, MYSQLI_STORE_RESULT);
	$yrrow = mysqli_fetch_array($ryr, MYSQLI_ASSOC);
	echo '<h3 style="color:red">Nothing uploaded. Please add title and file</h3>';
}
// page header
echo '<h2 style="margin-bottom:0px">Upload a General Document</h2>';
echo '<span style="font-size:0.8em">Use this page ONLY when the document is not specific to a team or committee.</span><br><br>';

?>
<?php
include('pbaincludes/pbadocsmenu.php');
?>

<div>
<hr>


<table style="width:100%;"><tr><td style="background:white; border:1px; width:50%">
<h4 style="align:center;">Upload a Document</h4><p style="color:red;">This is an archive only. Ensure the document is finalised before uploading.</p>
<form action="pbaloaddoc.php" method="post" enctype="multipart/form-data">
Document Title: <input type="text" size="40" name="docname" placeholder="Plain english title for document"><br>
<p>Year of Document: 
<!-- //create year select combo box -->
<select name="selectedyear" id="selectedyear">	
<?php
// get all year for combo box
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
	if(!isset($formyear) && date("Y") == $pbayear  ) //if no year specified, set to current year when found
	{
		$formyear = $yearid; //this is set to get the correct teams for the combo - this years teams
		echo '<option value = ' . $yearid . ' selected="selected">' . $pbayear . '</option>';
	}
	elseif(isset($formyear) && $formyear == $yearid)
	{
		echo '<option value = ' . $yearid . ' selected="selected">' . $pbayear . '</option>';
	}
	else
	{
		echo '<option value = ' . $yearid . '>' . $pbayear . '</option>';
	}
}
mysqli_close($link);
?>
</select></p>
Select File: <input style="margin:5px 0px 5px 0px;" type="file" name="filname"><br>
<input type="hidden" name="yid" value="<?php if(isset($getyear)){echo $getyear;} ?>">
<input class="buttonlink" type="submit" name="uploadfile" value="Upload">
</form>
<br><br>

</td></tr></table>

</div>

<?php
include('pbaincludes/pbafooter.html');
?>
<script>
//this is a test script file
</script>
</body></html>