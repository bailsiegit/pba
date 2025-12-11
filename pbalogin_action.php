<?php
//Rev 1 19/11/2025
//this page provides tools to complete the login process
if($_SERVER['REQUEST_METHOD'] == 'POST') #confirm button click
{
	require('../connecttopba.php');
	require('pbalogin_tools.php');
	
	#list function places returned values in the listed fields
	list($check, $data) = 
		validate($link, $_POST['email'], $_POST['pass']);
	
	if($check) # true false passed from validate function in login_tools
	{
		session_start();
		#make user details available across the site
		$_SESSION['userid'] = $data['userid'];
		$_SESSION['first_name'] = $data['firstname'];
		$_SESSION['last_name'] = $data['lastname'];
		$_SESSION['accesslevel'] = $data['accesslevel'];
		$_SESSION['timeoutstart'] = time();
		$_SESSION['timeoutlimit'] = 900;
		// if this is first login after pw reset, redirect to change pw
		if($data['pwreset'] > 0)
		{
			load('pbachangepassword.php');
		}
		else // if not from pw reset, load home page
		{
			load('pbahome.php');
		}
	}
	else
	{$errors = $data;}

mysqli_close($link);

}

include('pbalogin.php');