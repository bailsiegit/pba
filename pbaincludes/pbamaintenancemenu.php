<?php
//Rev 3 - 4/9/2026 - added pink to highlight current menu selection
//this displayed by an include statement in all the maintenance pages
//it provides a consistent menu to switch between the pages in the maintenance group
echo '<div style="margin-bottom:2px;">';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbateamentries.php") ? 'style="background:pink;"' : '' ; 
echo 'href="pbateamentries.php">Teams</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbaleaguestable.php") ? 'style="background:pink;"' : '' ; 
echo 'href="pbaleaguestable.php">Leagues</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbaseasonstable.php") ? 'style="background:pink;"' : '' ; 
echo 'href="pbaseasonstable.php">Seasons</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbagradestable.php") ? 'style="background:pink;"' : '' ; 
echo 'href="pbagradestable.php">Grades</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbagenderstable.php") ? 'style="background:pink;"' : '' ; 
echo 'href="pbagenderstable.php">Gender</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbadivisionstable.php") ? 'style="background:pink;"' : '' ; 
echo 'href="pbadivisionstable.php">Divisions</a></div>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbateamnamestable.php") ? 'style="background:pink;"' : '' ; 
echo 'href="pbateamnamestable.php">Team Names</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbamembershipstable.php") ? 'style="background:pink;"' : '' ; 
echo 'href="pbamembershipstable.php">Member Types</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbacommitteestable.php") ? 'style="background:pink;"' : '' ; 
echo 'href="pbacommitteestable.php">Committees</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbaawardstable.php") ? 'style="background:pink;"' : '' ; 
echo 'href="pbaawardstable.php">Awards</a>';
echo '<a class="submenu" '; echo (basename($_SERVER['PHP_SELF']) == "pbaemailgroups.php") ? 'style="background:pink;"' : '' ; 
echo 'href="pbaemailgroups.php">Email Groups</a>';
?>