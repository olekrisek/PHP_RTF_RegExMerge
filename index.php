<?php 

$file = file_get_contents('mergedoc.rtf');

// To temporary get rid of the escape characters...
$mergetext = str_replace("\\", "€€", $file); 

// The five-part regex expression (carefully crafted) :-)
// $regex = '/<<((?:€€[a-z0-9]*|\}|\{|\s)*)([a-z0-9.\-\+_æøåÆØÅA-Z]*)((?:€€[a-z0-9]*|\}|\{|\s)*)([a-z0-9.\-\+_æøåÆØÅA-Z]*)((?:€€[a-z0-9]+|\}|\{|\s)*)>>/'; 
// New seven part regex with default value detection
$regex2 = '/<<((?:€€[a-z0-9]*|\}|\{|\s)*)([a-z0-9.\-\+_æøåÆØÅA-Z]*)((?:€€[a-z0-9]*|\}|\{|\s)*)([a-z0-9.\-\+_æøåÆØÅA-Z]*)((?:€€[a-z0-9]*|\}|\{|\s)*)(?:\s*:(.*?)\s*)?((?:€€[a-z0-9]*|\}|\{|\s)*)>>/';

// Find all the matches in it....
preg_match_all($regex2,$mergetext, $out, PREG_SET_ORDER);

// Lets see the result
var_dump($out); 

foreach ($out as $match) {
	$whole_tag = $match[0]; // The part we actually replace. 
	$start = $match[1]; // The start formatting that has been injected in our tag, if any
	$tag = $match[2]; // The tag word itself. 
	if (($match[4].$match[6]) != "") { //some sec-part tag or default value?
		$end = $match[5]; // The end formatting that might be inserted. 
		if ($end == "") {
			$end = $match[7]; // No end in 5, we try 7. 
		}
	} else {
		$end = $match[3]; // No second tag or default value, we find end in match-3 
	}
	
	$secPartTag = $match[4]; // Do we have inserted some formatting inside the tag word too ? 
	if ($secPartTag != "") {
		$tag .= $secPartTag; // Put it together with the tag word. 
	}
	$default_value = $match[6]; 
	
	// Simple selection of what we do with the tag. 
	switch ($tag) {
		case 'COMPANY_NAME': 
			$txt = "MY MERGE COMPANY EXAMPLE LTD"; 
			break; 
		case 'SOMEOTHERTAG':
			$txt = "SOME OTHER TEXT XX"; 
			break; 
		case 'THISHASDEFAULT':
			$txt = ""; 
			break; 
		
		default:
			$txt = "NOTAG"; 
	}
	if ($txt == "") {
		$txt = $default_value; 
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