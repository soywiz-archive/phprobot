<?php

/*******************************************************************************

 WINBINDER - A native Windows binding for PHP

 Copyright © 2004-2005 Hypervisual - see LICENSE.TXT for details
 Author: Rubem Pechansky (pechansky@hypervisual.com)

 General-purpose supporting functions

*******************************************************************************/

//-------------------------------------------------------------------- FUNCTIONS

/*

Mimics function file_put_contents from PHP 5

int file_put_contents (string filename, string data [, int flags [, resource context]])

*/

if(PHP_VERSION < "5.0.0") {

function file_put_contents($filename, $data, $flags=0, $zcontext=null)
{
	if($zcontext)
		$fp = @fopen($filename, "w+b", $flags, $zcontext);
	else
		$fp = @fopen($filename, "w+b", $flags);
	if(!$fp)
		return FALSE;
	fwrite($fp, $data);
	fclose($fp);
	return TRUE;
}

}	// PHP_VERSION < "5.0.0"


/* Returns an array with all files of subdirectory $path. If $subdirs is TRUE,
  includes subdirectories recursively. $mask is a PCRE regular epression.
*/

function get_folder_files($path, $subdirs=false, $fullname=true, $mask="")
{
	// Correct path name, if needed

	$path = str_replace('/', '\\', $path);
	if(substr($path, -1) != '\\')
		$path .= "\\";
	if(!$path || !@is_dir($path))
		return array();

	// Browse the subdiretory list recursively

	$dir = array();
	if($handle = opendir($path)) {
		while(($file = readdir($handle)) !== false) {
			if(!is_dir($path.$file)) {	// No directories / subdirectories
				$file = strtolower($file);
				if(!$mask) {
					$dir[] = $fullname ? $path.$file : $file;
				} else if($mask && preg_match($mask, $file)) {
					$dir[] = $fullname ? $path.$file : $file;
				}
			} else if($subdirs && $file[0] != ".") {	// Exclude "." and ".."
				$dir = array_merge($dir, get_folder_files($path.$file, $subdirs, $fullname, $mask));
			}
		}
	}
	closedir($handle);
	return $dir;
}

//-------------------------------------------------------------------- INI FILES

/* Transforms the array $data in a text that can be saved as an INI file */

function generate_ini($data, $comments="")
{
	if(!is_array($data)) {
		trigger_error(__FUNCTION__ . ": Cannot save INI file.");
		return null;
	}
	$text = $comments;
	foreach($data as $name=>$section) {
		$text .= "\r\n[$name]\r\n";
		foreach($section as $key=>$value) {
			$value = trim($value);
			if((string)((int)$value) == (string)$value)
				;	// Integer: does nothing
			elseif((string)((float)$value) == (string)$value)
				;	// Floating point: does nothing
			elseif($value[0] == '"' && $value[strlen($value)-1] == '"')
				;	// Has quotes already: does nothing
			else
				$value = '"' . $value . '"';
			$text .= "$key = " . $value . "\r\n";
		}
	}
	return $text;
}

/* Replaces function parse_ini_file() with the following differences:

- The $initext parameter is the content of an INI file, not the file name
- Accepts sections without identifiers
- Returns sections without identifiers as an array of numeric keys
- Change case optionally

TODO: Understand "yes", "no", "on", "off", etc.
*/

function parse_ini($initext, $changecase=TRUE)
{
	$ini = preg_split("/\r\n|\n/", $initext);
	$secpattern = "/^\[(.[^\]]*)\]/i";
	$entrypattern = "/^([a-z_0-9]*)\s*=\s*\"?(.[^\"]*)\"?$/i";
	$strpattern = "/^\"?(.[^\"]*)\"?$/i";

	$section = array();
	$sec = "";

	// Loop of lines

	for($i = 0; $i < count($ini); $i++) {

		$line = trim($ini[$i]);

		// Skips blank lines and comments

		if($line == "" || preg_match("/^;/i", $line))
			continue;

		if(preg_match($secpattern, $line)) {

			// It's a section

			if($changecase)
				$sec = ucfirst(strtolower(preg_replace($secpattern, "\\1", $line)));
			else
				$sec = preg_replace($secpattern, "\\1", $line);
			$section[$sec] = array();

		} elseif(preg_match($entrypattern, $line)) {

			// It's an entry

			if($changecase)
				$entry = strtolower(preg_replace($entrypattern, "\\1", $line));
			else
				$entry = preg_replace($entrypattern, "\\1", $line);
			$value = preg_replace($entrypattern, "\\2", $line);
			$section[$sec][$entry] = $value;

		} else {

			// It's a normal string

			$section[$sec][] = preg_replace($strpattern, "\\1", $line);

		}
	}
	return $section;
}

//------------------------------------------------------------------ END OF FILE

?>