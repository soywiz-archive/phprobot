<?php

/*******************************************************************************

 WINBINDER - A native Windows binding for PHP

 Copyright © 2004-2005 Hypervisual - see LICENSE.TXT for details
 Author: Rubem Pechansky (pechansky@hypervisual.com)

 Windows functions

*******************************************************************************/

// TODO: These functions must be ported ("rewritten" is a better term) to C
// so this file will not be necessary in the future

//-------------------------------------------------------------------- CONSTANTS

define("BM_SETCHECK",			241);
define("LVM_FIRST",				0x1000);
define("LVM_DELETEALLITEMS",	(LVM_FIRST+9));
define("LVM_GETITEMCOUNT",		(LVM_FIRST+4));
define("LVM_GETITEMSTATE",		(LVM_FIRST+44));
define("LVM_GETSELECTEDCOUNT",	(LVM_FIRST+50));
define("LVIS_SELECTED",			2);
define("TCM_GETCURSEL",			4875);
define("CB_FINDSTRINGEXACT",	344);
define("CB_SETCURSEL",			334);
define("LB_FINDSTRINGEXACT",	418);
define("LB_SETCURSEL",			390);
define("TCM_SETCURSEL",			4876);

//------------------------------------------------------------- WINDOW FUNCTIONS

/*

Creates a window control, menu, toolbar, status bar or accelerator.

*/

function wb_create_control($parent, $class, $caption="", $xpos=0, $ypos=0, $width=0, $height=0, $id=null, $style=0, $lparam=0)
{
	switch($class) {

		case Accel:
			return wbtemp_set_accel_table($parent, $caption);

		case ToolBar:
			return wbtemp_create_toolbar($parent, $caption, $width, $height, $lparam);

		case Menu:
			return wbtemp_create_menu($parent, $caption);

		default:
			return wbtemp_create_control($parent, $class, $caption, $xpos, $ypos, $width, $height, $id, $style, $lparam);
	}
}

/*

Opens the standard Open dialog box.

*/

function wb_sys_dlg_open($parent=null, $title=null, $filter=null, $path=null, $filename=null)
{
	$filter = _make_file_filter($filter ? $filter : $filename);
	return wbtemp_sys_dlg_open($parent, $title, $filter, $path);
}

/*

Opens the standard Save As dialog box.

*/

function wb_sys_dlg_save($parent=null, $title=null, $filter=null, $path=null, $filename=null)
{
	$filter = _make_file_filter($filter ? $filter : $filename);
	return wbtemp_sys_dlg_save($parent, $title, $filter, $path, $filename);
}

/*

Sets the value of a control, optionally setting also the range of valid values.

*/

function wb_set_value($ctrl, $value, $min=null, $max=null)
{
	if(!$ctrl)
		return null;

	$class = wb_get_class($ctrl);
	switch($class) {

		case ListView:		// Array with items to be checked

			if($value === null)
				break;
			elseif(is_string($value) && strstr($value, ","))
				$values = explode(",", $value);
			elseif(!is_array($value))
				$values = array($value);
			else
				$values = $value;
			foreach($values as $index)
				wbtemp_set_listview_item_checked($ctrl, $index, 1);
			break;

		default:

			// If the control is a spinner 'buddy', should set range of spinner

			if(($min !== null) && ($max !== null))
				wbtemp_set_range($ctrl, $min, $max);	// Must set range before value
			if($value !== null)
				return wbtemp_set_value($ctrl, $value);
	}
}

/*

Gets the text from a control, a control item, or a control sub-item.

*/

function wb_get_text($ctrl, $item=null, $subitem=null)
{
	if(!$ctrl)
		return null;

	if(wb_get_class($ctrl) == ListView) {

		if($item !== null) {
			$line = wbtemp_get_listview_text($ctrl, $item);
			if($subitem === null)
				return $line;
			else
				return $line[$subitem];
		}

		$sel = wb_get_selected($ctrl);
		if($sel === null)
			return null;
		else {
			$items = array();
			foreach($sel as $row)
				$items[] = wbtemp_get_listview_text($ctrl, $row);
			return $items ? $items : null;
		}
	} else
		return wbtemp_get_text($ctrl);
}

