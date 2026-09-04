<!--
Rev 2 4/9/2026 - added pink to show current menu selection
this is a submenu for all the activity pages
-->
<div style="padding-bottom:5px;">
<?php
# this provides links to the different activity types
echo '<a '; echo (basename($_SERVER['PHP_SELF']) == "pbaactivitymemberships.php") ? 'style="background:pink;"' : '' ; echo 'href="pbaactivitymemberships.php" class="submenu">Memberships</a>';
echo '<a '; echo (basename($_SERVER['PHP_SELF']) == "pbaactivityteams.php") ? 'style="background:pink;"' : '' ; echo 'href="pbaactivityteams.php" class="submenu">Teams</a>';
echo '<a '; echo (basename($_SERVER['PHP_SELF']) == "pbaactivitycommittees.php") ? 'style="background:pink;"' : '' ; echo 'href="pbaactivitycommittees.php" class="submenu">Committees</a>';
echo '<a '; echo (basename($_SERVER['PHP_SELF']) == "pbaactivityawards.php") ? 'style="background:pink;"' : '' ; echo 'href="pbaactivityawards.php" class="submenu">Awards</a>';
echo '<a '; echo (basename($_SERVER['PHP_SELF']) == "pbaactivityaccolades.php") ? 'style="background:pink;"' : '' ; echo 'href="pbaactivityaccolades.php" class="submenu">Accolades</a>';
echo '<a '; echo (basename($_SERVER['PHP_SELF']) == "pbaactivityvolunteers.php") ? 'style="background:pink;"' : '' ; echo 'href="pbaactivityvolunteers.php" class="submenu">Volunteers</a>';
echo '<a '; echo (basename($_SERVER['PHP_SELF']) == "pbaactivityemployee.php") ? 'style="background:pink;"' : '' ; echo 'href="pbaactivityemployee.php" class="submenu">Employees</a>';
echo '<a '; echo (basename($_SERVER['PHP_SELF']) == "pbaactivityincident.php") ? 'style="background:pink;"' : '' ; echo 'href="pbaactivityincident.php" class="submenu">Incident</a>';

?>
</div>