<?php
//Rev 3 21/4/2026 - added timeout check
//this page is called either directly or via javascript from the incident activity page
//this page creates the table of results to display on that page
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();
	load('pbalogin.php?disp=1'); //redirect to login page
	exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');
//set view to all if not sent via GET
$getview = (isset($_GET['vid'])) ? (int) $_GET['vid'] : 0;
//define query scope text depending on view selected
switch ($getview)
{
	case 0:
		$viewscope = "";
		$noresults = "incidents";
		break;
	case 1:
		$viewscope = " WHERE incident.TribunalResult IS NULL";
		$noresults = "open incidents";
		break;
	case 2:
		$viewscope = " WHERE incident.ExpiryDate > CURDATE()";
		$noresults = "current penalties";
		break;
}

//get results from incident table depending on selected view
$incidentQuery = "SELECT incident.IncidentId, incident.IncidentDate, incident.IncidentDetails, incident.TribunalResult, 
incident.ExpiryDate, incident.YearId, members.MemberId, members.FirstName, 
members.LastName 
FROM members	 
INNER JOIN incident ON members.MemberId = incident.MembId $viewscope";
$incidentResult = mysqli_query($link, $incidentQuery);
if (!$incidentResult) 
{
   die('incident query failed: ' . mysqli_error($link));
}

echo '<p> </p><table width="100%">';
if (mysqli_num_rows($incidentResult) > 0) 
{
	if($_SESSION['accesslevel'] > 3)
	{
		echo '<tr><th>Name</th><th>Date</th><th>Details</th><th>Result</th><th>Expiry</th><th>Docs</th><th>Edit</th><th>Delete</th></tr>'; //headers for year based list with delete
	}
	elseif($_SESSION['accesslevel'] > 2)
	{
		echo '<tr><th>Name</th><th>Date</th><th>Details</th><th>Result</th><th>Expiry</th><th>Docs</th><th>Edit</th></tr>';
	}
	else
	{
		echo '<tr><th>Name</th><th>Date</th><th>Details</th><th>Result</th><th>Expiry</th><th>Docs</th></tr>';
	}
	while ($incident = mysqli_fetch_assoc($incidentResult)) 
	{
		$expdate = ($incident['ExpiryDate'] == "0000-00-00") ? "" : date("d/m/Y", strtotime($incident['ExpiryDate']));
		$expdate = (!empty($incident['ExpiryDate']) && strtotime($incident['ExpiryDate']) > time()) ? '<span Style="color:red;">'.$expdate.'</span>' : $expdate;
		if($_SESSION['accesslevel'] > 3)
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$incident['MemberId'].'">'.$incident['FirstName'].' '.$incident['LastName'].'</a></td>
				<td>'.date("d/m/Y", strtotime($incident['IncidentDate'])).'</td><td>'.$incident['IncidentDetails'].'</td>
				<td>'.$incident['TribunalResult'].'</td>
				<td>'.$expdate.'</td>
				<td><a class="buttonlink" href="pbainddocslist.php?actid=in&yid='.$incident['YearId'].'&refid='.$incident['IncidentId'].'">Docs</a></td>
				<td><a class="buttonlink" href="pbaeditincident.php?iid='.$incident['IncidentId'].'">Edit</a></td>
				<td><a onclick="return confirm(\'Are you sure?\');" class="buttonlink" href="pbadeleterecords.php?inid='.$incident['IncidentId'].'">Delete</a></td></tr>';
		}
		elseif($_SESSION['accesslevel'] > 2)
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$incident['MemberId'].'">'.$incident['FirstName'].' '.$incident['LastName'].'</a></td>
				<td>'.date("d/m/Y", strtotime($incident['IncidentDate'])).'</td><td>'.$incident['IncidentDetails'].'</td>
				<td>'.$incident['TribunalResult'].'</td>
				<td>'.$expdat.'</td>
				<td><a class="buttonlink" href="pbainddocslist.php?actid=in&yid='.$incident['YearId'].'&refid='.$incident['IncidentId'].'">Docs</a></td>
				<td><a class="buttonlink" href="pbaeditincident.php?iid='.$incident['IncidentId'].'">Edit</a></td></tr>';
		}
		else
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$incident['MemberId'].'">'.$incident['FirstName'].' '.$incident['LastName'].'</a></td>
				<td>'.date("d/m/Y", strtotime($incident['IncidentDate'])).'</td><td>'.$incident['IncidentDetails'].'</td>
				<td>'.$incident['TribunalResult'].'</td>
				<td>'.$expdat.'</td>
				</tr>';
		}
	}		
}
else 
{
	echo "No $noresults found.<br><br>";
}
echo '</table>';

?>
