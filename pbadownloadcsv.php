<?php
//Rev 1 19/11/2025
// This file sends a csv file to the browser
// The file is called from the various pbaactivity.... pages
session_start();
$filename = htmlentities(trim($_GET['fn'])); //clean input
$file = 'downloads/'.$filename.'.csv'; //generate full path and file name

// Check if the file exists. If so send to browser.
if (file_exists($file)) 
{
    // Set headers to trigger download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file));
    header('Pragma: no-cache');
    header('Expires: 0');

    // Read the file and output to browser
    readfile($file);
}
?>