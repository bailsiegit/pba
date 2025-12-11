<?php
//Rev 1 19/11/2025
// this file is used to send a stored document to the browser
//it is called from other pages as necessary
session_start();

if(isset($_GET['did']))
{
	$getdocid = htmlentities($_GET['did']); // clean the document id passed via getallheaders
	// get the document data from the documents table
	$q = "SELECT * FROM documents WHERE DocumentId = ?";
	require('../connecttopba.php');
	$stmt = mysqli_prepare($link, $q);
	mysqli_stmt_bind_param($stmt, "i", $getdocid);
	mysqli_stmt_execute($stmt);
	$r = mysqli_stmt_get_result($stmt);
	
	// create the file path from the data
	if(mysqli_num_rows($r) > 0) // if there is a matching row then proceed
	{
		$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
		$filepath = '..//documents//'.$row['Activity'].'//'.$row['FileName'];
		$doctype = mime_content_type($filepath);
		// create a easily read filename for the document
		$publicfilename = $row['DocName'].'.'.$row['FileType'];

		// Check if the file exists
		if (file_exists($filepath)) 
		{
			// Set headers to trigger download
			header('Content-Type: '. $doctype);
			header('Content-Disposition: attachment; filename="' . $publicfilename . '"');
			header('Content-Length: ' . filesize($filepath));
			header('Pragma: no-cache');
			header('Expires: 0');
			ob_clean();
			flush();

			// Read the file and output to browser
			readfile($filepath);
		}
	}
	mysqli_stmt_close($stmt);
	mysqli_close($link);
}
?>