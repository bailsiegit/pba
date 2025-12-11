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
		$q = "SELECT MembId, members.FirstName, members.LastName, members.Email,  EGroupId FROM emailrecipients 
		INNER JOIN members ON members.MemberID = emailrecipients.MembId WHERE EGroupId = ?"; // get the member names and emails in the group
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $eg);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		$titles = array("Member No", "FirstName", "LastName", "Email");
		$emailsfile = fopen("downloads/pbaemails.csv", "w");
		fputcsv($emailsfile, $titles); //put titles in output file
			while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
			{
				fputcsv($emailsfile, $row);
			}
		fclose($emailsfile);
		mysqli_free_result($r);
		mysqli_close($link);
		header('Location: pbadownloadcsv.php?fn=pbaemails');
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

<h3>Email Group Manager</h3>

<form action="pbaemails.php" method="POST">

<?php
// Mail group dropdown
$q = "SELECT * FROM emailgroups ORDER BY EGroupName";
$postgroup = isset($_POST['selectegroup']) ? htmlentities($_POST['selectegroup']) : 0;
require('../connecttopba.php');
$r = mysqli_query($link, $q);
?>
<div style="margin-bottom:18px;">
  <label for="selectegroup">Select Mail Group:</label>
  <select name="selectegroup" id="selectegroup" onchange="updateSelectArea()">
    <option value="0">Select mail group...</option>
    <?php
    while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
	{
        $selected = ($row['EGroupId'] == $postgroup) ? ' selected' : '';
        echo '<option value="'.$row['EGroupId'].'"'.$selected.'>'.$row['EGroupName'].'</option>';
    }
    ?>
  </select>
  <hr>
</div>

<!-- Main three-column area -->
<style>
  .pba-row { display:flex; gap:20px; align-items:flex-start; }
  .pba-col { flex:1; display:flex; flex-direction:column; }
  .pba-mid { display:flex; flex-direction:column; justify-content:center; gap:10px; align-items:center; width:120px; }
  .select-wrap { padding:8px; background:#fff; }
  .select-wrap select { width:100%; height:260px; box-sizing:border-box; }
  .header-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
</style>

<div class="pba-row">

  <!-- Left column: Current members + Download button on same header line -->
  <div class="pba-col">
    <div class="header-row">
      <div><strong>Current Group Members</strong></div>

      <!-- Download button placed here; initially hidden -->
      <div id="dlbutton" style="visibility:<?php if(isset($postgroup) && $postgroup > 0){echo 'visible';}else{echo 'hidden';}?>;">
        <input type="submit" name="dlemaillist" id="dlemaillist" value="Download Email List">
      </div>
    </div>

    <div id="egroupselect" class="select-wrap">
      <?php
      if ($postgroup > 0)
	  {
          include("pbaget_emailselect.php");
      }
      ?>
    </div>
  </div>

  <!-- Middle column: Add / Remove buttons (vertical) -->
  <?php
  if($_SESSION['accesslevel'] > 2)
  {
	  ?>
  <div class="pba-mid">
    <input type="submit" name="addtoemails" value="<< Add" style="min-width:100px; margin:100px 30px 30px 30px">
    <input type="submit" name="delfromemails" value="Remove >>" style="min-width:100px;">
  </div>

  <!-- Right column: Non-members select -->
  <div class="pba-col">
    <div class="header-row"><div><strong>Non-members</strong></div></div>
    <div id="nonmembers" class="select-wrap">
      <?php
      if ($postgroup > 0)
	  {
          include("pbaget_emailnonselect.php");
      }
      ?>
    </div>
  </div>
<?php
  }
  ?>
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