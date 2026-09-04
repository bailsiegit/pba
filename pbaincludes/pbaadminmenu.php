<?php
//Rev 3 - 4/9/2026 - added pink to highlight current menu selection
//this displayed by an include statement in all the documents pages
//it provides a consistent menu to switch between the pages in the documents group
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbachangepassword.php") ? 'style="background:pink;"' : '' ; echo 'href="pbachangepassword.php">Change Password</a>';
if($_SESSION['accesslevel'] < 3) //disable by user access
{
	echo '<a class="submenudisabled">Email Groups</a>';
}
else
{
	echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbaemails.php") ? 'style="background:pink;"' : '' ; echo 'href="pbaemails.php">Email Groups</a>';
}
if($_SESSION['accesslevel'] < 4)//disable by user access
{
	echo '<a class="submenudisabled">Change User Access</a>';
	echo '<a class="submenudisabled">Reset User Password</a>';
}
else
{
	echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbachangeaccess.php") ? 'style="background:pink;"' : '' ; echo 'href="pbachangeaccess.php">Change User Access</a>';
	echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbaresetpassword.php") ? 'style="background:pink;"' : '' ; echo 'href="pbaresetpassword.php">Reset User Password</a>';
}
?>