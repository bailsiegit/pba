<?php
//Rev 1 19/11/2025
//this page is called from pbaemails.php javascript
//this page selects the members of the email group
//and those not in the group so that members can be moved in either direction 

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');

    
if(isset($_GET['nmid']))
{
	$egroup = (int) $_GET['nmid'];
}
elseif(isset($postgroup))
{
	$egroup = (int)$postgroup;
}
	$q = "SELECT FirstName, LastName, MemberID FROM members t2 WHERE NOT exists 
	(SELECT * FROM emailrecipients t1 WHERE t1.MembId = t2.MemberID AND t1.EGroupId = ?) ORDER BY LastName, FirstName";
	 // get the member names in the group
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $egroup);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	if(mysqli_num_rows($r) > 0)
	{
		//build combo box for group notmembers
		echo '<select size="20" name="notmembers[]" id="notmembers" multiple>';
		while($row = mysqli_fetch_assoc($r))
		{
			echo '<option value="'.$row['MemberID'].'">'.$row['LastName'].', '.$row['FirstName'].'</option>';
		}
		echo '</select>';
	}
	else
	{
		echo "Everybody assigned to this mail group";
	}
?>
