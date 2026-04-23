<?php
//Rev 2 21/4/2026 - added timeout check
//this page is called either directly or via javascript from the accolades activity page
//this page creates the table of results to display on that page
if(isset($_GET['java']) && $_GET['java'] == 1)
{
	session_start();
}
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	session_unset();
	session_destroy();
	header('Location: pbalogin.php?disp=1');
	exit();
}

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


// put a title above the results
echo '<h4>'.$yeartext['YearText'].' - Accolades</h4>';

//get all accolades for the selected year
$accoladeQuery = "SELECT accolades.Accolade, accolades.Details, accolades.AccoladeId, members.MemberId, members.FirstName, 
members.LastName 
FROM members	 
INNER JOIN accolades ON members.MemberId = accolades.MembId 
WHERE accolades.YearId = ?";
$stmt = mysqli_prepare($link, $accoladeQuery);
mysqli_stmt_bind_param($stmt, "i", $getyear);
mysqli_stmt_execute($stmt);
$accoladeResult = mysqli_stmt_get_result($stmt);
if (!$accoladeResult) 
{
   die('accolade query failed: ' . mysqli_error($link));
}

//table for results
echo '<table width="90%">';
if (mysqli_num_rows($accoladeResult) > 0) 
{
	if($_SESSION['accesslevel'] > 3)
	{
		echo '<tr><th>Name</th><th>Accolade</th><th>Details</th><th>Document</th><th>Delete</th></tr>'; //headers for year based list with delete
	}
	else
	{
		echo '<tr><th>Name</th><th>Accolade</th><th>Details</th><th>Document</th></tr>'; //headers for year based list
	}

	while ($accolade = mysqli_fetch_assoc($accoladeResult)) 
	{
		//find how many documents are associated with the Accolade
		$curracc = $accolade['AccoladeId'];
		$qdocs = "SELECT COUNT(FileName) AS NumbFiles FROM documents WHERE ActivityRef = $curracc AND Activity = 'ac'";
		$rdocs = mysqli_query($link, $qdocs, MYSQLI_STORE_RESULT);
		$rdocarray = mysqli_fetch_array($rdocs, MYSQLI_ASSOC);
		$numdocs = $rdocarray['NumbFiles'];
		$deletestring = ($numdocs > 0) ? 'Delete' : '<a onclick="return confirm(\'Are you sure?\');" 
				class="buttonlink" href="pbadeleterecords.php?acid='.$accolade['AccoladeId'].'&yid='.$getyear.'">Delete</a>';
		
		if($_SESSION['accesslevel'] > 3)
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$accolade['MemberId'].'">'.$accolade['FirstName'].' '.$accolade['LastName'].'</a></td>
				<td>'.$accolade['Accolade'].'</td><td>'.$accolade['Details'].'</td>
				<td><a class="buttonlink" href="pbainddocslist.php?actid=ac&refid='.$accolade['AccoladeId'].'&yid='.$getyear.'">Documents 
				<span style="font-weight:normal; font-size:0.8em;">('.$numdocs.')</span></a></td>
				<td>'.$deletestring.'</td></tr>';
		}
		else
		{
			echo '<tr><td><a href="pbaperson.php?pid='.$accolade['MemberId'].'">'.$accolade['FirstName'].' '.$accolade['LastName'].'</a></td>
				<td>'.$accolade['Accolade'].'</td><td>'.$accolade['Details'].'</td>
				<td><a class="buttonlink" href="pbainddocslist.php?actid=ac&refid='.$accolade['AccoladeId'].'&yid='.$getyear.'">Documents 
				<span style="font-weight:normal; font-size:0.8em;">('.$numdocs.')</span></a></td>
				</tr>';
		}
	}		
}
else 
{
	echo '<option value="">No accolades found in '.$yeartext['YearText'].'</option><br><br>';
}
echo '</table>';

?>
