<?php
# this provides links to find other data about the person
echo '<a class="submenu" href="pbaperson.php?pid='.$pid.'">Details</a>';
echo '<a class="submenu" href="pbapersonmemberships.php?pid='.$pid.'">Memberships</a>';
echo '<a class="submenu" href="pbapersonteams.php?pid='.$pid.'">Teams</a>';
echo '<a class="submenu" href="pbapersoncommittees.php?pid='.$pid.'">Committees</a>';
echo '<a class="submenu" href="pbapersonawards.php?pid='.$pid.'">Awards</a>';
echo '<a class="submenu" href="pbapersonvoluntary.php?pid='.$pid.'">Volunteer</a>';
echo '<a class="submenu" href="pbapersonemployee.php?pid='.$pid.'">Employee</a>';
echo '<a class="submenu" href="pbapersonaddactivity.php?pid='.$pid.'">Add Activity for '.$person['FirstName'].'</a>';

?>