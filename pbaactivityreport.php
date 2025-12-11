<?php
//Rev 1 19/11/2025
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

		$pdf->SetFont('Arial', '', 14);
		$pdf->Cell(180, 10, "Memberships", "TB", 1);
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(15, 10, 'Year', 1, 0, 'C');
		$pdf->Cell(30, 10, 'Member Type', 1, 1, 'C');
		

		$pdf->SetFont('Arial', '', 10);
		
		$q = "SELECT years.YearText, membertypes.Type FROM ((memberships 
		INNER JOIN years ON memberships.Year = years.YearId)
		INNER JOIN membertypes ON membertypes.MemBTypeId = memberships.Mtype) WHERE MembId = ? ORDER BY YearText DESC";
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
		INNER JOIN years ON teammembers.Year = years.YearId)
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
		INNER JOIN years ON committeememb.Year = years.YearId)
		INNER JOIN committees ON committeememb.CommId = Committees.CommitteeId) WHERE MembId = ? ORDER BY YearText DESC";		
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
		
        $pdf->Ln(10);
		$pdf->SetFont('Arial', '', 14);
		$pdf->Cell(180, 10, "Voluteer", "TB", 1);
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(15, 10, 'Year', 1, 0, 'C');
		$pdf->Cell(40, 10, 'Role', 1, 1, 'C');
		

		$pdf->SetFont('Arial', '', 10);
		
		$q = "SELECT Role, years.YearText FROM volunteers 
		INNER JOIN years ON volunteers.Year = years.YearId
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
				$pdf->Cell(15, 7, "$yr", 1);
				$pdf->Cell(40, 7, "$vr", 1, 1); //extra parameter is to start new line
			}
		}
		else
		{
			$pdf->Cell(55, 7, 'No volunteer roles found.', 1, 1);
		}
		mysqli_stmt_close($stmt);
		mysqli_close($link);
		
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
		
        $pdf->Ln(10);
		$pdf->SetFont('Arial', '', 14);
		$pdf->Cell(180, 10, "Employee", "TB", 1);
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(15, 10, 'Year', 1, 0, 'C');
		$pdf->Cell(40, 10, 'Role', 1, 1, 'C');
		

		$pdf->SetFont('Arial', '', 10);
		
		$q = "SELECT Role, years.YearText FROM employees 
		INNER JOIN years ON employees.Year = years.YearId
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
				$er = $row['Role'];
				$pdf->Cell(15, 7, "$yr", 1);
				$pdf->Cell(40, 7, "$er", 1, 1); //extra parameter is to start new line
			}
		}
		else
		{
			$pdf->Cell(55, 7, 'No volunteer roles found.', 1, 1);
		}
		mysqli_stmt_close($stmt);
		mysqli_close($link);
		
		$pdf->Output('D', 'pbaactivityreport.pdf');
	}
header(Location: 'pbareports.php');


?>