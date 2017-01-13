<?php 

$file = file_get_contents('mergedoc.rtf');

// To temporary get rid of the escape characters...
$mergetext = str_replace("\\", "€€", $file); 

// The five-part regex expression (carefully crafted) :-)
$regex = '/<<((?:€€[a-z0-9]*|\}|\{|\s)*)([a-z0-9.\-\+_æøåÆØÅA-Z]*)((?:€€[a-z0-9]*|\}|\{|\s)*)([a-z0-9.\-\+_æøåÆØÅA-Z]*)((?:€€[a-z0-9]+|\}|\{|\s)*)>>/'; 

// Find all the matches in it....
preg_match_all($regex,$mergetext, $out, PREG_SET_ORDER);

// Lets see the result
var_dump($out); 

foreach ($out as $match) {
	$whole_tag = $match[0]; // The part we actually replace. 
	$start = $match[1]; // The start formatting that has been injected in our tag, if any
	$tag = $match[2]; // The tag word itself. 
	$end = $match[3]; // The end formatting that might be inserted. 
	$secPartTag = $match[4]; // Do we have inserted some formatting inside the tag word too ? 
	if ($secPartTag != "") {
		$tag .= $secPartTag; // Put it together with the tag word. 
		$end = $match[5]; // We ignore the match-3, since this then becomes the formatting injected inside the word
						// and lets the new end formatting be match-5 instead. 
	}
	
	// Simple selection of what we do with the tag. 
	switch ($tag) {
		case 'COMPANY_NAME': 
			$txt = "MY MERGE COMPANY EXAMPLE LTD"; 
			break; 
		case 'SOMEOTHERTAG':
			$txt = "SOME OTHER TEXT XX"; 
			break; 
		default:
			$txt = "NOTAG"; 
	}
	// Create RTF Line breaks in text, if any. 
	$txt = str_replace(chr(10), chr(10)."\\line", $txt); 
	// Do the replace in the file. 
	$mergetext = str_replace($whole_tag, $start.$txt.$end, $mergetext); 
}
// Put back the escape characters. 
$file = str_replace("€€", "\\", $mergetext);
// Save to file. Extention .doc makes it open in Word by default. 
file_put_contents("ResultDoc.doc", $file); 

?>