/*

Sets the text of a control.
In a ListView, it creates columns: each element of the array text is a column.
In a tab control, it renames the tabs.

*/

function wb_set_text($ctrl, $text, $append=false)
{
	if(!$ctrl)
		return null;

	switch(wb_get_class($ctrl)) {

		case ListView:

			if(!is_array($text))
				$text = explode(",", $text);

			if(!$append)
				wbtemp_clear_listview_columns($ctrl);

			// Creates column headers
			// In the loop below, passing -1 as the last argument of wbtemp_create_listview_column()
			// makes it calculate the column width automatically

			for($i = 0; $i < count($text); $i++) {
				if(is_array($text[$i]))
					wbtemp_create_listview_column($ctrl, $i,
					  (string)$text[$i][0],
					  isset($text[$i][1]) ? (int)$text[$i][1] : -1);
				else
					wbtemp_create_listview_column($ctrl, $i,
					  (string)$text[$i], -1);
			}
			break;

		case ListBox:

			if(!$text && !$append) {
				wb_delete_items($ctrl);
			} elseif(is_string($text)) {
				if(strchr($text, "\r") || strchr($text, "\n")) {
					$text = preg_split("/[\r\n,]/", $text);
					if(!$append)
						wb_delete_items($ctrl);
					foreach($text as $str)
						wbtemp_create_item($ctrl, (string)$str);
				} else {
					$index = wb_send_message($ctrl, LB_FINDSTRINGEXACT, -1, wb_get_address($text));
					wb_send_message($ctrl, LB_SETCURSEL, $index, 0);
				}
			} elseif(is_array($text)) {
				if(!$append)
					wb_delete_items($ctrl);
				foreach($text as $str)
					wbtemp_create_item($ctrl, (string)$str);
			}
			return;

		case ComboBox:

			if(!$text)
				wb_delete_items($ctrl);
			elseif(is_string($text)) {
				if(strchr($text, "\r") || strchr($text, "\n")) {
					$text = preg_split("/[\r\n,]/", $text);
					wb_delete_items($ctrl);
					foreach($text as $str)
						wbtemp_create_item($ctrl, (string)$str);
				} else {
					$index = wb_send_message($ctrl, CB_FINDSTRINGEXACT, -1, wb_get_address($text));
					wb_send_message($ctrl, CB_SETCURSEL, $index, 0);
				}
			} elseif(is_array($text)) {
				wb_delete_items($ctrl);
				foreach($text as $str)
					wbtemp_create_item($ctrl, (string)$str);
			}
			return;

		case TreeView:

			if(!is_array($text))
				return wbtemp_set_current_treeview_item($ctrl, $text);

			// It's an array: fill it up with data

			wb_delete_items($ctrl);

			for($i = 0; $i < count($text); $i++) {
				$item = $text[$i];
				$level = substr_count($item, "\t");
//				echo "$level  [$item]\n";
				wbtemp_create_treeview_item($ctrl,
				  trim($item), $level, -1, -1);
			}

/*
			for($i = 0; $i < count($text); $i++) {
				$item = $text[$i];
				$level = substr_count($item, "\t");
				echo "$level  [$item]\n";
				wb_create_items($ctrl, array($level, trim($item), 2, 3));
				$lastlevel = $level;
			}
*/

			return;

		default:
			if(is_array($text))
				return null;
			$text = str_replace("\r", "", (string)$text);
			$text = str_replace("\n", "\r\n", $text);
			return wbtemp_set_text($ctrl, $text);
	}
}

/*

Selects one or more items. Compare with wb_set_value() which checks items instead.

*/

function wb_set_selected($ctrl, $selitems, $selected=TRUE)
{
	switch(wb_get_class($ctrl)) {

		case Menu:

			return wbtemp_set_menu_item_selected($ctrl, $selected);

		case ListView:

			if(is_null($selitems)) {
				return wbtemp_select_all_listview_items($ctrl, false);
			} elseif(is_array($selitems)) {
				foreach($selitems as $item)
					wbtemp_select_listview_item($ctrl, $item, $selected);
				return TRUE;
			} else
				return wbtemp_select_listview_item($ctrl, $selitems, $selected);
			break;

		case ListBox:
			wb_send_message($ctrl, LB_SETCURSEL, (int)$selitems, 0);
			break;

		case ComboBox:
			wb_send_message($ctrl, CB_SETCURSEL, (int)$selitems, 0);
			break;

		case TabControl:
			wbtemp_select_tab($ctrl, (int)$selitems);
			break;

		default:

			return wb_set_value($ctrl, (bool)$selected);
	}
}

