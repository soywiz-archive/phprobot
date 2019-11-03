<?php
final class WbRadioButton extends WbControl{
	// Virtual attributes:
	//   checked
	public Function __construct($parent, $posx=false, $posy=false, $width=120, $height=80, $caption=false, $id=false, $style=false, $lparam=false){
		parent::__construct($parent, RadioButton, $caption, $posx, $posy, $width, $height, $id, $style, $lparam);
	}
	public Function __get($var){
		return parent::__get(($var=="checked"?"value":$var));
	}
	public Function __set($var, $val){
		return parent::__set(($var=="checked"?"value":$var), $val);
	}
}
