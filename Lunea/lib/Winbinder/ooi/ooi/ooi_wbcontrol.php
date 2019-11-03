<?php
class WbControl extends WbWidget{
	public $parentObject;

	Function __get($var){
		switch($var){
			case "enabled":
				$ret = wb_get_enabled($this->address);
				break;
			case "value":
				$ret = wb_get_value($this->address);
				break;
			case "selected":
				$ret = wb_get_selected($this->address);
				break;
			default:
				$ret = parent::__get($var);
		}
		return $ret;
	}
	Function __set($var, $value){
		switch($var){
			case "enabled":
				wb_set_enabled($this->address, $value);
				break;
			case "value":
				wb_set_value($this->address, $value);
				break;
			case "selected":
				wb_set_selected($this->address, $value);
				break;
			default:
				parent::__set($var, $value);
		}
	}
	Function __construct($parent, $type, $caption=false, $posx=false, $posy=false, $width=false, $height=false, $id=false, $style=false, $lparam=false){
		if(is_int($parent)){
			// Allows OOi to import existing control, into any class
			$this->address = $parent;
			parent::__construct();
			if($this->parent)
				$this->parentObject = Wb::addressToObject($this->parent);
			return;
		}
		if(get_class($parent) == "WbTab"){
			$lparam = $parent->index;
			$parent = $parent->controller;
		}
		$this->parentObject = $parent->parentObject?$parent->parentObject:$parent;
		$this->address = wb_create_control($parent->address, $type, $caption, $posx, $posy, $width, $height, $id, $style, $lparam);
		parent::__construct();
	}
	Function setFont($fontID, $redraw=false){
		wb_set_font($this->address, $fontID, $redraw);
	}
	Function setRange($min, $max){
		wb_set_value($this->address, null, $min, $max);
	}
}

/**
	The new method, consists in evaluating all the possible classes.
	Good bless the "eval" function! =)
**/
$_WbTemp["UseClasses"] = Array(
	"Calendar",
	"EditBox",
	"Frame",
	"Gauge",
	"HTMLEditBox",
	"Hyperlink",
	"Label",
	"PushButton",
	"RTFEditBox",
	"ScrollBar",
	"Slider",
	"Spinner",
	"StatusBar",
);
foreach($_WbTemp["UseClasses"] as $_WbTemp["ActualClass"]){
	$_WbTemp["temp"]  = 'final class Wb' . $_WbTemp["ActualClass"] . ' extends WbControl{' . "\n";
	$_WbTemp["temp"] .= '	public Function __construct($parent, $posx=false, $posy=false, $width=false, $height=false, $caption=false, $id=false, $style=false, $lparam=false){' . "\n";
	$_WbTemp["temp"] .= '		parent::__construct($parent, ' . $_WbTemp["ActualClass"] . ', $caption, $posx, $posy, $width, $height, $id, $style, $lparam);' . "\n";
	$_WbTemp["temp"] .= '	}' . "\n";
	$_WbTemp["temp"] .= '}' . "\n";
	eval($_WbTemp["temp"]);
}
unset($_WbTemp);




















