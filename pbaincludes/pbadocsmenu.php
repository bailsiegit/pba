<?php
//Rev 3 - added pink to highlight current menu selection
//this displayed by an include statement in all the documents pages
//it provides a consistent menu to switch between the pages in the documents group
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbadocsearch.php") ? 'style="background:pink;"' : '' ; echo 'href="pbadocsearch.php">Search</a>';
if($_SESSION['accesslevel'] < 3)//disable by user access
{
	echo '<a class="submenudisabled">Upload Document</a>';
}
else
{
	echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbaloaddoc.php") ? 'style="background:pink;"' : '' ; echo 'href="pbaloaddoc.php">Upload Document</a>';
}
?>