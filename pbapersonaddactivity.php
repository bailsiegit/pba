<?php
//Rev 3 23/4/2026 Added accolade to list of activities that can be added
//this page is called from the pbaperson page to add activities for that person
//a year is selected and 1 activity can be added for each activity type

session_start();
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	session_unset();
	session_destroy();
	header('Location: pbalogin.php');
	exit();
}
$page_title = 'People';
include('pbaincludes/pbaheader.html');

$_SESSION['timeoutstart'] = time(); //restart timeout

?>

<?php
if($_SESSION['accesslevel'] < 3)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<?php

if(isset($_GET['pid']))
{
	$pid = htmlentities(trim($_GET['pid']));
	require('../connecttopba.php');
	// get required data for member if id passed via GET
	$q = 'SELECT MemberID, FirstName, LastName FROM members WHERE members.MemberID = ?';
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $pid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	//$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
	$person = mysqli_fetch_array($r, MYSQLI_ASSOC);
	//save the person of data into variables so either get or post source can be used in the form
	$formmembid = $person['MemberID'];
	$formfn = $person['FirstName'];
	$formln = $person['LastName'];

}
if(isset($_POST['savechanges']))
	
{
	// clean the POST data and put into variables for building SQL statements
	$countadds = 0; //set counter to ensure at least 1 record is added
	$duplicates = 0; //set counter for ignored data
	$formmembid = htmlentities(trim($_POST['membid']));
	$formfn = htmlentities(trim($_POST['membfn']));
	$formln = htmlentities(trim($_POST['membln']));
	$formyear = htmlentities(trim($_POST['selectedyear']));
	
	// process team record
	$formteam = htmlentities(trim($_POST['selectedteam']));
	$formteamrole = htmlentities(trim($_POST['teamrole']));
	if($formteam > 0){
		$sql = "INSERT IGNORE INTO teammembers (MembId, TeamId, Role, YearId) VALUES (?, ?, ?, ?)";
		//connect to database
		require('../connecttopba.php');
		// run insert query
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "iisi", $formmembid, $formteam, $formteamrole, $formyear);
		mysqli_stmt_execute($stmt);
		if(mysqli_stmt_affected_rows($stmt) == 1)
		{
			$countadds++;
		}
		elseif(mysqli_stmt_affected_rows($stmt) == 0)
		{
			$duplicates++;
		}
		mysqli_close($link);
	}	
	// process membership record
	$formmembership = htmlentities(trim($_POST['selectedmembership']));
	$formmembershipstart = htmlentities(trim($_POST['membershipstart']));
	if($formmembership > 0){
		$sql = "INSERT IGNORE INTO memberships (MembId, Mtype, start, YearId) VALUES (?, ?, ?, ?)";
		//connect to database
		require('../connecttopba.php');
		// run insert query
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "iisi", $formmembid, $formmembership, $formmembershipstart, $formyear);
		mysqli_stmt_execute($stmt);
		if(mysqli_stmt_affected_rows($stmt) == 1)
		{
			$countadds++;
		}
		elseif(mysqli_stmt_affected_rows($stmt) == 0)
		{
			$duplicates++;
		}
		mysqli_close($link);
	}
	//process committee record
	$formcommittee = htmlentities(trim($_POST['selectedcommittee']));
	$formcommitteerole = htmlentities(trim($_POST['committeerole']));
	if($formcommittee > 0){
		$sql = "INSERT IGNORE INTO committeememb (MembId, CommId, Role, YearId) VALUES (?, ?, ?, ?)";
		//connect to database
		require('../connecttopba.php');
		// run insert query
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "iisi", $formmembid, $formcommittee, $formcommitteerole, $formyear);
		mysqli_stmt_execute($stmt);
		if(mysqli_stmt_affected_rows($stmt) == 1)
		{
			$countadds++;
		}
		elseif(mysqli_stmt_affected_rows($stmt) == 0)
		{
			$duplicates++;
		}
		mysqli_close($link);
	}
	//process awards records
	$formaward = htmlentities(trim($_POST['selectedaward']));
	$formawardcomment = htmlentities(trim($_POST['awardcomment']));
	if($formaward > 0){
		$sql = "INSERT IGNORE INTO awardwinners (MembId, AwardId, Comments, YearId) VALUES (?, ?, ?, ?)";
		//connect to database
		require('../connecttopba.php');
		// run insert query
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "iisi", $formmembid, $formaward, $formawardcomment, $formyear);
		mysqli_stmt_execute($stmt);
		if(mysqli_stmt_affected_rows($stmt) == 1)
		{
			$countadds++;
		}
		elseif(mysqli_stmt_affected_rows($stmt) == 0)
		{
			$duplicates++;
		}
		mysqli_close($link);
	}
	
	//process accolade record
	if(isset($_POST['selectedaccolade']) && $_POST['selectedaccolade'] != "custom" && $_POST['selectedaccolade'] != "no selection"){
		$formaccolade = htmlentities(trim($_POST['selectedaccolade']));
	}
	elseif(isset($_POST['customacc'])){
		$formaccolade = htmlentities(trim($_POST['customacc']));
	}
	if(!empty($formaccolade)){
		$formacccomment = isset($_POST['accoladecomment']) ? htmlentities($_POST['accoladecomment']) : "";
		$sql = "INSERT IGNORE INTO accolades (MembId, Accolade, YearId, Details) VALUES (?, ?, ?, ?)";
		//connect to database
		require('../connecttopba.php');
		// run insert query
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "isis", $formmembid, $formaccolade, $formyear, $formacccomment);
		mysqli_stmt_execute($stmt);
		if(mysqli_stmt_affected_rows($stmt) == 1)
		{
			$countadds++;
		}
		elseif(mysqli_stmt_affected_rows($stmt) == 0)
		{
			$duplicates++;
		}
		mysqli_close($link);
	}	
	
	//process volunteer record
	if(isset($_POST['volunteerrole']) && $_POST['volunteerrole'] != "custom" && $_POST['volunteerrole'] != "no selection"){
		$formvolunteerrole = htmlentities(trim($_POST['volunteerrole']));
	}
	elseif(isset($_POST['volcustomrole'])){
		$formvolunteerrole = htmlentities(trim($_POST['volcustomrole']));
	}
	if(!empty($formvolunteerrole)){
		$sql = "INSERT IGNORE INTO volunteers (MembId, Role, YearId) VALUES (?, ?, ?)";
		//connect to database
		require('../connecttopba.php');
		// run insert query
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "isi", $formmembid, $formvolunteerrole, $formyear);
		mysqli_stmt_execute($stmt);
		if(mysqli_stmt_affected_rows($stmt) == 1)
		{
			$countadds++;
		}
		elseif(mysqli_stmt_affected_rows($stmt) == 0)
		{
			$duplicates++;
		}
		mysqli_close($link);
	}
	//process employee record
	if(isset($_POST['employeerole']) && $_POST['employeerole'] != "custom" && $_POST['employeerole'] != "no selection"){
		$formemployeerole = htmlentities(trim($_POST['employeerole']));
	}
	elseif(isset($_POST['customemprole'])){
		$formemployeerole = htmlentities(trim($_POST['customemprole']));
	}
	if(!empty($formemployeerole)){
		$sql = "INSERT IGNORE INTO employees (MembId, Role, YearId) VALUES (?, ?, ?)";
		//connect to database
		require('../connecttopba.php');
		// run insert query
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "isi", $formmembid, $formemployeerole, $formyear);
		mysqli_stmt_execute($stmt);
		if(mysqli_stmt_affected_rows($stmt) == 1)
		{
			$countadds++;
		}
		elseif(mysqli_stmt_affected_rows($stmt) == 0)
		{
			$duplicates++;
		}
		mysqli_close($link);
	}	
	//check countadds to see any items have been selected
	if($countadds != 1)
	{
		echo '<span style="color:red;">'.$countadds.' records added </span>';
	}
	elseif($countadds == 1)
	{
		echo '<span style="color:red;">1 record added </span>';
	}
	if($duplicates > 1)
	{
		echo '<br><span style="color:red;">'.$duplicates.' records were duplicates and ignored</span>';
	}
	elseif($duplicates == 1)
	{
		echo '<br><span style="color:red;">1 record was a duplicate and ignored</span>';
	}
}

