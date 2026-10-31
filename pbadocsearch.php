<?php
//Rev 3 26/10/2026 - Added file viewer
//this page is used to find documents across all categories
//keywords are entered and searched against the document titles
//a list of matching files is displayed and hyperlinked for download
//pdf and images can be displayed immediately with the view button
session_start();
//check that user is logged in and has not been idle for too long
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	session_unset();
	session_destroy();
	header('Location: pbalogin.php'); //redirect to login page
	exit();
}
$page_title = 'Documents';
include('pbaincludes/pbaheader.html');

$_SESSION['timeoutstart'] = time(); //reset login timeout marker

?>
<?php
include('pbaincludes/pbadocsmenu.php');
?>
<h2>Document Search</h2>
<?php
# check the user has access to this page
if($_SESSION['accesslevel'] < 1)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>

<!-- search form -->
<form action="pbadocsearch.php" method = "POST"> 
Keyword/s: <input size="60" type="text" name="keywords"><br>
Document Year: <span style="font-size:0.7em">(Optional)</span>: <input type="number" name="searchyear" min="1963" max="2050">
Category <span style="font-size:0.7em">(Optional)</span>: <select name="doccategory" id="doccategory">
<option value="a">Select a category...</option>
<option value="tm">Team</option>
<option value="cm">Committee</option>
<option value="in">Incident</option>
<option value="ac">Accolade</option>
<option value="gn">General</option>
<option value="vl">Volunteer</option>
</select>
<input style="margin:2px" type="submit" name="searchbutton" value="Search">
</form>

