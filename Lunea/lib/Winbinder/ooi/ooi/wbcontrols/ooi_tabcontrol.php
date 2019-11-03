<?php
class WbTabControl extends WbControl{
	public $nexttabindex;
	Function __get($var){
		if($var == "selected")
			return wb_get_selected($this->address);
		return parent::__get($var);
	}
	Function __set($var, $value){
		if($var == "selected"){
			if($value >= $this->nexttabindex)
				echo "WbTabControl error: Trying to select a tab wich doesn't exists...\r\n";
			else
				return wb_set_selected($this->address, $value);
		}
		else
			return parent::__set($var, $value);
	}
	Function __construct($parent, $posx, $posy, $width, $height, $id=false, $style=false, $lparam=false){
		if(!$posx){
			echo ">> ERRO! Sem posicao ($parent).\n";
			return;
		}
		if(get_class($parent) == "WbTab"){
			$lparam = $parent->index;
			$parent = $parent->controller;
		}
		parent::__construct($parent, TabControl, "", $posx, $posy, $width, $height, $id, $style, $lparam);
		$this->nexttabindex = 0;
	}
	Function next(){
		$fut = $this->selected+1;
		if($fut >= $this->nexttabindex)
			$fut = 0;
		$this->selected = $fut;
	}
	Function back(){
		$fut = $this->selected-1;
		if($fut < 0)
			$fut = $this->nexttabindex-1;
		$this->selected = $fut;
	}
	Function end(){
		$this->selected = $this->nexttabindex-1;
	}
	Function first(){
		$this->selected = 0;
	}
}

class WbTab{
	public $index;
	public $controller;
	Function __construct(WbTabControl $parent, $description){
		wbtemp_create_item($parent->address, $description);
		$this->index = $parent->nexttabindex++;
		$this->controller = $parent;
	}
	Function focus(){
		$this->controller->selected = $this->index;
	}
	Function addControl($control_name){
		// This functions is on 'wbWindow' AND 'wbTab' classes..
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
