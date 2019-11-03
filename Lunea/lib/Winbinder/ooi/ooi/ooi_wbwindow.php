<?php
class WbWindow extends WbWidget{
	public $indestructible;
	Function __construct($type, $parent, $caption=false, $xpos=false, $ypos=false, $width=false, $height=false, $style=false, $lparam=false){
		if(is_int($parent) || is_int($caption)){
			// Allows OOi to import existing control, into any class
			$this->address = $parent?$parent:$caption;
		}
		else{
			if($width)  // ($type, $parent, $caption, $xpos, $ypos, $width, $height, $style, $lparam)
				$this->address = wb_create_window($parent?$parent->address:false, $type, $caption, $xpos, $ypos, ($width?$width:400), ($height?$height:300), $style, $lparam);
			else // ($type, $parent, $caption, $width, $height)
				$this->address = wb_create_window($parent?$parent->address:false, $type, $caption, WBC_CENTER, WBC_CENTER, ($xpos?$xpos:400), ($ypos?$ypos:300));
			wb_set_handler($this->address, '__ooi_proccess_control');
		}
		parent::__construct();
	}
	Function __destruct(){
		if(!$this->indestructible){
			// echo "> Called __destruct()\n";
			$this->destroy();
		}
	}

	Function setHandler($handler){
		$GLOBALS["__ooiDefaultHndl"][$this->address] = $handler;
	}
	Function startTimer($id, $interval){
		return wb_create_timer($this->address, $id, $interval);
	}
	Function destroyTimer($id){
		return wb_destroy_timer($this->address, $id);
	}
	Function getPixel($xpos, $ypos){
		return wb_get_pixel($this->address, $xpos, $ypos);
	}
	Function setPixel($xpos, $ypos, $color){
		return wb_set_pixel($this->address, $xpos, $ypos, $color);
	}
	Function getControl($id){
		if($address = wb_get_control($this->address, $id))
			return Wb::addressToObject($address);
		echo "OOi Error: Failed getting id '$id' from this object.\r\n";
		return false;
	}
	Function addControl($control_name){
		// This function is on 'wbWindow' AND 'wbTab' classes..
		// if you change it in one of them, you'll have to do the same on the other one.
		$param = "";
		$args = func_get_args();
		unset($args[0]);
		foreach($args as $idx=>$arg)
			$param .= ", \$args[$idx]";
		$toeval  = "new $control_name";
		$toeval .= "(\$this$param);";
		eval("\$ret = $toeval");
		if(!isset($ret)){
			echo "Error while evaluating:\r\n";
			echo "=========================\r\n";
			echo $toeval . "\r\n";
			echo "=========================\r\n";
			return false;
		}
		return $ret;
	}
}

/**
	The new class declaration method consists in evaluating all the possible classes.
	Good bless the "eval" function! =)
**/
$_WbTemp["UseClasses"] = Array(
//	Array(className, requireParent)
	Array("AppWindow", false),
	Array("PopupWindow", false),
	Array("ResizableWindow", false),
	Array("ModalDialog", true),
	Array("ModelessDialog", true),
	Array("ToolDialog", true),
);
foreach($_WbTemp["UseClasses"] as $_WbTemp["ActualClass"]){
	$_WbTemp["temp"]  = 'final class Wb' . $_WbTemp["ActualClass"][0] . ' extends WbWindow{' . "\n";
	$_WbTemp["temp"] .= '	public Function __construct(' . ($_WbTemp["ActualClass"][1]?'$parent, ':'') . '$caption=false, $posx=false, $posy=false, $width=false, $height=false, $style=false, $lparam=false){' . "\n";
	$_WbTemp["temp"] .= '		parent::__construct(' . $_WbTemp["ActualClass"][0] . ', ' . ($_WbTemp["ActualClass"][1]?'$parent':'false') . ', $caption, $posx, $posy, $width, $height, $style, $lparam);' . "\n";
	$_WbTemp["temp"] .= '	}' . "\n";
	$_WbTemp["temp"] .= '}' . "\n";
	eval($_WbTemp["temp"]);
}
unset($_WbTemp);
?>