<hr>
<table style="width:100%;"><tr><td style="border:0px; background-color:white; width:50%">
<?php
if(isset($_POST['searchbutton']))
{
	$nullqueries = 0; //check count for empty queries (not used as yet)
	require("../connecttopba.php"); //connect to database
	// sanitise inputs
	$kw = mysqli_real_escape_string($link,trim($_POST['keywords']));
	$sy = mysqli_real_escape_string($link,trim($_POST['searchyear']));
	$sc = mysqli_real_escape_string($link,trim($_POST['doccategory']));

	// split keywords into array of separate words
	// get first keyword
	$word = strtok($kw, " ");
	// work through collecting all keywords
	while($word !== false)
	{
		$keywords[] = $word;
		$word = strtok(" ");
	}

	//convert text year input to YearId
	if(isset($_POST['searchyear']))
	{
		$q = "SELECT YearId FROM years WHERE YearText = ?";
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $_POST['searchyear']);
		mysqli_stmt_execute($stmt);
		$ry = mysqli_stmt_get_result($stmt);
		$rowy = mysqli_fetch_array($ry, MYSQLI_ASSOC);
		// search YearId is now $rowy['YearId']
	}
	$qkw = "";
	$datatype = "";
	
	// if any criteria has been entered, process search, otherwise abandon Search
	if(!empty($keywords) || !empty($_POST['searchyear']) || $_POST['doccategory'] !== "a")
	{
		// search query

		if(!empty($_POST['searchyear']))
		{
			$where[] = "YearId = ?"; //to add year criteria to sql
			$datatype .= "i"; //to add to bind param data types
			$criteria[] = $rowy['YearId']; //to add yearid to bind parameters
		}
		
		if($_POST['doccategory'] !== "a")
		{
			$where[] = "Activity = ?"; // to add doc category to sql
			$datatype .= "s"; // to add to bind parameter data types
			$criteria[] = $sc; // to add doc category to bind parameters
		}
		
		if(!isset($keywords)) //if no keywords supplied, search on year and/or category
		{	
			$q = 'SELECT DocumentId FROM documents WHERE '.implode(" AND ", $where);
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, $datatype, ...$criteria);
			mysqli_stmt_execute($stmt);
			$r = mysqli_stmt_get_result($stmt);
			if(mysqli_num_rows($r) > 0) //did the search return any results?
			{
				while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
				{
					$alldocids[] = $row['DocumentId']; //put all found document ids into array
				}
			}
		}
		else
		{
			// for each keyword find matching records
			$datatype = "s".$datatype; // add to bind data types
			foreach($keywords as $index => $value)
			{
				if(isset($where)) //if year and/or category field entered, add to search criteria
				{
					array_unshift($criteria, "%$value%"); //put keyword at start of criteria array
					
					$q = 'SELECT DocumentId FROM documents WHERE DocName LIKE ? AND '.implode(" AND ",$where);
					$stmt = mysqli_prepare($link, $q);
					mysqli_stmt_bind_param($stmt, $datatype, ...$criteria);
					mysqli_stmt_execute($stmt);
					$r = mysqli_stmt_get_result($stmt);
					if(mysqli_num_rows($r) > 0) //did the search return any results?
					{
						while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
						{
							$alldocids[] = $row['DocumentId']; //put all found document ids into array
						}
					}
					array_shift($criteria); // remove the processed word from the search criteria ready for the next loop
				}
				else //if year and category not set only search doc name
				{
					$wildvalue = "%$value%";
					$q = 'SELECT DocumentId FROM documents WHERE DocName LIKE ?';
					$stmt = mysqli_prepare($link, $q);
					mysqli_stmt_bind_param($stmt, "s", $wildvalue);
					mysqli_stmt_execute($stmt);
					$r = mysqli_stmt_get_result($stmt);
					if(mysqli_num_rows($r) > 0) //did the search return any results?
					{
						while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
						{
							$alldocids[] = $row['DocumentId']; //put all found document ids into array
						}
					}
				}
			}	
		}
		if(!isset($alldocids)) //if alldocids not set, nothing found
		{
			echo "<br>No results found<br><br>";
		}
		else
		{
			array_unique($alldocids); //remove duplicate doc ids from array
			// category translation array
			$activity = array("cm" => "committees", "aw" => "awards", "tm" => "teams", "em" => "employees", 
			"vl" => "volunteers", "mb" => "members", "gn" => "general", "ac" => "accolade", "in" => "incident");
			$docids = "";
			$docids = implode(", ",$alldocids);// concatenate all docids into a string
			$q = "SELECT * FROM documents WHERE DocumentId IN ($docids)";
			$r = mysqli_query($link, $q);
			echo '<table style="width:100%"><tr><th>Document (download)</th><th>Created</th>
			<th>Category</th><th>View</th></tr>';
			while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
			{
				$row['LastSaved'] = substr($row['LastSaved'],0,4);
				$row['Activity'] = $activity[$row['Activity']];
				$yrid = $row['YearId'];
				$qy = "SELECT YearText FROM years WHERE YearId = $yrid";
				$ry = mysqli_query($link, $qy);
				$yrtext = mysqli_fetch_array($ry,MYSQLI_ASSOC);
				echo '<tr><td><a href="downloadfile.php?did='.$row['DocumentId'].'">'.$row['DocName'].'</a></td><td>'.$yrtext['YearText'].'</td>
				<td>'.$row['Activity'].'</td><td>';
				if(in_array(strtolower($row['FileType']),['jpg','jpeg','png','pdf']))
				{
					echo '<button type="button" onclick = "openView('.$row['DocumentId'].')">View</button>';
				}
				echo '</td></tr>';
			}
			echo '</table>';
		}
	}
	else
	{
		echo '<br>Please enter some search criteria.<br><br>';
	}
}
?>
</td><td style="border:0px; background-color:white;" id="viewercell">
<div style="position: sticky; top: 0; width: 100%; height: 65vh; overflow: hidden;">
<iframe id="docviewer" style="width:100%; height:100%; border:none; overflow: auto;">
</iframe></div>
</td></table>

<?php
include('pbaincludes/pbafooter.html');
?>
<script>
	function openView(docid){
		document.getElementById('docviewer').src = 
		'viewfile.php?did=' + docid;
	}
		
	

</script>
</body></html>

