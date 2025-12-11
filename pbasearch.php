<?php
//Rev 1 19/11/2025
//this page is part of the people group
//this page uses wild cards to add to the characters provided 
//then provides a list of people and their DOB that match the criteria

session_start();
//check that user is logged in and has not been idle for too long
if(!isset($_SESSION['userid']) || time() - $_SESSION['timeoutstart'] > $_SESSION['timeoutlimit']) //check if user is logged in
{
	require('pbalogin_tools.php');
	session_unset();
	session_destroy();
	load(); //redirect to login page
}
$page_title = 'People';
include('pbaincludes/pbaheader.html');

$_SESSION['timeoutstart'] = time(); //reset login timeout marker

?>
<h2>People Search</h2>
<?php
# check the user has access to this page
if($_SESSION['accesslevel'] < 1)
{
	echo '<br><br>You do not have permission to access this page.';
	exit();
}
?>


<!-- search form -->
<form action="pbasearch.php" method = "POST"> 
First Name: <input type="text" name="first_name">
Last Name: <input type="text" name="last_name">
<input type="submit" name="searchbutton" value="Search">
<button><a style="text-decoration:none; color:black;" href="pbaaddperson.php">Add New Person</a></button>
</form>
<hr>

<?php
if(isset($_POST['searchbutton']))
{
	if(!empty($_POST['first_name']) || !empty($_POST['last_name'])) //has any criteria been entered
	{
		$nullqueries = 0; //check count for empty queries (not used as yet)
		require("../connecttopba.php"); //connect to database
		// sanitise inputs
		$fn = '%'.mysqli_real_escape_string($link,trim($_POST['first_name'])).'%'; //clean input plus add wild character
		$ln = '%'.mysqli_real_escape_string($link,trim($_POST['last_name'])).'%';
		
		//first search query
		$q = 'SELECT * FROM members WHERE FirstName LIKE ? AND LastName LIKE ? ORDER BY LastName';
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "ss", $fn, $ln);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		if(mysqli_num_rows($r) > 0) //did the search return any results?
		{
			// load table header for query results
			echo '<table width="80%"><tr><th width="20%">ID</th><th width="40%">Name</th><th width="40%">Birth Year</th></tr>';
			while($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
			{
				$row['Birthdate'] = ($row['Birthdate'] == "0000-00-00") ? "" : substr($row['Birthdate'],0,4);
				echo '<tr><td>'.$row['MemberID'].'</td>
				<td><a href="pbaperson.php?pid='.$row['MemberID'].'&sid='.$row['Salutation'].'">'.$row['FirstName'].' '.$row['LastName'].'</a></td>
				<td>'.$row['Birthdate'].'</td></tr>';
			}
			echo '</table>';
		}
		else
		{
			echo "No results found.";
		}
				
		mysqli_close($link);
	}
	else
	{
	echo '<span style="color:red">Please enter some search criteria.</span><br><br>';
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