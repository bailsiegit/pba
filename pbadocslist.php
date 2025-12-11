<?php
//Rev 1 19/11/2025
//This page is reached from a button on various pages in the activity area
//the page displays a list of files associated with the selected activity
//it also has an upload area to add files to the activity
//back buttons take the user back to the selected activity
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
if($_SESSION['accesslevel'] < 1)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<?php
// set up cross reference fields
$activity = array("cm" => "committees", "aw" => "awards", "tm" => "teams", "em" => "employees", "vl" => "volunteers", "mb" => "members");
$fields = array("cm" => "CommitteeName", "aw" => "AwardName", "tm" => "TeamName", "em" => "employees", "vl" => "volunteers", "mb" => "members");
$actref = array("cm" => "CommitteeId", "aw" => "AwardId", "tm" => "TeamId", "em" => "EmployeeId", "vl" => "VolunteerId", "mb" => "MemberId");


if(isset($_GET['actid']))
{
	$getactivity = strtolower(trim($_GET['actid']));
	$getyear = strtolower(trim($_GET['yid']));
	$getactref = strtolower(trim($_GET['refid']));
	
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
	$qact = "SELECT $fields[$getactivity] FROM $activity[$getactivity] WHERE $actref[$getactivity] = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $qyr);
	mysqli_stmt_bind_param($stmt, "i", $getyear);
	mysqli_stmt_execute($stmt);
	$ryr = mysqli_stmt_get_result($stmt);
	$yrrow = mysqli_fetch_array($ryr, MYSQLI_ASSOC);
	$stmt = mysqli_prepare($link, $qact);
	mysqli_stmt_bind_param($stmt, "i", $getactref);
	mysqli_stmt_execute($stmt);
	$ract = mysqli_stmt_get_result($stmt);
	$actrow = mysqli_fetch_array($ract, MYSQLI_ASSOC);
	$yrtxt = $yrrow['YearText'];
	$actname = $actrow[$fields[$getactivity]];
}

// process document Upload
if(isset($_POST['uploadfile']) && !empty($_POST['docname']) && !empty($_FILES['filname']['name']))
{
	//save all the POST values into same variables as GET to refresh page after upload
	$getyear = htmlentities($_POST['yid']);
	$getactivity = htmlentities($_POST['actid']);
	$getactref = htmlentities($_POST['actrefid']);
	$postfiltitle = htmlentities($_POST['docname']);
	$qyr = "SELECT * FROM years WHERE YearId = ?";
	$qact = "SELECT $fields[$getactivity] FROM $activity[$getactivity] WHERE $actref[$getactivity] = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $qyr);
	mysqli_stmt_bind_param($stmt, "i", $getyear);
	mysqli_stmt_execute($stmt);
	$ryr = mysqli_stmt_get_result($stmt);
	$yrrow = mysqli_fetch_array($ryr, MYSQLI_ASSOC);
	$stmt = mysqli_prepare($link, $qact);
	mysqli_stmt_bind_param($stmt, "i", $getactref);
	mysqli_stmt_execute($stmt);
	$ract = mysqli_stmt_get_result($stmt);
	$actrow = mysqli_fetch_array($ract, MYSQLI_ASSOC);
	$yrtxt = $yrrow['YearText'];
	$actname = $actrow[$fields[$getactivity]];
	// work out document number
	$q = "SELECT MAX(DocIndex) as maxindex FROM documents WHERE Activity = ?";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "s", $getactivity);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	$maxrow = mysqli_fetch_array($r, MYSQLI_ASSOC);
	$maxfileindex = (is_null($maxrow['maxindex'])) ? 0 : $maxrow['maxindex'];
	$newfileindex = str_pad((string) $maxfileindex + 1, 6, "0", STR_PAD_LEFT);
	$tmpfullpath = $_FILES['filname']['tmp_name'];
	$fileextension = pathinfo($_FILES['filname']['name'], PATHINFO_EXTENSION);
	$newfilename = $getactivity.$newfileindex.'.'.$fileextension;
	//$newfullpath = "C:\wamp64\www\\documents\\$getactivity\\$newfilename";
	$root = dirname($_SERVER['DOCUMENT_ROOT']); // one level above public_html
	$uploadDir = $root . "/documents/$getactivity/";
	$newfullpath = $uploadDir . $newfilename;

	date_default_timezone_set("Australia/Perth");
	$lastsaved = date("Y-m-d H:i", time());
	rename($tmpfullpath, $newfullpath);
	$q = "INSERT INTO documents (DocName, DocIndex, FileName, FileType, Activity, ActivityRef, LastSaved, YearId) 
	VALUES (?, $newfileindex, '$newfilename', '$fileextension', ?, ?, '$lastsaved', ?)";
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "ssii", $postfiltitle, $getactivity, $getactref, $getyear);
	mysqli_stmt_execute($stmt);
}
elseif(isset($_POST['uploadfile'])) //if doc name and/or upload file not entered, refresh page
{
	// reset GET values from POST data to refresh page after POST failure
	$getyear = htmlentities($_POST['yid']);
	$getactivity = htmlentities($_POST['actid']);
	$getactref = htmlentities($_POST['actrefid']);
	// get text for year and activity
	$qyr = "SELECT * FROM years WHERE YearId = ?";
	// redo doc query
	$qact = "SELECT $fields[$getactivity] FROM $activity[$getactivity] WHERE $actref[$getactivity] = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $qyr);
	mysqli_stmt_bind_param($stmt, "i", $getyear);
	mysqli_stmt_execute($stmt);
	$ryr = mysqli_stmt_get_result($stmt);
	$yrrow = mysqli_fetch_array($ryr, MYSQLI_ASSOC);
	$stmt = mysqli_prepare($link, $qact);
	mysqli_stmt_bind_param($stmt, "i", $getactref);
	mysqli_stmt_execute($stmt);
	$ract = mysqli_stmt_get_result($stmt);
	$actrow = mysqli_fetch_array($ract, MYSQLI_ASSOC);
	$yrtxt = $yrrow['YearText'];
	$actname = $actrow[$fields[$getactivity]];
}
// page header
echo '<h2>Document List</h2>';

