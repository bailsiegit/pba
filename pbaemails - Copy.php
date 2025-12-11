<?php
//Rev 1 19/11/2025
//this page is for managing email lists
//select a list and the move people in or out of the list

session_start();
//check that user is logged in and has not been idle for too long
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();
	load(); //redirect to login page
}
$page_title = 'Admin';
include('pbaincludes/pbaheader.html');

$_SESSION['timeoutstart'] = time(); //reset login timeout marker
echo '<pre>';
print_r($_POST);
echo '</pre>';
?>
<h2>Admin</h2>
<?php
include("pbaincludes/pbaadminmenu.php");
?>
<hr>

<?php
if($_SESSION['accesslevel'] > 1)
{ 
	//check user has access to get email lists
	//if email list button active
	if(isset($_POST['dlemaillist']))
	{
		$eg = $_POST['selectegroup'];
		$q = "SELECT members.FirstName, members.LastName, members.Email, MembId EGroupId FROM emailrecipients 
		INNER JOIN members ON members.MemberID = emailrecipients.MembId WHERE EGroupId = ?"; // get the member names and emails in the group
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $eg);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		$titles = array("Member No", "FirstName", "LastName", "Email");
		$emailsfile = fopen("pbaemails.csv", "w");
		fputcsv($emailsfile, $titles); //put titles in output file

			
			
			//$r = mysqli_query($link, "SELECT FirstName, LastName, Email FROM members WHERE Newsletter > 0",MYSQLI_STORE_RESULT);
			while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
			{
				fputcsv($emailsfile, $row);
			}
		fclose($emailsfile);
		mysqli_free_result($r);
		mysqli_close($link);
	}

	
	//Process adding to email Group
	if(isset($_POST['addtoemails']) && isset($_POST['notmembers']))
	{
		$q = "INSERT IGNORE INTO emailrecipients (MembId, EgroupId) VALUES (?, ?)";
		$emg = htmlentities($_POST['selectegroup']);
		$nm = $_POST['notmembers'];
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		foreach($nm as $value)
		{
			//get existing record to learn MemberID and Role
			$cleanvalue = htmlentities($value);

			//create new record
			mysqli_stmt_bind_param($stmt, "ii", $value, $emg);
			mysqli_stmt_execute($stmt);
		}
	}
	
	//process delete from group
	if(isset($_POST['delfromemails']) && isset($_POST['currentmembers']))
	{
		$q = "DELETE FROM emailrecipients WHERE MembId = ? AND EGroupId = ?";
		$emg = htmlentities($_POST['selectegroup']);
		$cm = $_POST['currentmembers'];
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		foreach($cm as $value)
		{
			//get existing record to learn MemberID and Role
			$cleanvalue = htmlentities($value);

			//delete record
			mysqli_stmt_bind_param($stmt, "ii", $value, $emg);
			mysqli_stmt_execute($stmt);
		}
	}

?>
<table style="width:100%;"><tr style="background-color:white; border:0px;"><td style="width:50%; background-color:white; border:0px;">
<h3>Email Group Manager</h3>

Select Email Group: 
<form action="pbaemails.php" method="POST">
<?php
//create mail group selection combo
$q = "SELECT * FROM emailgroups ORDER BY EGroupName";
$postgroup = (isset($_POST['selectegroup'])) ? htmlentities($_POST['selectegroup']) : 0;//if refreshing after add or remove get email group id from POST
require('../connecttopba.php');
$r = mysqli_query($link, $q);
?>
<select name="selectegroup" id="selectegroup" onchange="updateSelectArea()">
<option value="0">Select mail group...</option>
<?php
while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	if($row['EGroupId'] == $postgroup)
	{
		echo '<option value="'.$row['EGroupId'].'" selected="selected">'.$row['EGroupName'].'</option>';
	}
	else
	{
		echo '<option value="'.$row['EGroupId'].'">'.$row['EGroupName'].'</option>';
	}
}
?>
	</select>
	</td><td style="width:50%; background-color:white; border:0px;">
	<div id="dlbutton" style="visibility:hidden;"><input type="submit" name="dlemaillist" id="dlemaillist" value="Download Email List"></div>
	</td></tr></table>
	<hr>
	<table style="width:80%"><tr><th style="text-align:center;">Current Group Members</th>
	<th style="width:10%; text-align:center;">Action</th>
	<th style="text-align:center;">Non-members</th></tr>
	<tr><td style="border:0px; background-color:white;">
	
	<div id="egroupselect">
	<?php
	if($postgroup > 0)
	{
		include("pbaget_emailselect.php");
	}
	?>
	</div></td>
	<td style="border:0px; background-color:white;">
	<input type="submit" name="delfromemails" id="delfromemails"value="Remove" style="margin:30px; text-align:center;">
	<input type="submit" name="addtoemails" id="addtoemails"value="Add" style="margin:30px; text-align:center;"></td>
	<td style="border:0px; background-color:white;">
	<div id="nonmembers">
		<?php
	if($postgroup > 0)
	{
		include("pbaget_emailnonselect.php");
	}
	?>
	</div></td>
	</tr></table>
	</form>
	<br>
<?php
}
else
{
	echo "You do not have permission to access this page.";
	echo "<br><br>";
}
?>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
function updateSelectArea() {
    var egroup = document.getElementById("selectegroup").value;
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "pbaget_emailselect.php?mgid=" + egroup, true);
    xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && xhr.status == 200) {
        document.getElementById("egroupselect").innerHTML = xhr.responseText;
        }
    };
    xhr.send();
	document.getElementById("dlbutton").style.visibility = "visible";
	updateNonselectArea();
}
function updateNonselectArea() {
	var egroup = document.getElementById("selectegroup").value;
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "pbaget_emailnonselect.php?nmid=" + egroup, true);
    xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && xhr.status == 200) {
        document.getElementById("nonmembers").innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}
</script>
</body></html>