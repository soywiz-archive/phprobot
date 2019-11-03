<?php
/**************************************************************************
| Winbinder Oriented Objects Interface
| Version: 0.3 for Winbinder
| Author: Alexandre Tedeschi
| E-mail: alexandrebr@gmail.com
|
| Why?
|   The original syntax of Winbinder is very obscure and ugly, IMHO.
|   This setting of classes should make it a little more exciting to
|   program using a friendly interface, based on Objects.
|
|   Who already tried PHP-Gtk knows the importance of a clean code, and
|   that's what I'm trying to do.
|
|   Also, I'm adding some implementations of some things I think winbinder
|   is lacking.
\*************************************************************************/

// error_reporting(E_ALL);

DEFINE("WB_OOiVERSION", 0.223);
if(!defined("WBOBJ"))      define("WBOBJ", "Vhandle/Vid/Vwbclass/litem/lsubitem/Vstyle/Vparent/Vhandler/Vlparam");
if(!defined("WBOBJ_RAW"))  define("WBOBJ_RAW", "V3l2V4");
if(!defined("WBOBJ_SIZE")) define("WBOBJ_SIZE", 9 * 4);

if(phpversion() < 5){
	wb_message_box(null, "You're using PHP " . PHPVERSION() . "\n\nWinbinder OOi requires PHP 5 or newer.", "Error", WBC_OK);
	die;
}

require "ooi/ooi_signal-control.php";         // Signal Control
require "ooi/ooi_wb.php";                     // Wb
require "ooi/ooi_wbimage.php";                // WbImage
require "ooi/ooi_wbtimer.php";                // WbTimer
require "ooi/ooi_wbobject.php";               // WbObject
require "ooi/ooi_wbwidget.php";               // | WbWidget and WbHolder
require "ooi/ooi_wbwindow.php";               // | WbWindow and its extended final classes
require "ooi/ooi_wbcontrol.php";              // | WbControl and its extended final classes, except the ones wich are listed below
require "ooi/ooi_wbcontainer.php";            // | | WbContainer and its extended final classes (ListView and TreeView)
require "ooi/wbcontrols/ooi_accel.php";       // | | WbAccel
require "ooi/wbcontrols/ooi_checkbox.php";    // | | WbCheckbox
require "ooi/wbcontrols/ooi_menu.php";        // | | WbMenu and WbMenuItem
require "ooi/wbcontrols/ooi_radiobutton.php"; // | | WbRadiobutton
require "ooi/wbcontrols/ooi_toolbar.php";     // | | WbToolbar
require "ooi/wbcontrols/ooi_tabcontrol.php";  // | | WbTabControl and WbTab
require "ooi/wbcontrols/ooi_listboxes.php";   // | | WbListBoxes and its extended final classes (ComboBox and ListBox)
