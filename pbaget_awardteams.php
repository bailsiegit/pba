<?php
//Rev 1 14/8/2026
//this page is called from pbaactivityawards.php when a team award is selected for display
//this page then limits the teams combo box to only those teams 
//that have members who have won the selected award
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');
//if called from pageload use existing variables else use GET values
$getaward = (isset($_GET['aid'])) ? (int)$_GET['aid'] : $formaward;
//find teams that have members for selected year
	$q = "SELECT 
	t.TeamName, 
	t.TeamId
	FROM teams t
	JOIN awardwinners aw ON t.TeamId = aw.TeamId
	WHERE aw.AwardId = $getaward
	GROUP BY t.TeamId
	ORDER BY t.TeamName";
	require('../connecttopba.php');
	$r = mysqli_query($link, $q);
	if(mysqli_num_rows($r) < 1)
	{
		echo 'ok';
	}
	else
	{
		$isteam = mysqli_fetch_array($r, MYSQLI_ASSOC);
		mysqli_data_seek($r, 0); //reset to start of result set
		if($isteam['TeamId'] == 54)
		{
			echo 'ok';
		}
		else
		{
			echo '<option value="0">Select team...</option>';
			while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
			{
				echo '<option value="'.$row['TeamId'].'">'.$row['TeamName'].'</option>';
			}
		}
	}
?>
