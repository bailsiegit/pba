<?php
//Rev 5 6/9/2026 - added report by year feature
//this page is part of the reports group
//it is called from the reports page after the selection of a person
//a pdf of all activities for the person is generated and downloaded
require('fpdf/fpdf.php');
class PDF extends FPDF
{
    function Header()
    {
        // Select Arial bold 15
        //$this->SetFont('Arial', 'B', 15);
        // Move to the right
        //$this->Cell(30);
		//$this->SetFont('Arial', 'B', 16);
		//$this->Cell(30, 10, "Type", 1);
		//$this->Cell(45, 10, "First Name", 1);
		//$this->Cell(45, 10, "Last Name", 1);
		//$this->Cell(30, 10, "Signature", 'TLB', 0, 'R'); //extra parameter is to start new line
		//$this->SetFont('Arial', '', 10);
		//$this->Cell(30, 10, "(or proxy name)", 'TRB', 1, 'L');
	}
	
	function Footer()
    {
        // Go to 1.5 cm from bottom
        $this->SetY(-15);
        // Select Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Print centered page number
        $this->Cell(0, 10, 'Page '.$this->PageNo().' of {nb}', 0, 0, 'C');
    }

}

//build report by activity
if(isset($_POST['dlactivityreport']))
{
	//get members name for report header
	$formperson = htmlentities($_POST['personid']);
	$q = "SELECT FirstName, LastName FROM members WHERE MemberID = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formperson);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	$person = mysqli_fetch_array($r, MYSQLI_ASSOC);
	mysqli_stmt_close($stmt);
	mysqli_close($link);

	$pdf = new PDF(); //create new pdf doc
	$pdf->AliasNbPages();
	$pdf->AddPage(); //add the first page
	$pdf->SetLeftMargin(30);
		
	//header section
		
	//insert logo
	$pdf->Image('images/Redbacks.png', 10, 10, -300, 14);
    // unframed title

	$pdf->SetFont('Arial', '', 16);
	//$pdf->Cell(10, 10, '');
    $pdf->Cell(120, 8, 'PBA Member Activity Report', 0, 2, 'C');
	$pdf->Cell(120, 8, $person['FirstName'].' '.$person['LastName'], 0, 0, 'C');
    // Line break
	$pdf->SetLeftMargin(10);
    $pdf->Ln(20);
		
	//memberships section

	$pdf->SetFont('Arial', '', 14);
	$pdf->Cell(180, 10, "Memberships", "TB", 1);
	$pdf->Ln(5);
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell(15, 10, 'Year', 1, 0, 'C');
	$pdf->Cell(30, 10, 'Member Type', 1, 1, 'C');
		

	$pdf->SetFont('Arial', '', 10);
	
	$q = "
	SELECT y.YearText, mt.Type 
	FROM memberships ms 
	JOIN years y ON ms.YearId = y.YearId
	JOIN membertypes mt ON mt.MemBTypeId = ms.Mtype 
	WHERE MembId = ? 
	ORDER BY y.YearText DESC";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formperson);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);

	if(mysqli_num_rows($r) > 0)
	{
		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
		{
			$yr = $row['YearText'];
			$tp = $row['Type'];
			$pdf->Cell(15, 7, "$yr", 1);
			$pdf->Cell(30, 7, "$tp", 1, 1); //extra parameter is to start new line
		}
	}
	else
	{
		$pdf->Cell(45, 7, 'No memberships found.', 1, 1);
	}
	mysqli_stmt_close($stmt);
	mysqli_close($link);
	
	//Teams section
		
    $pdf->Ln(10);
	$pdf->SetFont('Arial', '', 14);
	$pdf->Cell(180, 10, "Teams", "TB", 1);
	$pdf->Ln(5);
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell(15, 10, 'Year', 1, 0, 'C');
	$pdf->Cell(70, 10, 'Team', 1, 0, 'C');
	$pdf->Cell(40, 10, 'Role', 1, 1, 'C');
		

	$pdf->SetFont('Arial', '', 10);
		
	$q = "
	SELECT tm.Role, tm.GamesPlayed, y.YearText, t.TeamName 
	FROM teammembers tm
	JOIN years y ON tm.YearId = y.YearId
	JOIN teams t ON tm.TeamId = t.TeamId 
	WHERE MembId = ? 
	ORDER BY y.YearText DESC";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formperson);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);

	if(mysqli_num_rows($r) > 0)
	{
		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
		{
			$yr = $row['YearText'];
			$tm = $row['TeamName'];
			$tr = $row['Role'];
			$pdf->Cell(15, 7, "$yr", 1);
			$pdf->Cell(70, 7, "$tm", 1);
			$pdf->Cell(40, 7, "$tr", 1, 1); //extra parameter is to start new line
		}
	}
	else
	{
		$pdf->Cell(125, 7, 'No team roles found.', 1, 1);
	}		
	mysqli_stmt_close($stmt);
	mysqli_close($link);
		
	//Committees section
		
    $pdf->Ln(10);
	$pdf->SetFont('Arial', '', 14);
	$pdf->Cell(180, 10, "Committees", "TB", 1);
	$pdf->Ln(5);
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell(15, 10, 'Year', 1, 0, 'C');
	$pdf->Cell(70, 10, 'Committee', 1, 0, 'C');
	$pdf->Cell(40, 10, 'Role', 1, 1, 'C');
		

	$pdf->SetFont('Arial', '', 10);
		
	$q = "
	SELECT cm.Role, y.YearText, y.YearId, c.CommitteeName 
	FROM committeememb cm
	JOIN years y ON cm.YearId = y.YearId
	JOIN committees c ON cm.CommId = c.CommitteeId 
	WHERE MembId = ? 
	ORDER BY y.YearText DESC";		
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formperson);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	
	if(mysqli_num_rows($r) > 0)
	{
		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
		{
			$yr = $row['YearText'];
			$cn = $row['CommitteeName'];
			$cr = $row['Role'];
			$pdf->Cell(15, 7, "$yr", 1);
			$pdf->Cell(70, 7, "$cn", 1);
			$pdf->Cell(40, 7, "$cr", 1, 1); //extra parameter is to start new line
		}
	}
	else
	{
		$pdf->Cell(125, 7, 'No committee roles found.', 1, 1);
	}		
	mysqli_stmt_close($stmt);
	mysqli_close($link);
		
	//Volunteer section
		
    $pdf->Ln(10);
	$pdf->SetFont('Arial', '', 14);
	$pdf->Cell(180, 10, "Volunteer", "TB", 1);
	$pdf->Ln(5);
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell(15, 10, 'Year', 1, 0, 'C');
	$pdf->Cell(50, 10, 'Role', 1, 0, 'C');
	$pdf->Cell(50, 10, 'Details', 1, 1, 'C');
		

	$pdf->SetFont('Arial', '', 10);
		
	$q = "
	SELECT v.Role, v.Details, y.YearText 
	FROM volunteers v
	JOIN years y ON v.YearId = y.YearId
	WHERE MembId = ? 
	ORDER BY y.YearText DESC";	
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formperson);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	if(mysqli_num_rows($r) > 0)
	{
		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
		{
			$yr = $row['YearText'];
			$vr = $row['Role'];
			$dr = $row['Details'];
			$pdf->Cell(15, 7, "$yr", 1);
			$pdf->Cell(50, 7, "$vr", 1);
			$pdf->Cell(50, 7, "$dr", 1, 1); //extra parameter is to start new line
		}
	}
	else
	{
		$pdf->Cell(115, 7, 'No volunteer roles found.', 1, 1);
	}
	mysqli_stmt_close($stmt);
	mysqli_close($link);
		
	//Awards section
		
    $pdf->Ln(10);
	$pdf->SetFont('Arial', '', 14);
	$pdf->Cell(180, 10, "Awards", "TB", 1);
	$pdf->Ln(5);
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell(13, 10, 'Year', 1, 0, 'C');
	$pdf->Cell(70, 10, 'Award', 1, 0, 'C');
	$pdf->Cell(70, 10, 'Team', 1, 0, 'C');
	$pdf->Cell(40, 10, 'Comment', 1, 1, 'C');
		

	$pdf->SetFont('Arial', '', 10);
		
	$q = "SELECT 
	aw.Comments, 
	a.AwardName, 
	y.YearText, 
	t.TeamName
	FROM awardwinners aw
	JOIN awards a ON aw.AwardId = a.AwardID 
	JOIN years y ON aw.YearId = y.YearId
	JOIN teams t ON aw.TeamId = t.TeamId
	WHERE MembId = ? 
	ORDER BY y.YearText DESC";	
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formperson);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	if(mysqli_num_rows($r) > 0)
	{
		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
		{
			$yr = $row['YearText'];
			$an = $row['AwardName'];
			$tn = $row['TeamName'];
			$ac = $row['Comments'];
			$rh = 7;
			$pdf->Cell(13, $rh, "$yr", 1);
			$pdf->Cell(70, $rh, "$an", 1);
			$pdf->Cell(70, $rh, "$tn", 1);
			$pdf->Cell(40, $rh, "$ac", 1, 1); //extra parameter is to start new line
		}
	}
	else
	{
		$pdf->Cell(125, 7, 'No awards found.', 1, 1);
	}
	mysqli_stmt_close($stmt);
	mysqli_close($link);
		
	//Accolades section
	
    $pdf->Ln(10);
	$pdf->SetFont('Arial', '', 14);
	$pdf->Cell(180, 10, "Accolades", "TB", 1);
	$pdf->Ln(5);
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell(15, 10, 'Year', 1, 0, 'C');
	$pdf->Cell(40, 10, 'Accolade', 1, 0, 'C');
	$pdf->Cell(70, 10, 'Details', 1, 1, 'C');
		

	$pdf->SetFont('Arial', '', 10);
		
	$q = "SELECT 
	ac.Accolade,
	ac.Details,
	yr.YearText 
	FROM Accolades ac 
	INNER JOIN years yr 
	ON ac.YearId = yr.YearId
	WHERE ac.MembId = ? 
	ORDER BY yr.YearText DESC";	
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formperson);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
		
	if(mysqli_num_rows($r) > 0)
	{
		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
		{
			$yr = $row['YearText'];
			$acc = $row['Accolade'];
			$acd = $row['Details'];
			$pdf->Cell(15, 7, "$yr", 1);
			$pdf->Cell(40, 7, "$acc", 1);
			$pdf->Cell(70, 7, "$acd", 1, 1); //extra parameter is to start new line
		}
	}
	else
	{
		$pdf->Cell(125, 7, 'No accolades found.', 1, 1);
	}
	mysqli_stmt_close($stmt);
	mysqli_close($link);
		
	//Employee section
		
    $pdf->Ln(10);
	$pdf->SetFont('Arial', '', 14);
	$pdf->Cell(180, 10, "Employee", "TB", 1);
	$pdf->Ln(5);
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell(15, 10, 'Year', 1, 0, 'C');
	$pdf->Cell(50, 10, 'Role', 1, 0, 'C');
	$pdf->Cell(50, 10, 'Details', 1, 1, 'C');
		

	$pdf->SetFont('Arial', '', 10);
		
	$q = "
	SELECT e.Role, e.Details, y.YearText 
	FROM employees e
	JOIN years y ON e.YearId = y.YearId
	WHERE MembId = ? 
	ORDER BY y.YearText DESC";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formperson);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
		
	if(mysqli_num_rows($r) > 0)
	{
		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
		{
			$yr = $row['YearText'];
			$er = $row['Role'];
			$ed = $row['Details'];
			$pdf->Cell(15, 7, "$yr", 1);
			$pdf->Cell(50, 7, "$er", 1);
			$pdf->Cell(50, 7, "$ed", 1, 1); //extra parameter is to start new line
		}
	}
	else
	{
		$pdf->Cell(115, 7, 'No employee roles found.', 1, 1);
	}
	mysqli_stmt_close($stmt);
	mysqli_close($link);
		
	//Incidents section
		
    $pdf->Ln(10);
	$pdf->SetFont('Arial', '', 14);
	$pdf->Cell(180, 10, "Incidents", "TB", 1);
	$pdf->Ln(5);
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell(25, 10, 'Date', 1, 0, 'C');
	$pdf->Cell(75, 10, 'Incident', 1, 0, 'C');
	$pdf->Cell(80, 10, 'Outcome', 1, 1, 'C');
		

	$pdf->SetFont('Arial', '', 10);
		
	$q = "SELECT 
	IncidentDate,
	IncidentDetails,
	TribunalResult
	FROM incident 
	WHERE MembId = ? 
	ORDER BY IncidentDate DESC";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formperson);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
		
	if(mysqli_num_rows($r) > 0)
	{
		while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
		{
			$dt = date_format(date_create($row['IncidentDate']), "j/m/Y") ;
			$in = $row['IncidentDetails'];
			$tr = $row['TribunalResult'];
			$pdf->Cell(25, 7, "$dt", 1);
			$pdf->Cell(75, 7, "$in", 1);
			$pdf->Cell(80, 7, "$tr", 1, 1); //extra parameter is to start new line
		}
	}
	else
	{
		$pdf->Cell(180, 7, 'No incidents found.', 1, 1);
	}
	mysqli_stmt_close($stmt);
	mysqli_close($link);		

	$pdf->Output('D', 'pbaactivityreport.pdf');
}