?>

<?php
// copy the current person details into standard variables for this area for sticky form
if(isset($formmembid)){$pid = $formmembid;}
if(isset($formfn)){$person['FirstName'] = $formfn;}
if(isset($formln)){$person['LastName'] = $formln;}	

?>


<?php
//add the persons name to top of page
	echo '<h2>'.$person['FirstName'].' '.$person['LastName'].'</h2>';
//add sub menu
require('pbaincludes/pbapersonmenu.php'); 
$stickyselect = 0; //marker for managing year combo box
?>


<!-- //create form for adding records for selected person -->
<form action="pbapersonaddactivity.php" method = "POST">
<table width="50%">
<tr><th width="15%">Add Activity Records</th><th width="30%"></th></tr>
<tr><td style="text-align:right; width:25%">
	<input type="hidden" name="membid" value="<?php if(isset($formmembid))echo $formmembid;?>">
	<input type="hidden" name="membfn" value="<?php if(isset($formfn))echo $formfn;?>">
	<input type="hidden" name="membln" value="<?php if(isset($formln))echo $formln;?>">
	Year: </td>
<td>
<!-- //create year select combo box -->
<select name="selectedyear" id="selectedyear">
<?php
// get all years for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM years';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build years combo box from above query
while($pbayears = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$yearid = $pbayears['YearId'];
	$pbayear = $pbayears['YearText'];
	//make combobox sticky to current year or last selected Year
	if(isset($formyear) && $formyear == $yearid){
		$stickyselect = 1; //this is used to hold the sticky year rather than the current year
		echo '<option value = ' . $yearid . ' selected="selected">' . $pbayear . '</option>';}
	elseif(date("Y") == $pbayear && $stickyselect < 1){
		echo '<option value = ' . $yearid . ' selected="selected">' . $pbayear . '</option>';}
	else{
		echo '<option value = ' . $yearid . '>' . $pbayear . '</option>';}
}
$stickyselect = 0; //reset sticky select
?>