// array of return urls for back button add here for each new activity
$returnto = array("cm" => "pbaactivitycommittees.php?yid=$getyear&cid=$getactref", "tm" => "pbaactivityteams.php?yid=$getyear&tid=$getactref");
echo '<a class="buttonlink" href="'.$returnto[$getactivity].'">Back</a>';
?>

<div>
<hr>
<?php
//find related documents
require('../connecttopba.php');
$q = "SELECT * FROM documents WHERE YearId = ? AND ActivityRef = ? AND Activity = ?";
$stmt = mysqli_prepare($link, $q);
mysqli_stmt_bind_param($stmt, "iis", $getyear, $getactref, $getactivity);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);

// show year and activity name
echo '<h2 style="text-align:center; margin:5px; width:100%;">Documents - '.$yrtxt.' - '.$actname.'</h2>';
// display document List
?>
	<table style="width:100%;"><tr><td style="background:white; border:0px; width:50%">
		<table style="width="100%;">
<?php
		if(mysqli_num_rows($r) > 0)
		{
			echo '<tr><th>Document</th><th>Uploaded</th><th>Type</th><th>Download</th></tr>';
			while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
			{
				echo '<tr><td>'.$row['DocName'].'</td><td>'.date("d/m/Y H:i",strtotime($row['LastSaved'])).'</td><td>'.$row['FileType'].'</td>
				<td><a class="buttonlink" href="downloadfile.php?did='.$row['DocumentId'].'">Download</a></td></tr>';
			}
		}
		else
		{
			echo '<tr><td style="background:white; border:0px; width:50%">';
			echo 'No documents found.';
			echo '</td></tr>';
		}
?>
		</table>
	</td><td style="background:white; border:1px; width:50%">
	<h4 style="align:center;">Upload a Document</h4>
<?php
if($_SESSION['accesslevel'] > 2) //only show upload dialogue if user has permission
{
	?>
	<p style="color:red;">This is an archive only. Ensure the document is finalised before uploading.</p>
	<form action="pbadocslist.php" method="post" enctype="multipart/form-data">
	Document Title: <input type="text" size="40" name="docname" placeholder="Plain english title for document"><br>
	Select File: <input style="margin:5px 0px 5px 0px;" type="file" name="filname"><br>
	<input type="hidden" name="yid" value="<?php if(isset($getyear)){echo $getyear;} ?>">
	<input type="hidden" name="actrefid" value="<?php if(isset($getactref)){echo $getactref;} ?>">
	<input type="hidden" name="actid" value="<?php if(isset($getactivity)){echo $getactivity;} ?>">
	<input class="buttonlink" type="submit" name="uploadfile" value="Upload">
	</form>
	<br><br>
<?php
}
else
{
	echo "You do not have permission to upload files.";
}
?>
	</td></tr></table>

</div>

<?php
include('pbaincludes/pbafooter.html');
?>

</body></html>