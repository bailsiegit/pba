<?php
//Rev 1 19/11/2025
//this page is called from the reports page
//the report is compiled for the year selected on that page
//a pdf is generated and downloaded of all members eligible to vote
//at a general meeting of the club in a format where they can sign at entry.
require('fpdf/fpdf.php');
class PDF extends FPDF
{
    function Header()
    {
        // Select Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Move to the right
        //$this->Cell(30);
		//insert logo
		$this->Image('images/Redbacks.png', 10, 10, -300);
        // Framed title
        $this->Cell(80, 10, '  PBA AGM Sign-in List', 0, 0, 'C');
        // Line break
        $this->Ln(20);
		$this->SetFont('Arial', 'B', 16);
		$this->Cell(30, 10, "Type", 1);
		$this->Cell(45, 10, "First Name", 1);
		$this->Cell(45, 10, "Last Name", 1);
		$this->Cell(30, 10, "Signature", 'TLB', 0, 'R'); //extra parameter is to start new line
		$this->SetFont('Arial', '', 10);
		$this->Cell(30, 10, "(or proxy name)", 'TRB', 1, 'L');
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
	//if email list button active
	if(isset($_POST['agmlist']))
	{
		$formyear = htmlentities($_POST['selectedyear']);

		$pdf = new PDF(); //create new pdf doc
		$pdf->AliasNbPages();
		$pdf->AddPage(); //add the first page

		$pdf->SetFont('Arial', '', 12);
		require('../connecttopba.php');
		$q = "SELECT membertypes.Type, members.FirstName, members.LastName FROM ((members 
		INNER JOIN memberships ON members.MemberId = memberships.MembId) 
		INNER JOIN membertypes ON memberships.Mtype = membertypes.MemBTypeId) 
		WHERE memberships.Year = ? AND memberships.end = '0000-00-00' AND (memberships.Mtype = 1 OR memberships.Mtype = 3) ORDER BY LastName";
		$stmt = mysqli_prepare($link, $q);
		mysqli_stmt_bind_param($stmt, "i", $formyear);
		mysqli_stmt_execute($stmt);
		$r = mysqli_stmt_get_result($stmt);
		
			while($row = mysqli_fetch_array($r,MYSQLI_ASSOC))
			{
				$type = $row['Type'];
				$fn = $row['FirstName'];
				$ln = $row['LastName'];
				$pdf->Cell(30, 7, "$type", 1);
				$pdf->Cell(45, 7, "$fn", 1);
				$pdf->Cell(45, 7, "$ln", 1);
				$pdf->Cell(60, 7, "", 1, 1); //extra parameter is to start new line
			}
		$pdf->Output('D', 'pbaAGMlist.pdf');
	}
header(Location: 'pbareports.php');


?>