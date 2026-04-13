<?php
//Rev 1 2/4/2026
//this page is part of the reports group
//it is called from the reports page after the selection of a year
//a pdf of all activities for the club for the year is generated and downloaded
require('fpdf/fpdf.php');
class PDF extends FPDF
{
    function Header()
    {
        //get year text
		$reportyear = htmlentities($_POST['clubyear']);
		$q = 'SELECT * FROM years WHERE YearId = ?';
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $reportyear);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		$yearstring = mysqli_fetch_array($r, MYSQLI_ASSOC);
		mysqli_stmt_close($stmt);
		mysqli_close($link);
		// Select Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Move to the right
        //$this->Cell(30);
		//insert logo
		$this->Image('images/Redbacks.png', 10, 10, -300);
        // Framed title
		$this->Cell(30, 10);
        $this->Cell(80, 10, '  '.$yearstring['YearText'].' PBA Club Activity Report', 0, 0, 'R');
        // Line break
        $this->Ln(20);
	}
	
	    function Footer()
    {
        // Go to 1.5 cm from bottom
        $this->SetY(-15);
        // Select Arial italic 8
        $this->SetFont('Arial', 'I', 8);
		$reportdate = date('j F Y');
        // Print centered page number
        $this->Cell(0, 4, 'Page '.$this->PageNo().' of {nb}', 0, 1, 'C');
		$this->Cell(0, 4, $reportdate, 0, 0, 'C');
    }
}
	//if activity report button active
	if(isset($_POST['dlclubactivityreport']))
	{
		$reportyear = htmlentities($_POST['clubyear']);
		//get total members for the year
		$q = "SELECT COUNT(Mtype) AS Qty FROM memberships WHERE YearId = ?";
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $reportyear);
		mysqli_stmt_execute($stmt);
		$rtot = mysqli_stmt_get_result($stmt);
		if(mysqli_num_rows($rtot) > 0)
		{
			$totmembers = mysqli_fetch_array($rtot, MYSQLI_ASSOC);
		}
		else
		{
			$totmembers['Qty'] = 0;
		}
		mysqli_stmt_close($stmt);
		//get the number of each type of membership
		$q = 'SELECT 
			mbs.Mtype,
			COUNT(mbs.MembId) AS Qty,
			mt.Type
		FROM memberships mbs
		INNER JOIN membertypes mt
		ON mt.MemBTypeId = mbs.Mtype
		WHERE mbs.YearId = ?
		GROUP BY mt.Type';
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $reportyear);
		mysqli_stmt_execute($stmt);
		$rmbt = mysqli_stmt_get_result($stmt);
		mysqli_stmt_close($stmt);	
		mysqli_close($link);

		$pdf = new PDF(); //create new pdf doc
		$pdf->AliasNbPages();
		$pdf->AddPage(); //add the first page

        // Line break
		$pdf->SetLeftMargin(10);
        $pdf->Ln(20);

		$pdf->SetFont('Arial', '', 14);
		$pdf->Cell(180, 10, 'Memberships - '.$totmembers['Qty'], "TB", 1); //display total membership as heading
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(30, 9, 'Type', 1, 0, 'L'); // heading for table of membership types
		$pdf->Cell(15, 9, 'Qty', 1, 1, 'C');
		

		$pdf->SetFont('Arial', '', 10);
		
		if(mysqli_num_rows($rmbt) > 0)
		{
			while($row = mysqli_fetch_array($rmbt,MYSQLI_ASSOC)) //display each membership type and qty
			{
				$mtyp = $row['Type'];
				$mqty = $row['Qty'];
				$pdf->Cell(30, 7, "$mtyp", 1,);
				$pdf->Cell(15, 7, "$mqty", 1, 1, 'C'); //extra parameters are to start new line and centre text
			}
		}
		else
		{
			$pdf->Cell(45, 7, 'No memberships found.', 1, 1);
		}
		
		// start teams section
		//Find total teams
		$q = 'SELECT COUNT(DISTINCT TeamId) AS Qty FROM teammembers WHERE YearId = ?';
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $reportyear);
		mysqli_stmt_execute($stmt);
		$rtot = mysqli_stmt_get_result($stmt);
		if(mysqli_num_rows($rtot) > 0)
		{
			$totteams = mysqli_fetch_array($rtot, MYSQLI_ASSOC);
		}
		else
		{
			$totteams['Qty'] = 0;
		}
		mysqli_stmt_close($stmt);
		//mysqli_stmt_close($stmt);
		//mysqli_close($link);
		
        $pdf->Ln(10);
		$pdf->SetFont('Arial', '', 14);
		$pdf->Cell(180, 10, 'Teams - '.$totteams['Qty'], "TB", 1);
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(25, 9, 'League', 1, 0, 'C');
		$pdf->Cell(30, 9, 'Teams', 1, 0, 'C');
		$pdf->Cell(30, 9, 'Team Members', 1, 1, 'C');
		
		//get the number of teams in each league
		$q = 'SELECT 
			LEFT(t.TeamName, 4) AS League,
			COUNT(DISTINCT tm.TeamId) AS Qty,
			COUNT(tm.MembId) AS Players
		FROM teammembers tm
		INNER JOIN teams t
		ON t.TeamId = tm.TeamId
		WHERE tm.YearId = ?
		GROUP BY LEFT(t.TeamName, 4)
		ORDER BY t.TeamName';
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $reportyear);
		mysqli_stmt_execute($stmt);
		$rtbl = mysqli_stmt_get_result($stmt);
		mysqli_stmt_close($stmt);	
		mysqli_close($link);

		$pdf->SetFont('Arial', '', 10);
		
		if(mysqli_num_rows($rtbl) > 0)
		{
			while($row = mysqli_fetch_array($rtbl,MYSQLI_ASSOC))
			{
				$lg = $row['League'];
				$qt = $row['Qty'];
				$pl = $row['Players'];
				$pdf->Cell(25, 7, "$lg", 1);
				$pdf->Cell(30, 7, "$qt", 1, 0, 'C');
				$pdf->Cell(30, 7, "$pl", 1, 1, 'C'); //extra parameter is to start new line
			}
			$pdf->SetFont('Arial', '', 8);
			$pdf->Cell(80, 7, 'Note: Team members include coaches, manager, etc.');
		}
		else
		{
			$pdf->Cell(85, 7, 'No team roles found.', 1, 1);
		}

		//start committee section
		//get number of committees
		$q = 'SELECT COUNT(DISTINCT CommId) AS Qty FROM committeememb WHERE YearId = ?';
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $reportyear);
		mysqli_stmt_execute($stmt);
		$rtotcomms = mysqli_stmt_get_result($stmt);
		if(mysqli_num_rows($rtotcomms) > 0)
		{
			$totcomms = mysqli_fetch_array($rtotcomms, MYSQLI_ASSOC);
		}
		else
		{
			$totcomms['Qty'] = 0;
		}
		mysqli_stmt_close($stmt);
		
        $pdf->Ln(10);
		$pdf->SetFont('Arial', '', 14);
		$pdf->Cell(180, 10, 'Committees - '.$totcomms['Qty'], "TB", 1);
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 12);
		//$pdf->Cell(15, 9, 'Year', 1, 0, 'C');
		$pdf->Cell(70, 9, 'Committee', 1, 0, 'C');
		$pdf->Cell(40, 9, 'Members', 1, 1, 'C');
		

		$pdf->SetFont('Arial', '', 10);
		
		$q = "SELECT 
		COUNT(cm.MembId) AS Qty,
		c.CommitteeName 
		FROM committeememb cm 
		INNER JOIN committees c 
		ON cm.CommId = c.CommitteeId WHERE cm.YearId = ? GROUP BY cm.CommId ORDER BY c.CommitteeName";
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $reportyear);
		mysqli_stmt_execute($stmt);
		$rcomm = mysqli_stmt_get_result($stmt);
		mysqli_stmt_close($stmt);	
		mysqli_close($link);		

		if(mysqli_num_rows($rcomm) > 0)
		{
			while($row = mysqli_fetch_array($rcomm,MYSQLI_ASSOC))
			{
				$cn = $row['CommitteeName'];
				$cq = $row['Qty'];
				$pdf->Cell(70, 7, "$cn", 1);
				$pdf->Cell(40, 7, "$cq", 1, 1, 'C'); //extra parameter is to start new line
			}
			
		}
		else
		{
			$pdf->Cell(110, 7, 'No committee roles found.', 1, 1);
		}		
		
		$pdf->AddPage();		
		//start awards section
		//get number of awards presented and people receiving them
		$q = 'SELECT COUNT(DISTINCT AwardId) AS AwQty, COUNT(DISTINCT MembId) AS PeQty FROM awardwinners WHERE YearId = ?';
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $reportyear);
		mysqli_stmt_execute($stmt);
		$rtotawrds = mysqli_stmt_get_result($stmt);
		if(mysqli_num_rows($rtotawrds) > 0)
		{
			$totawards = mysqli_fetch_array($rtotawrds, MYSQLI_ASSOC);
		}
		else
		{
			$totawards['AwQty'] = 0;
			$totawards['PeQty'] = 0;
		}
		mysqli_stmt_close($stmt);
		mysqli_close($link);
		
        $pdf->Ln(10);
		$pdf->SetFont('Arial', '', 14);
		$pdf->Cell(180, 10, 'Awards', "TB", 1);
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(70, 9, 'Awards Presented', 1, 0);
		$pdf->Cell(20, 9, $totawards['AwQty'], 1, 1, 'C');
		$pdf->Cell(70, 9, 'Award Recipients', 1, 0);
		$pdf->Cell(20, 9, $totawards['PeQty'], 1, 1, 'C');

		$pdf->SetFont('Arial', '', 10);
		
        //start volunteers section
		//get number of volunteer roles and people performing them
		$q = 'SELECT COUNT(DISTINCT Role) AS RlQty, COUNT(DISTINCT MembId) AS PeQty FROM volunteers WHERE YearId = ?';
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $reportyear);
		mysqli_stmt_execute($stmt);
		$rtotvol = mysqli_stmt_get_result($stmt);
		if(mysqli_num_rows($rtotvol) > 0)
		{
			$totvols = mysqli_fetch_array($rtotvol, MYSQLI_ASSOC);
		}
		else
		{
			$totvols['RlQty'] = 0;
			$totvols['PeQty'] = 0;
		}
		mysqli_stmt_close($stmt);
		mysqli_close($link);
		
        $pdf->Ln(10);
		$pdf->SetFont('Arial', '', 14);
		$pdf->Cell(180, 10, 'Volunteers', "TB", 1);
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(70, 9, 'Roles', 1, 0);
		$pdf->Cell(20, 9, $totvols['RlQty'], 1, 1, 'C');
		$pdf->Cell(70, 9, 'People', 1, 0);
		$pdf->Cell(20, 9, $totvols['PeQty'], 1, 1, 'C');

		//start accolades section
		//get number of accolades and people receiving them
		$q = 'SELECT COUNT(Accolade) AS AcQty, COUNT(DISTINCT MembId) AS PeQty FROM accolades WHERE YearId = ?';
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $reportyear);
		mysqli_stmt_execute($stmt);
		$rtotacc = mysqli_stmt_get_result($stmt);
		if(mysqli_num_rows($rtotacc) > 0)
		{
			$totaccs = mysqli_fetch_array($rtotacc, MYSQLI_ASSOC);
		}
		else
		{
			$totaccs['AcQty'] = 0;
			$totaccs['PeQty'] = 0;
		}
		mysqli_stmt_close($stmt);
		mysqli_close($link);
		
        $pdf->Ln(10);
		$pdf->SetFont('Arial', '', 14);
		$pdf->Cell(180, 10, 'Accolades', "TB", 1);
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(70, 9, 'Accolades', 1, 0);
		$pdf->Cell(20, 9, $totaccs['AcQty'], 1, 1, 'C');
		$pdf->Cell(70, 9, 'Recipients', 1, 0);
		$pdf->Cell(20, 9, $totaccs['PeQty'], 1, 1, 'C');
		
		//start employees section
		//get number of employee roles and people performing them
		$q = 'SELECT COUNT(DISTINCT Role) AS RlQty, COUNT(DISTINCT MembId) AS PeQty FROM employees WHERE YearId = ?';
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $reportyear);
		mysqli_stmt_execute($stmt);
		$rtotemp = mysqli_stmt_get_result($stmt);
		if(mysqli_num_rows($rtotemp) > 0)
		{
			$totemps = mysqli_fetch_array($rtotemp, MYSQLI_ASSOC);
		}
		else
		{
			$totemps['RlQty'] = 0;
			$totemps['PeQty'] = 0;
		}
		mysqli_stmt_close($stmt);
		mysqli_close($link);
		
        $pdf->Ln(10);
		$pdf->SetFont('Arial', '', 14);
		$pdf->Cell(180, 10, 'Employees', "TB", 1);
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(70, 9, 'Roles', 1, 0);
		$pdf->Cell(20, 9, $totemps['RlQty'], 1, 1, 'C');
		$pdf->Cell(70, 9, 'People', 1, 0);
		$pdf->Cell(20, 9, $totemps['PeQty'], 1, 1, 'C');

		//start incidents section
		//get number of new incidents
		$q = 'SELECT COUNT(MembId) AS InQty FROM incident WHERE YearId = ?';
		require('../connecttopba.php');
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $reportyear);
		mysqli_stmt_execute($stmt);
		$rtotinc = mysqli_stmt_get_result($stmt);
		if(mysqli_num_rows($rtotinc) > 0)
		{
			$totincs = mysqli_fetch_array($rtotinc, MYSQLI_ASSOC);
		}
		else
		{
			$totincs['InQty'] = 0;
		}
		mysqli_stmt_close($stmt);
		mysqli_close($link);
		
        $pdf->Ln(10);
		$pdf->SetFont('Arial', '', 14);
		$pdf->Cell(180, 10, 'Incidents', "TB", 1);
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(70, 9, 'New', 1, 0);
		$pdf->Cell(20, 9, $totincs['InQty'], 1, 1, 'C');
		
		//get the number of open incidents
		$q = 'SELECT COUNT(MembId) AS OpQty FROM incident WHERE TribunalResult = ""';
		require('../connecttopba.php');
		$ropinc = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
		if(mysqli_num_rows($ropinc) > 0)
		{
			$totincs = mysqli_fetch_array($ropinc, MYSQLI_ASSOC);
		}
		else
		{
			$totincs['OpQty'] = 0;
		}
		//mysqli_stmt_close($stmt);
		mysqli_close($link);
		$pdf->Cell(70, 9, 'Open', 1, 0);
		$pdf->Cell(20, 9, $totincs['OpQty'], 1, 1, 'C');
		
		//get the number of unserved penalties
		$q = 'SELECT COUNT(MembId) AS PnQty FROM incident WHERE ExpiryDate > CURDATE()';
		require('../connecttopba.php');
		$rpninc = mysqli_query($link, $q, MYSQLI_STORE_RESULT);
		if(mysqli_num_rows($rpninc) > 0)
		{
			$totincs = mysqli_fetch_array($rpninc, MYSQLI_ASSOC);
		}
		else
		{
			$totincs['PnQty'] = 0;
		}
		$pdf->Cell(70, 9, 'Serving', 1, 0);
		mysqli_close($link);
		$pdf->Cell(20, 9, $totincs['PnQty'], 1, 1, 'C');
		
		
		$pdf->SetFont('Arial', '', 8);
		$pdf->Ln(2);
		$pdf->Cell(15, 4, 'Note:', 0, 0, 'R');
		$pdf->Cell(100, 4, 'New are incidents created in the report year.', 0, 1);
		$pdf->Cell(15, 4, '', 0, 0);
		$pdf->Cell(100, 4, 'Open are incidents under investigation when report is generated.', 0, 1);
		$pdf->Cell(15, 4, '', 0, 0);
		$pdf->Cell(100, 4, 'Serving are incidents where the offender is serving out a penalty when the report is generated.', 0, 1);
		
		
		$pdf->Output('D', 'pbaactivityreport.pdf');
	}
//header(Location: 'pbareports.php');


?>