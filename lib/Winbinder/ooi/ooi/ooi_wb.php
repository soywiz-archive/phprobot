<?php
class Wb{
/**
	Understanding Wb::parseRc:
		parseRc($filename, $winvar='$w', $classname='WbAppWindow', $width, $height, $posx=WBC_CENTER, $posy=WBC_CENTER, $parent, $style=false, $notify="WBC_GETFOCUS | WBC_DBLCLICK"){
		- filename:  resource file with extension ".rc"
		- winvar:    an array name, wich will hold all the objects created
		- classname: May be any "WbWindow" class. Otherwise, will be considered the
		             container name, where all created objects will be included.
		- width, height: you MUST input it, if you're creating a window.
		                 Don't know why, but the resource doesn't bring it right.
		- posx, posy: If not given, will be centered
		- parent:    May be used if you're trying to create a dialog... Send its name (like '$w["main"]')
		- style:     Style for the window...
		- notify:    Send more types of notification to the window handler? Wich ones?
		             default are "WBC_GETFOCUS" and "WBC_DBLCLICK"
		When Wb::parseRc creates a window, its object handler
		will Always be $winvar["main"].

		Warning:
			Be careful when importing more than one resource file,
			so, one file won't replace other's "define" properties...
			If it occurs, you may experience some problems while
			trying to access a control properties.
**/
	static Function parseRc($filename, $winvar='$w', $classname='WbAppWindow', $width=false, $height=false, $posx=WBC_CENTER, $posy=WBC_CENTER, $parent=false, $style=false, $notify="WBC_GETFOCUS | WBC_DBLCLICK"){
		// Open external file
		$tmp = file_get_contents($filename);
		if(!$tmp)
			return;

		// Use original Winbinder parser:
		if($notify && !($style&WBC_NOTIFY))
			$style |= WBC_NOTIFY;
		$tmp = parse_rc($tmp, $winvar, $parent, $classname, '', $posx, $posy, $width, $height, $style, $notify);

		/**
			Transforming main window
		**/
		$parent = $parent?$parent:"NULL";
		if(ereg("Window", $classname)){
			preg_match("/\\$winvar = wb_create_window\($parent, (.*?), (.*), (.*?), (.*?), (.*?), (.*?), (.*?), (.*?)\)/", $tmp, $output);
			$daddy = "$winvar"."[\"main\"]";
			$torep = "$daddy = new $classname($output[2], $output[3], $output[4], $output[5], $output[6], $output[7], $output[8])";
			preg_match("/\\$winvar = wb_create_window\((.*?), (.*?), (.*), (.*?), (.*?), (.*?), (.*?), (.*?), (.*?)\)/", $tmp, $output);
		}
		elseif(ereg("Dialog", $classname)){
			preg_match("/\\$winvar = wb_create_window\(".preg_quote($parent).", (.*?), (.*), (.*?), (.*?), (.*?), (.*?), (.*?), (.*?)\)/", $tmp, $output);
			$daddy = "$winvar"."[\"main\"]";
			$torep = "$daddy = new $classname($parent, $output[2], $output[3], $output[4], $output[5], $output[6], $output[7], $output[8])";
		}
		else{
			$daddy = $classname;
			$torep = "";
			preg_match("/\\$winvar = wb_create_window\((.*?), (.*?), (.*), (.*?), (.*?), (.*?), (.*?), (.*?), (.*?)\)/", $tmp, $output);
		}
		$tmp = str_replace($output[0], $torep, $tmp);

		/**
			Transforming controls
		**/
		preg_match_all("/wb_create_control\((.*?), (.*?), (.*), (.*?), (.*?), (.*?), (.*?), (.*?), (.*?)\)/", $tmp, $output);
		for($x = 0; $x < sizeof($output[0]); $x++){
			$class = $output[2][$x];
			$caption=$output[3][$x];
			$xpos  = $output[4][$x];
			$ypos  = $output[5][$x];
			$width = $output[6][$x];
			$height= $output[7][$x];
			$id    = $output[8][$x];
			$style = $output[9][$x];
			switch($class){
				// Without 'caption'
				case "TabControl":
				case "TreeView":
					$parms = $winvar."[$id] = $daddy"."->addControl('Wb$class', $xpos, $ypos, $width, $height, $id, $style)";
					break;

				// Only 'parent'
				case "Accel":   // Needs to be initialized via coding
				case "Menu":    // Needs to be initialized via coding
				case "ToolBar": // Needs to be initialized via coding
					$parms = $winvar."[$id] = $daddy"."->addControl('Wb$class')";
					break;

				// All the parameters:
				default:
					$parms = $winvar."[$id] = $daddy"."->addControl('Wb$class', $xpos, $ypos, $width, $height, $caption, $id, $style)";
			}
			$tmp = str_replace($output[0][$x], $parms, $tmp);
		}
		return $tmp;
	}
	static Function generateIni($data, $comments=false){
		return generate_ini($data, $comments);
	}
	static Function getFolderFiles($path, $subdirs=false, $fullname=false, $mask=false){
		return get_folder_files($path, $subdirs, $fullname, $mask);
	}
	static Function parseIni($initext, $changecase=false){
		return parse_ini($initext, $changecase);
	}
	static Function exec($command, $param=false){
		return wb_exec($command, $param);
	}
	static Function findFile($filename){
		return wb_find_file($filename);
	}
	static Function getRegistryKey($key, $subkey, $entry=false){
		return wb_get_registry_key($key, $subkey, $entry);
	}
	static Function getSystemInfo($info){
		return wb_get_system_info($info);
	}
	static Function mainLoop(){
		return wb_main_loop();
	}
	static Function playSound($style){
		return wb_play_sound($style);
	}
	static Function setRegistryKey($key, $subkey, $entry=false, $value=false){
		return wb_set_registry_key($key, $subkey, $entry, $value);
	}
	static Function getAddress($str){
		return wb_get_address($str);
	}
	static Function peek($address, $length=false){
		return wb_peek($address, $length);
	}
	static Function poke($address, $contents, $length=false){
		return wb_poke($address, $contents, $length);
	}
	static Function sendMessage($wbobject, $message, $wparam, $lparam){
		if(!is_int($wbobject))
			$wbobject = $wbobject->address;
		return wb_send_message($wbobject, $message, $wparam, $lparam);
	}
	static Function messageBox($parent, $message, $title=false, $style=false){
		if(is_string($parent)) return wb_message_box(NULL, $parent, $message, $title);
		if(is_object($parent)) return wb_message_box($parent->address, $message, $title, $style);
		else                   return wb_message_box($parent, $message, $title, $style);
	}
	static Function sysDlgColor($parent=false, $title=false, $color=false){
		if(is_string($parent)) return wb_sys_dlg_color(NULL,             $parent, $title);
		if(is_object($parent)) return wb_sys_dlg_color($parent->address, $title,  $color);
		                       return wb_sys_dlg_color($parent,          $title,  $color);
	}
	static Function sysDlgOpen($parent=false, $title=false, $filter=false, $path=false, $filename=false){
		if(is_string($parent)) return wb_sys_dlg_open(NULL,             $parent, $title,  $filter, $path);
		if(is_object($parent)) return wb_sys_dlg_open($parent->address, $title,  $filter, $path, $filename);
		                       return wb_sys_dlg_open($parent,          $title,  $filter, $path, $filename);
	}
	static Function sysDlgPath($parent=false, $title=false, $path=false){
		if(is_string($parent)) return wb_sys_dlg_path(NULL,             $parent, $title);
		if(is_object($parent)) return wb_sys_dlg_path($parent->address, $title,  $path);
		                       return wb_sys_dlg_path($parent,          $title,  $path);
	}
	static Function sysDlgSave($parent=false, $title=false, $filter=false, $path=false, $filename=false){
		if(is_string($parent)) return wb_sys_dlg_save(NULL,             $parent, $title,  $filter, $path  );
		if(is_object($parent)) return wb_sys_dlg_save($parent->address, $title,  $filter, $path, $filename);
		                       return wb_sys_dlg_save($parent,          $title,  $filter, $path, $filename);
	}
	static Function createFont($name, $size, $color=false, $flags=false){
		return wb_create_font($name, $size, $color, $flags);
	}
	static Function loadLibrary($libname){
		return wb_load_library($libname);
	}
	static Function getFunctionAddress($fname, $idlib=false){
		return wb_get_function_address($fname, $idlib);
	}
	static Function callFunction($address, $args=false){
		wb_call_function($address, $args);
	}
	static Function releaseLibrary($idlib){
		wb_release_library ($idlib);
	}
	static Function addressToObject($address, $only_details=false){
		$style_table = Array(
			AppWindow=>"WbAppWindow",
			PopupWindow=>"WbPopupWindow",
			ResizableWindow=>"WbResizableWindow",
			ModalDialog=>"WbModalDialog",
			ModelessDialog=>"WbModelessDialog",
			ToolDialog=>"WbToolDialog",
			Accel=>"WbAccel",
			Calendar=>"WbCalendar",
			CheckBox=>"WbCheckBox",
			ComboBox=>"WbComboBox",
			EditBox=>"WbEditBox",
			Frame=>"WbFrame",
			Gauge=>"WbGauge",
			HTMLEditBox=>"WbHTMLEditBox",
			Label=>"WbLabel",
			LayoutDialog=>"WbLayoutDialog",
			ListBox=>"WbListBox",
			ListView=>"WbListView",
			Menu=>"WbMenu",
			PushButton=>"WbPushButton",
			RadioButton=>"WbRadioButton",
			RTFEditBox=>"WbRTFEditBox",
			ScrollBar=>"WbScrollBar",
			Slider=>"WbSlider",
			Spinner=>"WbSpinner",
			StatusBar=>"WbStatusBar",
			TabControl=>"WbTabControl",
			ToolBar=>"WbToolBar",
			TreeView=>"WbTreeView"
		);
		$details = unpack(WBOBJ, wb_peek($address, WBOBJ_SIZE));
		$wbclass = $details["wbclass"];
		if($only_details){
			$details["object_class"] = $style_table[$wbclass];
			return $details;
		}
		eval("\$return  = new $style_table[$wbclass]($address);");
		return $return;
	}
	static Function isSignalAccepted($widget, $signal){
		// Signals that are currently being emulated by the O.O. Interface.
		//
		// Currently:
		// - WbWindow: destroy, key-pressed, key-released, double-clicked, mouse-down, mouse-up, mouse-move
		// - WbControl: focus-in, clicked
		// - | WbAccel: ..... KISS: keep it simple, stupid! :O ... it will be the same 'clicked'
		// - | WbEditBox: key-pressed [or KISS: clicked]
		// - | WbContainer: double-clicked

		$allow = Array();
		if ($widget instanceof WbWindow)
			$allow = array_merge($allow, Array('destroy', 'key-pressed', 'mouse-down', 'mouse-up', 'double-clicked'));

		if ($widget instanceof WbControl)
			$allow = array_merge($allow, Array('clicked', 'focus-in'));

		if ($widget instanceof WbEditBox)
			$allow = array_merge($allow, Array('key-pressed'));

		if ($widget instanceof WbContainer)
			$allow = array_merge($allow, Array('double-clicked'));

		return in_array($signal, $allow);
	}
	/**
		Connects an ID to an action, without having to create a button.
	**/
	static Function connectEvent($window, $id, $action){
		global $__ooiSignalTable;

		$param = false;
		if(is_array($action)){
			$param  = $action[1];
			$action = $action[0];
		}
		$__ooiSignalTable[$window->address][$id]['clicked'] = Array("widget"=>$window, "handler"=>$action, "param"=>$param);
	}
}
