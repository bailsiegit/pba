<?php

function load($page = 'pbalogin.php') #login.php is default if no file passed to function
{
	$url = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
	
	$url = rtrim($url, '/\\'); //remove any slashes from the end of the url
	$url .= '/'.$page; // add a / and the required page name to the url
	
	header("Location: $url"); //use header location to redirect to url
	exit();
	
}

function validate($link, $email = '', $pwd = '') #empty string defaults
{
	$errors = array(); // establish array to store errors
	
	$finduserstmt = $link->prepare("SELECT userid, firstname, lastname, accesslevel, badlogins, pwreset  
		FROM pbausers WHERE email = ? AND password = SHA2(?, 256) AND badlogins < 3");
	$finduserstmt->bind_param("ss", $e, $p);
	$zerobadloginstmt = $link->prepare("UPDATE pbausers SET badlogins = 0 WHERE userid = ?"); //reset bad login counter after good login
	$zerobadloginstmt->bind_param("i", $uid);
	$findbaduidstmt = $link->prepare("SELECT userid, badlogins FROM pbausers WHERE email = ?"); //find userid if pword wrong
	$findbaduidstmt->bind_param("s", $e);
	$addbadloginstmt = $link->prepare("UPDATE pbausers SET badlogins = ? WHERE userid = ?"); //increment bad login counter
	$addbadloginstmt->bind_param("ii", $badlogs, $uid);
	
	if(empty($email)) // check email has been entered
	{$errors[] = 'Enter your email address.';} //if email not entered, add error to array
	else
	{$e = mysqli_real_escape_string($link, trim($email));} //clean email input

	if(empty($pwd)) //check if password has been entered
	{$errors[] = 'Enter your password.';} // if password not entered, add error to array
	else
	{$p = mysqli_real_escape_string($link, trim($pwd));} // clean password

	if(empty($errors)) # if no errors validate against user table
	{			
		$finduserstmt->execute();
		$r = mysqli_stmt_get_result($finduserstmt);
		
		if(mysqli_num_rows($r) == 1) // if there is a matching row then pass user data back
		{
			$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
			$uid = $row['userid'];
			$zerobadloginstmt->execute(); // reset bad login counter after good login
			$zerobadloginstmt->close();
			$finduserstmt->close();
			return array(true, $row); #true to confirm valid login
		}
		else
		{
			$findbaduidstmt->execute(); // get userid for entered email
			$r = mysqli_stmt_get_result($findbaduidstmt);
			if(mysqli_num_rows($r) == 1) // check if email is in user list
			{
				$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
				$uid = $row['userid'];
				$badlogs = $row['badlogins'] + 1; // increment bad login counter
				$addbadloginstmt->execute(); //write increment to table
				$addbadloginstmt->close();
			}
			$findbaduidstmt->close();
			$finduserstmt->close();
			$errors[] = 'Email address and password not found.';} //user email and password combo not found
	
	}
	
	return array(false, $errors); #false as login match has failed
}