</select></td></tr>

<!--Team Section of Form-->
<tr style="height:3px;"><td style="background-color:black;"></td><td style="background-color:black;"></td></tr>
<tr><td style="text-align:right;">Team: </td><td>
<!-- //create team select combo box -->
<select name="selectedteam" id="selectedteam">
<option value = "0" selected="selected">Select a team...</option>
<?php
// get all teams for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM teams ORDER BY TeamName';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build teams combo box from above query
while($pbateams = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$teamid = $pbateams['TeamId'];
	$pbateam = $pbateams['TeamName'];
	//make sticky combobox
	if(isset($formteam) && $formteam == $teamid){
		echo '<option value = ' . $teamid . ' selected="selected">' . $pbateam . '</option>';}
	else{
		echo '<option value = ' . $teamid . '>' . $pbateam . '</option>';}
}

?>
</td></tr>
<tr><td style="text-align:right;">Team Role: </td><td><input type="text" name="teamrole" value="<?php if(isset($formteamrole)) echo $formteamrole;?>"></td></tr>

<!--Membership Section of Form-->
<tr style="height:3px;"><td style="background-color:black;"></td><td style="background-color:black;"></td></tr>
<tr><td style="text-align:right;">Membership Type: </td><td>
<!-- //create team select combo box -->
<select name="selectedmembership" id="selectedmembership">
<option value = "0" selected="selected">Select a membership type...</option>
<?php
// get all membership types for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM membertypes';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build teams combo box from above query
while($pbamemberships = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$membershipid = $pbamemberships['MemBTypeId'];
	$pbamembership = $pbamemberships['Type'];
	//make sticky combobox
	if(isset($formmembership) && $formmembership == $membershipid){
		echo '<option value = ' . $membershipid . ' selected="selected">' . $pbamembership . '</option>';}
	else{
		echo '<option value = ' . $membershipid . '>' . $pbamembership . '</option>';}
}

?>
</td></tr>
<tr><td style="text-align:right;">Start Date: </td><td><input type="date" name="membershipstart" value="<?php if(isset($formmembershipstart)) echo $formmembershipstart;?>"></td></tr>

<!--Committee Section of Form-->
<tr style="height:3px;"><td style="background-color:black;"></td><td style="background-color:black;"></td></tr>
<tr><td style="text-align:right;">Committee: </td><td>
<!-- //create committee select combo box -->
<select name="selectedcommittee" id="selectedcommittee">
<option value = "0" selected="selected">Select a committee...</option>
<?php
// get all committees for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM committees ORDER BY CommitteeName';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build committee combo box from above query
while($pbacommittees = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$committeeid = $pbacommittees['CommitteeId'];
	$pbacommittee = $pbacommittees['CommitteeName'];
	//make sticky combobox
	if(isset($formcommittee) && $formcommittee == $committeeid){
		echo '<option value = ' . $committeeid . ' selected="selected">' . $pbacommittee . '</option>';}
	else{
		echo '<option value = ' . $committeeid . '>' . $pbacommittee . '</option>';}
}

?>
</td></tr>
<tr><td style="text-align:right;">Committee Role: </td><td><input type="text" name="committeerole" value="<?php if(isset($formcommitteerole)) echo $formcommitteerole;?>"></td></tr>
<tr style="height:3px;"><td style="background-color:black;"></td><td style="background-color:black;"></td></tr>

