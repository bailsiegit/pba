<?php
//Rev 1 12/12/2025
//this page is called either directly or via javascript from the accolades activity page
//this page creates the table of results to display on that page
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');

$getyear = (isset($_GET['yid'])) ? (int) $_GET['yid'] : $y;
//$getrole = (isset($_GET['rid'])) ? htmlentities($_GET['rid']) : $rl;
$qyear = "SELECT YearText FROM years WHERE YearId = ?";
$stmt = mysqli_prepare($link, $qyear);
mysqli_stmt_bind_param($stmt, "i", $getyear);
mysqli_stmt_execute($stmt);
$ryear = mysqli_stmt_get_result($stmt);

if (!$ryear) 
{
	die('Year query failed: ' . mysqli_error($link));
}
$yeartext = mysqli_fetch_assoc($ryear);
    

echo '<table style="width:100%;"><tr>';
echo '<td style="width:50%; background:white; border:0px;">';
//if(strlen($getrole) == 1)//if $getrole is zero then display for selected year
//{
	$accoladeQuery = "SELECT accolades.Accolade, accolades.Details, accolades.AccoladeId, members.MemberId, members.FirstName, 
	members.LastName 
	FROM members	 
	INNER JOIN accolades ON members.MemberId = accolades.MembId 
	WHERE accolades.YearId = ?";
	$stmt = mysqli_prepare($link, $accoladeQuery);
	mysqli_stmt_bind_param($stmt, "i", $getyear);
	echo '<h4>'.$yeartext['YearText'].' - Accolades</h4>';
//}
//else //if $getrole has something other than zero, display for selected role
//{
//	$volunteerQuery = "SELECT years.YearText, volunteers.Role, volunteers.VolId, members.MemberId, members.FirstName, members.LastName 
	//FROM members	 
//	INNER JOIN volunteers ON members.MemberId = volunteers.MembId
//	INNER JOIN years ON volunteers.Year = years.YearId 
//	WHERE volunteers.Role = ? 
//	ORDER BY years.YearId DESC";
//	$stmt = mysqli_prepare($link, $volunteerQuery);
//	if ($stmt === false) {
  //  die("Prepare failed: " . mysqli_error($link) . "<br>Query: $q");
//}
//	mysqli_stmt_bind_param($stmt, "s", $getrole);
//	echo '<h4>'.$getrole.' - Volunteers</h4>';
//}
	
mysqli_stmt_execute($stmt);
$accoladeResult = mysqli_stmt_get_result($stmt);
if (!$accoladeResult) 
{
   die('accolade query failed: ' . mysqli_error($link));
}
echo '</td><td style="width:50%; background:white; border:0px;">  </td></tr></table>';
echo '<p> </p><table width="90%">';
if (mysqli_num_rows($accoladeResult) > 0) 
{
//	if(strlen($getrole) > 1)
//	{
//		echo '<tr><th>Year</th><th>Name</th><th>Role</th></tr>'; //header for role based list
//	}
	if($_SESSION['accesslevel'] > 3)
	{
		echo '<tr><th>Name</th><th>Accolade</th><th>Details</th><th>Delete</th></tr>'; //headers for year based list with delete
	}
	else
	{
		echo '<tr><th>Name</th><th>Accolade</th><th>Details</th></tr>'; //headers for year based list
	}

	while ($accolade = mysqli_fetch_assoc($accoladeResult)) 
	{
		//if(strlen($getrole) > 1)
		//{
		//	echo '<tr><td>'.$accolade['YearText'].'</td>
		//		<td><a href="pbaperson.php?pid='.$accolade['MemberId'].'">'.$accolade['FirstName'].' '.$accolade['LastName'].'</a></td>
		//		<td>'.$accolade['Accolade'].'</td><td>'.$accolade['Details'].'</tr>';
		//}
		if($_SESSION['accesslevel'] > 3)
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$accolade['MemberId'].'">'.$accolade['FirstName'].' '.$accolade['LastName'].'</a></td>
				<td>'.$accolade['Accolade'].'</td><td>'.$accolade['Details'].'</td>
				<td><a onclick="return confirm(\'Are you sure?\');" class="buttonlink" href="pbadeleterecords.php?acid='.$accolade['AccoladeId'].'&yid='.$getyear.'">Delete</a></td></tr>';
		}
		else
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$accolade['MemberId'].'">'.$accolade['FirstName'].' '.$accolade['LastName'].'</a></td>
				<td>'.$accolade['Accolade'].'</td><td>'.$accolade['Details'].'</td></tr>';
		}
	}		
}
else 
{
	echo '<option value="">No accolades found in '.$yeartext['YearText'].'</option><br><br>';
}
echo '</table>';

?>
