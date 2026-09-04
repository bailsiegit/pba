<?php
// Rev 3 4/9/2026 - added pink highlight for current menu selection
# this provides links to find other data about the person
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbaperson.php") ? 'style="background:pink;"' : '' ; echo 'href="pbaperson.php?pid='.$pid.'">Details</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbapersonmemberships.php") ? 'style="background:pink;"' : '' ; echo 'href="pbapersonmemberships.php?pid='.$pid.'">Memberships</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbapersonteams.php") ? 'style="background:pink;"' : '' ; echo 'href="pbapersonteams.php?pid='.$pid.'">Teams</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbapersoncommittees.php") ? 'style="background:pink;"' : '' ; echo 'href="pbapersoncommittees.php?pid='.$pid.'">Committees</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbapersonawards.php") ? 'style="background:pink;"' : '' ; echo 'href="pbapersonawards.php?pid='.$pid.'">Awards</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbapersonaccolades.php") ? 'style="background:pink;"' : '' ; echo 'href="pbapersonaccolades.php?pid='.$pid.'">Accolades</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbapersonvoluntary.php") ? 'style="background:pink;"' : '' ; echo 'href="pbapersonvoluntary.php?pid='.$pid.'">Volunteer</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbapersonemployee.php") ? 'style="background:pink;"' : '' ; echo 'href="pbapersonemployee.php?pid='.$pid.'">Employee</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbapersonincident.php") ? 'style="background:pink;"' : '' ; echo 'href="pbapersonincident.php?pid='.$pid.'">Incident</a>';
if($_SESSION['accesslevel'] < 3) // disable option by user access
{
	echo '<a class="submenudisabled">Add Activity for '.$person['FirstName'].'</a>';
}
else
{
	echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbapersonaddactivity.php") ? 'style="background:pink;"' : '' ; echo 'href="pbapersonaddactivity.php?pid='.$pid.'">Add Activity for '.$person['FirstName'].'</a>';
}
?>