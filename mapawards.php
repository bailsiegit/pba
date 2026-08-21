<?php
require('../connecttopba.php');
$q = 'SELECT * FROM awardmaps';
$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);

$testaw = mysqli_fetch_array($r, MYSQLI_ASSOC);

$i = 0;
//echo '<pre>';
//print_r($testaw);
//echo '</pre>';

while($pbaawards = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	$winnerid = $pbaawards['AwardWinId'];
	$pbaaward = $pbaawards['AwardId'];
	$team = $pbaawards['TeamId'];

		$q2 = "UPDATE awardwinners SET TeamId = $team, AwardId = $pbaaward WHERE AwardWinId = $winnerid";
		$r2 = mysqli_query($link, $q2, MYSQLI_STORE_RESULT);
		if($r2)
		{
			$i = $i + 1;
			echo $winnerid.' done. '.$i.' records processed.<br>';
		}
		else
		{
			echo 'award id '.$winnerid.' failed.';
			exit();
		}
}
