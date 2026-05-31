<?php
//Rev 3 31/5/20026 - added incident and accolade section
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
	//if activity report button active
	if(isset($_POST['dlactivityreport']))
	{
		$formperson = htmlentities($_POST['personid']);
		//$q = "SELECT FirstName, LastName FROM members WHERE MemberID = $formperson";
		$q = "SELECT FirstName, LastName FROM members WHERE MemberID = ?";
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $formperson);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		
		
		//$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
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
		
		$q = "SELECT years.YearText, membertypes.Type FROM ((memberships 
		INNER JOIN years ON memberships.YearId = years.YearId)
		INNER JOIN membertypes ON membertypes.MemBTypeId = memberships.Mtype) WHERE MembId = ? ORDER BY YearText DESC";
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
		
		$q = "SELECT Role, GamesPlayed, years.YearText, teams.TeamName FROM ((teammembers 
		INNER JOIN years ON teammembers.YearId = years.YearId)
		INNER JOIN teams ON teammembers.TeamId = teams.TeamId) WHERE MembId = ? ORDER BY YearText DESC";
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $formperson);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		//$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
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
		
		$q = "SELECT Role, years.YearText, years.YearId, committees.CommitteeName FROM ((committeememb 
		INNER JOIN years ON committeememb.YearId = years.YearId)
		INNER JOIN committees ON committeememb.CommId = Committees.CommitteeId) WHERE MembId = ? ORDER BY YearText DESC";		
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
		
		$q = "SELECT Role, Details, years.YearText FROM volunteers 
		INNER JOIN years ON volunteers.YearId = years.YearId
		WHERE MembId = ? ORDER BY YearText DESC";	
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $formperson);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		//$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
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
		$pdf->Cell(15, 10, 'Year', 1, 0, 'C');
		$pdf->Cell(70, 10, 'Award', 1, 0, 'C');
		$pdf->Cell(40, 10, 'Comment', 1, 1, 'C');
		

		$pdf->SetFont('Arial', '', 10);
		
		$q = "SELECT Comments, awards.AwardName , years.YearText FROM ((awardwinners 
		INNER JOIN awards ON awardwinners.AwardId = awards.AwardID) INNER JOIN years ON awardwinners.YearId = years.YearId)
		WHERE MembId = ? ORDER BY YearText DESC";	
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $formperson);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		//$r = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
		if(mysqli_num_rows($r) > 0)
		{
			while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
			{
				$yr = $row['YearText'];
				$an = $row['AwardName'];
				$ac = $row['Comments'];
				$pdf->Cell(15, 7, "$yr", 1);
				$pdf->Cell(70, 7, "$an", 1);
				$pdf->Cell(40, 7, "$ac", 1, 1); //extra parameter is to start new line
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
		WHERE ac.MembId = ? ORDER BY YearText DESC";	
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
		
		$q = "SELECT Role, Details, years.YearText FROM employees 
		INNER JOIN years ON employees.YearId = years.YearId
		WHERE MembId = ? ORDER BY YearText DESC";
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
		WHERE MembId = ? ORDER BY IncidentDate DESC";
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

?>