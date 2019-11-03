<?php
class WbListBoxes extends WbControl{
	private $options;

	public Function __construct($parent, $type, $options=false, $posx=false, $posy=false, $width=120, $height=80, $id=false, $style=false, $lparam=false){
		parent::__construct($parent, $type, '', $posx, $posy, $width, $height, $id, $style, $lparam);
		$this->addOption($options);
	}
	public Function __get($var){
		switch($var){
			case "selectedValue": return $this->getSelectedValue();
			case "selectedDesc":  return $this->getSelectedDesc();
		}
		return parent::__get($var);
	}
	public Function __set($var, $val){
		switch($var){
			case "selectedValue":
				$selected = Array();
				foreach($this->options as $index=>$opt){
					if($opt[0] == $val){
						$var = "selected";
						$val = $index;
						break;
					}
				}
				break;
			case "selectedDesc":
				$selected = Array();
				foreach($this->options as $index=>$opt){
					if($opt[1] == $val){
						$var = "selected";
						$val = $index;
						break;
					}
				}
				break;
		}
		return parent::__set($var, $val);
	}

	public Function addOption($value, $inner=false){
		if(!$value)
			return;
		if(is_array($value))
			foreach($value as $op)
				if(is_array($op))
					$toadd[] = Array($op[0], $op[1]);
				else
					$toadd[] = Array($op, $op);
		else
			$toadd[] = Array($value, $inner?$inner:$value);

		foreach($toadd as $ta)
			$this->options[] = $ta;

		$this->refreshOptions();
	}
	public Function deleteOption($index, $length=1){
		if(!isset($this->options[$index]))
			return false;
		array_splice($this->options, $index, $length);
		$this->refreshOptions();
	}
	public Function clearOptions(){
		$this->options = Array();
		$this->refreshOptions();
	}
	public Function refreshOptions(){
		$output = "";
		if(!sizeof($this->options))
			return;
		foreach($this->options as $op)
			$output .= "\n" . $op[1];
		$this->text = $output;
	}
	public Function sort($ascending=false){
		$ascending?sort($this->options):asort($this->options);
		$this->refreshOptions();
	}
	private Function getSelectedValue(){
		$si = $this->__get("selected");
		return ($si<0)?$si:$this->options[$si][0];
	}
	private Function getSelectedDesc(){
		$si = $this->__get("selected");
		return ($si<0)?$si:$this->options[$si][1];
	}
}

$_WbTemp["UseClasses"] = Array(
	"ComboBox",
	"ListBox",
);
foreach($_WbTemp["UseClasses"] as $_WbTemp["ActualClass"]){
	$_WbTemp["temp"]  = 'final class Wb' . $_WbTemp["ActualClass"] . ' extends WbListBoxes{' . "\n";
	$_WbTemp["temp"] .= '	public Function __construct($parent, $posx=false, $posy=false, $width=false, $height=false, $caption=false, $id=false, $style=false, $lparam=false){' . "\n";
	$_WbTemp["temp"] .= '		parent::__construct($parent, ' . $_WbTemp["ActualClass"] . ', $caption, $posx, $posy, $width, $height, $id, $style, $lparam);' . "\n";
	$_WbTemp["temp"] .= '	}' . "\n";
	$_WbTemp["temp"] .= '}' . "\n";
	eval($_WbTemp["temp"]);
}
unset($_WbTemp);
