<?php
//Rev 1 19/11/2025
//this page is called either directly or via javascript from the membership activity page
//this page creates the table of results to display on that page
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');

$getyear = (isset($_GET['yid'])) ? (int) $_GET['yid'] : $my;
$getmembership = (isset($_GET['mid'])) ? (int)$_GET['mid'] : $mt;
$qyear = "SELECT YearText, YearId FROM years WHERE YearId = ?";
$stmt = mysqli_prepare($link, $qyear);
mysqli_stmt_bind_param($stmt, "i", $getyear);
mysqli_stmt_execute($stmt);
$ryear = mysqli_stmt_get_result($stmt);

if (!$ryear) 
{
    die('Year query failed: ' . mysqli_error($link));
}
$yeartext = mysqli_fetch_assoc($ryear);

$qmembership = "SELECT Type FROM membertypes WHERE MemBTypeId = ?";
$stmt = mysqli_prepare($link, $qmembership);
mysqli_stmt_bind_param($stmt, "i", $getmembership);
mysqli_stmt_execute($stmt);
$rmembership = mysqli_stmt_get_result($stmt);
$membershipname = mysqli_fetch_assoc($rmembership);
?>
<table style="width:100%; background:white; border:0px"><tr><td style="width:50%; background:white; border:0px">
<?php
echo '<h4>'.$yeartext['YearText'].' - '.$membershipname['Type'].'</h4></td>';
echo '<td style="width:50%; background:white; border:0px">';
if($_SESSION['accesslevel'] > 3)
	{
		echo '   <a style="margin:40px 0px 0px 0px;" class="buttonlink" href="pbacopymemberships.php?yid='.$getyear.'&actid='.$getmembership.'">Copy Memberships</a>';
	}
echo '</td></tr></table>';	
$membershipQuery = "SELECT mbid, mn, st, msid, end, rc, members.FirstName, members.LastName FROM
	(SELECT Mtype AS mn, 
	MembId AS mbid, 
	start AS st, 
	end AS end, 
	receipt AS rc, 
	MshipId AS msid 
	FROM memberships 
	INNER JOIN years ON memberships.Year = years.YearId
	INNER JOIN membertypes ON memberships.Mtype = membertypes.MemBTypeId 
	WHERE memberships.Year = ? AND memberships.Mtype = ?) 
	AS membershipdata 
	INNER JOIN members ON membershipdata.mbid = members.MemberID";
$stmt = mysqli_prepare($link, $membershipQuery);
mysqli_stmt_bind_param($stmt, "ii", $getyear, $getmembership);
mysqli_stmt_execute($stmt);
$membershipResult = mysqli_stmt_get_result($stmt);

if (!$membershipResult) 
{
    die('membership query failed: ' . mysqli_error($link));
}

echo '<p> </p><table width="90%">';
if($_SESSION['accesslevel'] > 3)
{
	echo '<tr><th>Name</th><th>Start</th><th>End</th><th>Receipt</th><th>Update</th><th>Delete</th></tr>';
}
elseif($_SESSION['accesslevel'] > 2)
{
	echo '<tr><th>Name</th><th>Start</th><th>End</th><th>Receipt</th><th>Update</th></tr>';
}
else
{
	echo '<tr><th>Name</th><th>Start</th><th>End</th><th>Receipt</th></tr>';
}
if (mysqli_num_rows($membershipResult) > 0) 
{
    while ($membership = mysqli_fetch_array($membershipResult, MYSQLI_ASSOC)) 
	{
		$membership['st'] = ($membership['st'] == "0000-00-00") ? "" : date("d/m/Y", strtotime($membership['st']));
		$membership['end'] = ($membership['end'] == "0000-00-00") ? "" : date("d/m/Y", strtotime($membership['end']));			
		if($_SESSION['accesslevel'] > 3 )
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$membership['mbid'].'">'.$membership['FirstName'].' '.$membership['LastName'].'</a></td>
			<td>'.$membership['st'].'</td><td>'.$membership['end'].'</td><td>'.$membership['rc'].'</td>
			<td><a class="buttonlink" href="pbaeditmembership.php?msid='.$membership['msid'].'">Edit</a></td>
			<td><a onclick="return confirm(\'Are you sure?\');" class="buttonlink" href="pbadeleterecords.php?msid='.$membership['msid'].'
			&yid='.$getyear.'&mid='.$getmembership.'">Delete</a></td></tr>';
		}
		elseif($_SESSION['accesslevel'] > 2)
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$membership['mbid'].'">'.$membership['FirstName'].' '.$membership['LastName'].'</a></td>
			<td>'.$membership['st'].'</td><td>'.$membership['end'].'</td><td>'.$membership['rc'].'</td>
			<td><a class="buttonlink" href="pbaeditmembership.php?msid='.$membership['msid'].'">Edit</a></td></tr>';
		}
		else
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$membership['mbid'].'">'.$membership['FirstName'].' '.$membership['LastName'].'</a></td>
			<td>'.$membership['st'].'</td><td>'.$membership['end'].'</td><td>'.$membership['rc'].'</td></tr>';
		}
    }
		
} 
else 
{
    echo '<option value="">No '.$membershipname['Type'].'members found in '.$yeartext['YearText'].'</option>';
}
echo '</table>';

?>