//build report by year
if(isset($_POST['dlyearactivityreport']))
{
	//get members name for report header
	$formperson = htmlentities($_POST['personid']);
	$q = "SELECT FirstName, LastName FROM members WHERE MemberID = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $formperson);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	$person = mysqli_fetch_array($r, MYSQLI_ASSOC);
	mysqli_stmt_close($stmt);
	mysqli_close($link);
	
	//get all the data into an array
	$formperson = htmlentities($_POST['personid']);
	$q = "
	SELECT 'T' AS Type, t.TeamName, tm.Role, y.YearText
	FROM teammembers tm
	JOIN teams t ON tm.TeamId = t.TeamId
	JOIN years y ON tm.YearId = y.YearId
	WHERE tm.MembId = ?
	UNION ALL
	SELECT 'C' AS Type, c.CommitteeName, cm.Role, y.YearText
	FROM committeememb cm
	JOIN committees c ON cm.CommId = CommitteeId
	JOIN years y ON cm.YearId = y.YearId
	WHERE cm.MembId = ?
	UNION ALL
	SELECT 'M' AS Type, CONCAT(mt.Type, ' Member'), IF(ms.start = '0000-00-00', '', ms.start), y.YearText
	FROM memberships ms
	JOIN membertypes mt ON ms.Mtype = mt.MemBTypeId
	JOIN years y ON ms.YearId = y.YearId
	WHERE ms.MembId = ?
	UNION ALL
	SELECT 'V' AS Type, v.Role, v.Details, y.YearText
	FROM volunteers v
	JOIN years y ON v.YearId = y.YearId
	WHERE v.MembId = ?
	UNION ALL
	SELECT  'Aw' AS Type,IF(t.TeamName = 'Individual', a.AwardName, CONCAT(a.AwardName, ' ', t.TeamName)) AS Prize, aw.Comments, y.YearText
	FROM awardwinners aw
	JOIN awards a ON aw.AwardId = a.AwardID
	JOIN teams t ON aw.TeamId = t.TeamId
	JOIN years y ON aw.YearId = y.YearId
	WHERE aw.MembId = ?
	UNION ALL
	SELECT 'Ac' AS Type, a.Accolade, a.Details, y.YearText
	FROM accolades a
	JOIN years y ON a.YearId = y.YearId
	WHERE a.MembId = ?
	UNION ALL
	SELECT 'E' AS Type, e.Role, e.Details, y.YearText
	FROM employees e
	JOIN years y ON e.YearId = y.YearId
	WHERE e.MembId = ?
	UNION ALL
	SELECT 'I' AS Type, i.IncidentDetails, i.TribunalResult, y.YearText
	FROM incident i
	JOIN years y ON i.YearId = y.YearId
	WHERE i.MembId = ?
	ORDER BY YearText, Type";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "iiiiiiii", $formperson, $formperson, $formperson, $formperson, $formperson, $formperson, $formperson, $formperson);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);

	while($record = mysqli_fetch_array($r, MYSQLI_ASSOC))
		{
			// for each member, add a row to the combo box
			$allrows[] = $record;
		}
	$numrows = mysqli_num_rows($r);
	mysqli_stmt_close($stmt);
	mysqli_close($link);

	//find all the years the person has activities
	$ycolumn = array_column($allrows, "YearText");
	$allyears = array_unique($ycolumn);//unique years in data
	$i = 0;
	$j = 0;
	
	$pdf = new PDF(); //create new pdf doc
	$pdf->AliasNbPages();
	$pdf->AddPage(); //add the first page
	$pdf->SetLeftMargin(30);
		
	//header section
		
	//insert logo
	$pdf->Image('images/Redbacks.png', 10, 10, -300, 14);
       // unframed title

	$pdf->SetFont('Arial', '', 16);
	//$pdf->Cell(10, 10, '');
    $pdf->Cell(120, 8, 'PBA Member Activity Report', 0, 2, 'C');
	$pdf->Cell(120, 8, $person['FirstName'].' '.$person['LastName'], 0, 0, 'C');
	// Line break
	$pdf->SetLeftMargin(10);
    $pdf->Ln(20);
	
	foreach($allyears as $y) //cycle through years
	{
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(100, 7, $y, 0, 1);
		for($i = 0; $i < $numrows; $i++)//cycle through rows returned
		{
			if($allrows[$i]['YearText'] == $y) //only process rows for the current year
			{
				$pdf->SetFont('Arial', '', 8);
				$pdf->Cell(6, 7, $allrows[$i]['Type'], 1, 0);
				$pdf->Cell(90, 7, $allrows[$i]['TeamName'], 1, 0);
				$pdf->Cell(80, 7, $allrows[$i]['Role'], 1, 1);
			}
		}
		$pdf->Ln();
	}
	$pdf->Cell(150, 7, 'Legend:', 0, 1);
	$pdf->Cell(150, 7, 'T-Team, C-Committee, V-Volunteer, Aw-Award, Ac-Accolade, M-Membership, E-Employee, I-Incident', 0, 0);
$pdf->Output('D', 'pbaactivityreport.pdf');
}
?>