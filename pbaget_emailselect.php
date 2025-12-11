<?php
//Rev 1 19/11/2025
//this page is called from pbaemails.php javascript or the page itself
//this page builds a select box of the members of the email group

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');
//get members in selected email group
if(isset($_GET['mgid'])) //email group id from javascript
{
	$egroup = (int) $_GET['mgid'];
}
elseif(isset($postgroup)) //email group id exists if called from within page
{
	$egroup = (int) $postgroup;
}
$q = "SELECT members.FirstName, members.LastName, MembId, EReceiverId, EGroupId FROM emailrecipients 
INNER JOIN members ON members.MemberID = emailrecipients.MembId WHERE EGroupId = ? ORDER BY LastName, FirstName"; // get the member names in the group
$stmt = mysqli_prepare($link, $q);
mysqli_stmt_bind_param($stmt, "i", $egroup);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
if(mysqli_num_rows($r) > 0)
{
	//build combo box for group members
	echo '<select size="20" name="currentmembers[]" id="currentmembers" multiple>';
	while($row = mysqli_fetch_assoc($r))
	{
		echo '<option value="'.$row['MembId'].'">'.$row['LastName'].', '.$row['FirstName'].'</option>';
	}
	echo '</select>';
}
else
{
	echo "Nobody assigned to email group";
}
?>
