<?php
//Rev 1 19/11/2025
//this page is used to find documents across all categories
//keywords are entered and searched against the document titles
//a list of matching files is displayed and hyperlinked for download
session_start();
//check that user is logged in and has not been idle for too long
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();
	load(); //redirect to login page
}
$page_title = 'Documents';
include('pbaincludes/pbaheader.html');

$_SESSION['timeoutstart'] = time(); //reset login timeout marker

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
Keyword/s: <input size="60" type="text" name="keywords">
Document Year: <input type="number" name="searchyear" min="1963" max="2050">
<input type="submit" name="searchbutton" value="Search">
</form>
<?php
include('pbaincludes/pbadocsmenu.php');
?>
<hr>

<?php
if(isset($_POST['searchbutton']))
{
	$nullqueries = 0; //check count for empty queries (not used as yet)
	require("../connecttopba.php"); //connect to database
	// sanitise inputs
	$kw = mysqli_real_escape_string($link,trim($_POST['keywords']));
	// split keywords into array of separate words
	$word = strtok($kw, " ");
	//$keywords = array();
	while($word !== false)
	{
		$keywords[] = $word;
		$word = strtok(" ");
	}
	// if no search criteria entered, abandon Search
	if(isset($keywords))
	{
		// search query
		// category translation array
		$activity = array("cm" => "committees", "aw" => "awards", "tm" => "teams", "em" => "employees", 
		"vl" => "volunteers", "mb" => "members", "gn" => "general");
		// for each keyword find matching records
		$noresults = 0;
		foreach($keywords as $index => $value)
		{
			$wildvalue = "%$value%";
			//$q = 'SELECT DocumentId FROM documents WHERE DocName LIKE "%'.$value.'%"';
			$q = 'SELECT DocumentId FROM documents WHERE DocName LIKE ?';
			$stmt = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($stmt, "s", $wildvalue);
			mysqli_stmt_execute($stmt);
			$r = mysqli_stmt_get_result($stmt);
			//$r = mysqli_query($link, $q);
			if(mysqli_num_rows($r) > 0) //did the search return any results?
			{
				while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
				{
					$alldocids[] = $row['DocumentId']; //put all found document ids into array
				}
			}
			else
			{
			$noresults = $noresults + 1;
			}
		}	
		//mysqli_close($link);
		if($noresults == count($keywords)) //if noresults equals word count, nothing was found
		{
			echo "<br>No results found<br><br>";
		}
		else
		{
			array_unique($alldocids); //remove duplicate doc ids from array
			$docids = "";
			foreach($alldocids as $index => $value)
			{
				$docids = $docids . "$value, "; // concatenate all docids into a string
			}
			$docids = substr($docids,0,strlen($docids)-2); // trim off the last comma and space
			$q = "SELECT * FROM documents WHERE DocumentId IN ($docids)";
			$r = mysqli_query($link, $q);
			echo '<table style="width:80%"><tr><th style="width:40%">Document</th><th style="width:10%">Year Created</th>
			<th style="width:15%">Category</th><th style="width:10%">Year Loaded</th></tr>';
			while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
			{
				$row['LastSaved'] = substr($row['LastSaved'],0,4);
				$row['Activity'] = $activity[$row['Activity']];
				$yrid = $row['YearId'];
				$qy = "SELECT YearText FROM years WHERE YearId = $yrid";
				$ry = mysqli_query($link, $qy);
				$yrtext = mysqli_fetch_array($ry,MYSQLI_ASSOC);
				echo '<tr><td><a href="downloadfile.php?did='.$row['DocumentId'].'">'.$row['DocName'].'</a></td><td>'.$yrtext['YearText'].'</td>
				<td>'.$row['Activity'].'</td><td>'.$row['LastSaved'].'</td></tr>';
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

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>