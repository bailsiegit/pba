<?php
//Rev 1 19/11/2025
//this page is for users to register on the pba database
//registered users must contact the administrator to be given permission to access any data
//there are 4 levels of access

$page_title = "Register";
include("pbaincludes/pbaheader.html");

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	require('../connecttopba.php');
	$pwerrors = 0;
	
	$pwerrors = empty($_POST['first_name']) ? $pwerrors + 1 : $pwerrors;
	$fn = mysqli_real_escape_string($link,trim($_POST['first_name']));
	$pwerrors = empty($_POST['last_name']) ? $pwerrors + 1 : $pwerrors;
	$ln = mysqli_real_escape_string($link,trim($_POST['last_name']));
	$pwerrors = empty($_POST['email']) ? $pwerrors + 1 : $pwerrors;
	$e = mysqli_real_escape_string($link,trim($_POST['email']));
	$pwerrors = !empty($_POST['pass1']) ? $pwerrors : $pwerrors + 1;
	$pwerrors = !empty($_POST['pass2']) ? $pwerrors : $pwerrors + 1;
	$pwerrors = $_POST['pass1'] != $_POST['pass2'] ? $pwerrors + 1 : $pwerrors;
	$p = mysqli_real_escape_string($link, trim($_POST['pass1']));
	$pwerrors = strlen($p) > 9 ? $pwerrors : $pwerrors + 1; //ensure new password is at least 10 characters
	$pwerrors = preg_match('/[A-Z]/', $p) ?  $pwerrors : $pwerrors + 1; //ensure pw has capital letter`
	$pwerrors = preg_match('/[a-z]/', $p) ?  $pwerrors : $pwerrors + 1; //ensure pw has lowercaseletter
	$pwerrors = preg_match('/[0-9]/', $p) ?  $pwerrors : $pwerrors + 1; //ensure pw has number
	$pwerrors = preg_match('/[\W_]/', $p) ?  $pwerrors : $pwerrors + 1; //ensure pw has special character

	
	if($pwerrors > 0)
	{
		echo '<p style="color:red;">Please ensure all fields are completed accurately.</p>';
	}
	else
	{
	
		# is user email already in the database users table?
	
		//$q = "SELECT userid FROM pbausers WHERE email = '$e'";
		$q = "SELECT userid FROM pbausers WHERE email = ?";
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "s", $e);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		//$r = mysqli_query($link, $q);
		$emm = mysqli_num_rows($r);
		if(mysqli_num_rows($r) > 0)
		{
			echo '<span style="color:red">Email address already registered.</span> <a href="pbalogin.php">Login here.<a/>';
		}
		else
		{
			# store new user in the database
	
			$q = "INSERT INTO pbausers (firstname, lastname, email, password, registered) 
				VALUES (?, ?, ?, SHA2(?,256), NOW())";
			$r = mysqli_prepare($link, $q);
			mysqli_stmt_bind_param($r, "ssss", $fn, $ln, $e, $p);
			mysqli_stmt_execute($r);
			if($r)
			{
				echo '<h1>Registered!</h1>
				<p>You are now registered.</p>
				<p>Contact your administrator to get access</p>
				<p><a href="pbalogin.php">Login</a></p>';
			}
			mysqli_close($link);
			include('pbaincludes/pbafooter.html"');
			echo '</body>';
			echo '</html>';
			exit();
		}
	}
}
?>
	
<h1>Register</h1>
<form action="pbaregister.php" method="POST">
<p>
First Name: <input type="text" name="first_name"
	value="<?php if(isset($_POST['first_name']))
		echo $_POST['first_name'];?>">
Last Name: <input type="text" name="last_name"
	value="<?php if(isset($_POST['last_name']))
		echo $_POST['last_name'];?>">
</p> <p>
Email Address: <input type="email" name="email"
	value="<?php if(isset($_POST['email']))
		echo $_POST['email'];?>">
</p> <p>
Password: <input type="password" name="pass1">
Confirm Password: <input type="password" name="pass2">
</p> 
<div style="font-size:0.7em; color:red">Password needs to contain uppercase and lowercase letter, number and special character and be at least 10 characters.</div>
<p>
<input type="submit" value="Register"> </p>
</form>

<?php
include('pbaincludes/pbafooter.html');
?>

<script>
//this is a test script file
</script>
</body></html>