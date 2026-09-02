<?php
//Rev 2 - 2/9/2026 - added filter by access level
//this displayed by an include statement in all the documents pages
//it provides a consistent menu to switch between the pages in the documents group
echo '<a class="submenu" href="pbadocsearch.php">Search</a>';
if($_SESSION['accesslevel'] > 2)
{
	echo '<a class="submenu" href="pbaloaddoc.php">Upload Document</a>';
}
?>