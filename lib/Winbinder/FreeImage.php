<?php

/*******************************************************************************

 WINBINDER - A native Windows binding for PHP

 Copyright © 2004-2005 Hypervisual - see LICENSE.TXT for details
 Author: Rubem Pechansky (pechansky@hypervisual.com)

 Some functions to call the FreeImage library directly

*******************************************************************************/

/*

To create a new function, the actual function name inside the DLL must be
passed to wb_get_function_address(). The actual function name is obtained by
prepending the library function name with a '_', appending an '@', then the
number of arguments times 4.

Example: FreeImage_Load accepts 3 arguments, so it becomes "_FreeImage_Load@12"

Go to the FreeImage web site (http://freeimage.sourceforge.net) to download
the FreeImage DLL and documentation.

These functions were successfully tested with the following FreeImage versions:

2.3.1 (600 kB, 260 kB zipped)
2.5.4 (670 kB, 290 kB zipped)
3.4.0 (744 kB, 350 kB zipped)
3.5.3 (888 kB, 413 kB zipped)

*/

//-------------------------------------------------------------------- CONSTANTS

// These were taken from FreeImage.h (version 3.5.3)

define("FIF_UNKNOWN",	-1);
define("FIF_BMP",		0);
define("FIF_ICO",		1);
define("FIF_JPEG",		2);
define("FIF_JNG",		3);
define("FIF_KOALA",		4);
define("FIF_LBM",		5);
define("FIF_IFF", FIF_LBM);
define("FIF_MNG",		6);
define("FIF_PBM",		7);
define("FIF_PBMRAW",	8);
define("FIF_PCD",		9);
define("FIF_PCX",		10);
define("FIF_PGM",		11);
define("FIF_PGMRAW",	12);
define("FIF_PNG",		13);
define("FIF_PPM",		14);
define("FIF_PPMRAW",	15);
define("FIF_RAS",		16);
define("FIF_TARGA",		17);
define("FIF_TIFF",		18);
define("FIF_WBMP",		19);
define("FIF_PSD",		20);
define("FIF_CUT",		21);
define("FIF_XBM",		22);
define("FIF_XPM",		23);
define("FIF_DDS",		24);
define("FIF_GIF",		25);

//------------------------------------------------------------- GLOBAL VARIABLES

if (!isset($FI))
	$FI = wb_load_library('freeimage');

//-------------------------------------------------------------------- FUNCTIONS

function FreeImage_GetVersion() {
	global $FI;
	static $pfn = null;

	if($pfn === null)
		$pfn = wb_get_function_address("_FreeImage_GetVersion@0", $FI);

	// Must use wb_peek because this function returns a string pointer

	$version = wb_peek(wb_call_function($pfn));
	return $version;
}

function FreeImage_GetInfoHeader($dib) {
	global $FI;
	static $pfn = null;

	if ($pfn === null)
		$pfn = wb_get_function_address("_FreeImage_GetInfoHeader@4", $FI);

	return wb_call_function($pfn, array($dib));
}

function FreeImage_GetBits($dib) {
	global $FI;
	static $pfn = null;

	if ($pfn === null)
		$pfn = wb_get_function_address("_FreeImage_GetBits@4", $FI);

	return wb_call_function($pfn, array($dib));
}

function FreeImage_Unload($bmp) {
	global $FI;
	static $pfn = null;

	if ($pfn === null)
		$pfn = wb_get_function_address("_FreeImage_Unload@4", $FI);

	return wb_call_function($pfn, array($bmp));
}

function FreeImage_GetWidth($bmp) {
	global $FI;
	static $pfn = null;

	if ($pfn === null)
		$pfn = wb_get_function_address("_FreeImage_GetWidth@4", $FI);

	return wb_call_function($pfn, array($bmp));
}

function FreeImage_GetHeight($bmp) {
	global $FI;
	static $pfn = null;

	if ($pfn === null)
		$pfn = wb_get_function_address("_FreeImage_GetHeight@4", $FI);

	return wb_call_function($pfn, array($bmp));
}

function FreeImage_Load($type, $filename, $flags=0) {
	global $FI;
	static $pfn = null;

	if ($pfn === null)
		$pfn = wb_get_function_address("_FreeImage_Load@12", $FI);

	return wb_call_function($pfn, array($type, $filename, $flags));
}

function FreeImage_Save($type, $dib, $filename, $flags=0) {
	global $FI;
	static $pfn = null;

	if ($pfn === null)
		$pfn = wb_get_function_address("_FreeImage_Save@16", $FI);

	return wb_call_function($pfn, array($type, $dib, $filename, $flags));
}

//-------------------------------------------------------------------------- END

?>