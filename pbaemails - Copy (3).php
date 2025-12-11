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
//echo '<pre>';
//print_r($_POST);
//echo '</pre>';
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
<!--<div style="display:flex; justify-content:flex-start; gap:20px;">
<div style="flex:50%">-->
<h3>Email Group Manager</h3>

<form action="pbaemails.php" method="POST">

    <!-- Mail group selection -->
    <?php
    $q = "SELECT * FROM emailgroups ORDER BY EGroupName";
    $postgroup = isset($_POST['selectegroup']) ? htmlentities($_POST['selectegroup']) : 0;
    require('../connecttopba.php');
    $r = mysqli_query($link, $q);
    ?>
    <div style="margin-bottom:20px;">
        <label for="selectegroup">Select Mail Group:</label>
        <select name="selectegroup" id="selectegroup" onchange="updateSelectArea()">
            <option value="0">Select mail group...</option>
            <?php
            while($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                $selected = ($row['EGroupId'] == $postgroup) ? 'selected' : '';
                echo '<option value="'.$row['EGroupId'].'" '.$selected.'>'.$row['EGroupName'].'</option>';
            }
            ?>
        </select>
    </div>

    <!-- Main content: flex container -->
    <div style="display:flex; align-items:center; gap:20px;">

        <!-- Current Members -->
        <div style="flex:1; text-align:center;">
            <label>Current Group Members:</label>
            <div id="egroupselect" style="margin-top:5px;">
                <?php
                if($postgroup > 0) {
                    include("pbaget_emailselect.php"); // initial page load
                }
                ?>
            </div>
        </div>

        <!-- Add / Remove Buttons -->
        <div style="display:flex; flex-direction:column; justify-content:center; gap:10px;">
            <input type="submit" name="addtoemails" value="Add">
            <input type="submit" name="delfromemails" value="Remove">
        </div>

        <!-- Non-Members -->
        <div style="flex:1; text-align:center;">
            <label>Non-members:</label>
            <div id="nonmembers" style="margin-top:5px;">
                <?php
                if($postgroup > 0) {
                    include("pbaget_emailnonselect.php");
                }
                ?>
            </div>
        </div>

    </div>

    <!-- Optional Download button -->
    <div id="dlbutton" style="visibility:<?php if(isset($postgroup) && $postgroup > 0){echo "visible";}else{echo "hidden";}?>; margin-top:20px;">
        <input type="submit" name="dlemaillist" id="dlemaillist" value="Download Email List">
    </div>

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