<?php
//Rev 1 26/10/2026 used in conjunction with pbadocsearch to display files.
?>

<?php

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
		//$doctype = mime_content_type($filepath);
		$mimetypes = [
		'pdf'  => 'application/pdf',
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png'  => 'image/png'
		];

		// Check if the file exists
		if (file_exists($filepath)) 
		{			
			// Set headers to trigger download
			header('Content-Type: '. $mimetypes[strtolower($row['FileType'])]);
			header('Content-Disposition: inline');
			header('X-Content-Type-Options: nosniff');
			//header('Pragma: no-cache');
			//header('Expires: 0');
			//ob_clean();
			//flush();
			 while (ob_get_level() > 0) {
				ob_end_clean();
			}

			// Read the file and output to browser
			readfile($filepath);
			exit;
		}
		else
		{
			echo 'File missing, check with the system administrator.';
		}
		
	}
	//mysqli_stmt_close($stmt);
	//mysqli_close($link);
}
?>