<!--Awards Section of Form-->
<tr><td style="text-align:right;">Award Name: </td><td>
<!-- //create award select combo box -->
<select name="selectedaward" id="selectedaward">
<option value = "0" selected="selected">Select an award...</option>
<?php
// get all awards for combo box
require('../connecttopba.php');
$q = 'SELECT * FROM awards ORDER BY AwardName';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build award combo box from above query
while($pbaawards = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$awardid = $pbaawards['AwardID'];
	$pbaaward = $pbaawards['AwardName'];
	//make sticky combobox
	if(isset($formaward) && $formaward == $awardid){
		echo '<option value = ' . $awardid . ' selected="selected">' . $pbaaward . '</option>';}
	else{
		echo '<option value = ' . $awardid . '>' . $pbaaward . '</option>';}
}
$stickyselect = 0; //reset sticky select for next combo box
?>
</select>
</td></tr>
<tr><td style="text-align:right;">Award Comment: </td><td><input type="text" name="awardcomment" value="<?php if(isset($formawardcomment)) echo $formawardcomment;?>"></td></tr>
<tr style="height:3px;"><td style="background-color:black;"></td><td style="background-color:black;"></td></tr>

<!--Accolades Section of Form-->
<tr><td style="text-align:right;">Accolade: </td><td>
<!-- //create award select combo box -->
<select name="selectedaccolade" id="selectedaccolade" onchange="checkCustomOptionAcc()">
<option value = "0" selected="selected">Select an accolade...</option>
<?php
// get all accolades for combo box
require('../connecttopba.php');
$q = 'SELECT Accolade FROM accolades GROUP BY Accolade ORDER BY Accolade';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

// build accolades combo box from above query
while ($accroles = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
    $thisrole = htmlspecialchars($accroles['Accolade']);
    echo '<option value="' . $thisrole . '">' . $thisrole . '</option>';
}
?>
<option value="custom">- Add New Accolade -</option>
</select>
<input type="text" id="customacc" name="customacc" style="display:none;" placeholder="Enter new accolade">
</td></tr>
<tr><td style="text-align:right;">Accolade Comment: </td><td><input type="text" name="accoladecomment"></td></tr>

<!--Volunteer Section of Form-->
<tr style="height:3px;"><td style="background-color:black;"></td><td style="background-color:black;"></td></tr>
<tr><td style="text-align:right;">Volunteer Role: </td>
<?php

?>
<td><select name="volunteerrole" id="selectvolrole" onchange="checkCustomOptionVol()">
<option value="no selection" selected="selected">Select role...</option>
    <?php
    require('../connecttopba.php');
    $q = 'SELECT Role FROM volunteers GROUP BY Role ORDER BY Role';
    $r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
    while ($volroles = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
        $thisrole = htmlspecialchars($volroles['Role']);
        echo '<option value="' . $thisrole . '">' . $thisrole . '</option>';
    }
    ?>
    <option value="custom">-- Add New Role --</option>
</select>

<input type="text" id="volcustomrole" name="volcustomrole" style="display:none;" placeholder="Enter new role">

</td></tr>

<!--Employee Section of Form-->
<tr style="height:3px;"><td style="background-color:black;"></td><td style="background-color:black;"></td></tr>
<tr><td style="text-align:right;">Employee Role: </td><td>
<select name="employeerole" id="selectemprole" onchange="checkCustomOptionEmp()">
<option value="no selection" selected="selected">Select role...</option>
    <?php
    require('../connecttopba.php');
    $q = 'SELECT Role FROM employees GROUP BY Role ORDER BY Role';
    $r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
    while ($emproles = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
        $thisrole = htmlspecialchars($emproles['Role']);
        echo '<option value="' . $thisrole . '">' . $thisrole . '</option>';
    }
    ?>
    <option value="custom">-- Add New Role --</option>
</select>

<input type="text" id="customemprole" name="customemprole" style="display:none;" placeholder="Enter new role">

</td></tr></table>

<input type="submit" value="Save" name="savechanges">

</form>

<?php
include('pbaincludes/pbafooter.html');
?>
<script>
function checkCustomOptionAcc() {
    var select = document.getElementById("selectedaccolade");
    var customInput = document.getElementById("customacc");

    if (select.value === "custom") {
        customInput.style.display = "inline";  // Show input box
        customInput.focus();
    } else {
        customInput.style.display = "none";   // Hide input box
    }
}
</script>
<script>
function checkCustomOptionVol() {
    var select = document.getElementById("selectvolrole");
    var customInput = document.getElementById("volcustomrole");

    if (select.value === "custom") {
        customInput.style.display = "inline";  // Show input box
        customInput.focus();
    } else {
        customInput.style.display = "none";   // Hide input box
    }
}
</script>
<script>
function checkCustomOptionEmp() {
    var select = document.getElementById("selectemprole");
    var customInput = document.getElementById("customemprole");

    if (select.value === "custom") {
        customInput.style.display = "inline";  // Show input box
        customInput.focus();
    } else {
        customInput.style.display = "none";   // Hide input box
    }
}
</script>
</body></html>