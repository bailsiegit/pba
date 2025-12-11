<?php
//Rev 1 19/11/2025
//this page is called from either the loading of pbaactivitymemberships.php
//or the javascript on that page
//this page then limits the membership types combo box to only those membership categories (types) 
//that have members for the selected year
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../connecttopba.php');
//if called from page load use existing page values else use GET parameters
$year = (isset($_GET['yid'])) ? (int) $_GET['yid'] : $my;
$membershiptype = (isset($_GET['mid'])) ? (int) $_GET['mid'] : $mt;
//get year text for display as required
$qyear = "SELECT YearText FROM years WHERE YearId = ?";
$stmt = mysqli_prepare($link, $qyear);
mysqli_stmt_bind_param($stmt, "i", $year);
mysqli_stmt_execute($stmt);
$ryear = mysqli_stmt_get_result($stmt);

if (!$ryear) 
{
	die('Year query failed: ' . mysqli_error($link));
}
$yeartext = mysqli_fetch_assoc($ryear);
//get membership types with members for the selected year to populate combo box    
$membtypesQuery = "SELECT memberships.Mtype, Type FROM (membertypes 
INNER JOIN memberships ON membertypes.MemBTypeId = memberships.Mtype) 
WHERE memberships.Year = ? GROUP BY Type";
$stmt = mysqli_prepare($link, $membtypesQuery);
mysqli_stmt_bind_param($stmt, "i", $year);
mysqli_stmt_execute($stmt);
$membertypeResult = mysqli_stmt_get_result($stmt);
if (!$membertypeResult) 
{
    die('membership query failed: ' . mysqli_error($link));
}
    
if (mysqli_num_rows($membertypeResult) > 0) 
{
	echo '<option value="0">Select membership...</option>';
    while ($membtype = mysqli_fetch_assoc($membertypeResult)) 
	{
		$selected = ($membershiptype == $membtype['Mtype']) ? ' selected="selected"' : '';
        echo '<option value="' . $membtype['Mtype'] . '"'.$selected.'>' . $membtype['Type'] . '</option>';
    }
} 
else
{
    echo '<option value="0">No members found in '.$yeartext['YearText'].'</option>';
}

?>