/*

Creates one or more items in a control.

*/

function wb_create_items($ctrl, $items, $clear=false, $param=null)
{
	switch(wb_get_class($ctrl)) {

		case ListView:

			if($clear)
				wb_send_message($ctrl, LVM_DELETEALLITEMS, 0, 0);

			$last = -1;

			// For each row

			for($i = 0; $i < count($items); $i++) {
				if(!is_scalar($items[$i]))
					$last = wbtemp_create_listview_item(
						$ctrl, -1, 0, (string)$items[$i][0]);
				else
					$last = wbtemp_create_listview_item(
						$ctrl, -1, 0, (string)$items[$i]);
				wbtemp_set_listview_item_text($ctrl, -1, 0, (string)$items[$i][0]);

				// For each column except the first

				for($sub = 0; $sub < count($items[$i]) - 1; $sub++) {
					if($param) {
						$result = call_user_func($param, 	// Callback function
							$items[$i][$sub + 1],			// Item value
							$i,								// Row
							$sub							// Column
						);
						wbtemp_set_listview_item_text($ctrl, $last, $sub + 1, $result);
					} else
						wbtemp_set_listview_item_text($ctrl, $last, $sub + 1, (string)$items[$i][$sub + 1]);
				}
			}
			return $last;
			break;

		case TreeView:

			if($clear)
				$handle = wb_delete_items($ctrl); // Empty the treeview

			if(!$items)
				break;
			for($i = 0; $i < count($items); $i++) {
				@wbtemp_create_treeview_item($ctrl,
				  (string)$items[$i][1],	// Name
				  $items[$i][0],			// Level
				  (int)$items[$i][2] != 0 ? (int)$items[$i][2] : -1,
				  (int)$items[$i][3] != 0 ? (int)$items[$i][3] : -1);
			  }
			break;

		default:

			if(is_array($items))
				foreach($items as $item)
					wbtemp_create_item($ctrl, $item);
			else
				wbtemp_create_item($ctrl, $items);
			break;
	}
}

/*

Sets the value of a control item.

*/

function wb_set_item($ctrl, $index, $item)
{
	switch(wb_get_class($ctrl)) {

		case ListView:

			if($index === null || !wb_get_text($ctrl, $index, 0)) {

				// New item

				wb_create_items($ctrl, is_array($item[0]) ? $item : array($item));

			} else {

				// Item already exists

				for($sub = 0; $sub < count($item) - 1; $sub++) {
					wbtemp_set_listview_item_text($ctrl, $index, $sub + 1,
					  (string)$item[$sub + 1]);
				}
			}
			break;
	}
}

/*

Sets the image (bitmap or icon) of a control.

*/

function wb_set_item_image($ctrl, $index, $image)
{
	if(!$ctrl)
		return null;

	if(wb_get_class($ctrl) == ListView) {
		if(is_array($index)) {
			$item = $index[0];
			$subitem = $index[1];
			wbtemp_set_listview_item_image($ctrl, $item, $subitem, $image);
		} else
			wbtemp_set_listview_item_image($ctrl, $index, 0, $image);
	}
}

//----------------------------------------- AUXILIARY FUNCTIONS FOR INTERNAL USE

/*

Creates a file filter for Open/Save dialog boxes based on an array.

*/

function _make_file_filter($filter)
{
	if(!$filter)
		return "All Files (*.*)\0*.*\0\0";

	if(is_array($filter)) {
		$result = "";
		foreach($filter as $line)
			$result .= "$line[0] ($line[1])\0$line[1]\0";
		$result .= "\0";
		return $result;
	} else
		return $filter;
}

//-------------------------------------------------------------------------- END

?>
