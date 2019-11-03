<?php
class WbMenu extends WbControl{
	private $pre_built;
	Function __construct($parent){
		$this->pre_built["parent"] = $parent;
	}
	Function addItem($id=false, $text=false, $image=false, $accel=false){
		global $__ooiReservedIDs;
		if(is_string($id) && $text === false)
			$it = $id;
		else{
			$it = new WbMenuItem($this);
			if(is_string($id) || is_array($id)){
				$action = $id;
				$id = $__ooiReservedIDs++;
				Wb::connectEvent($this->pre_built["parent"], $id, $action);
			}
			$it->id = $id;
			if(is_array($text)){
				$it->text = $text[0] . ($accel?"\t".$accel:'');
				$it->hint = $text[1];
			}
			else{
				$it->text = $text . ($accel?"\t".$accel:'');
				$it->hint = null;
			}
			$it->image = $image;
			$it->accel = $accel;
		}
		return $this->pre_built["items"][] = $it;
	}
	Function finished(){
		// Mount the 'caption', to build the menu
		for($x = 0; $x < sizeof($this->pre_built["items"]); $x++){
			$it = $this->pre_built["items"][$x];
			if(!is_string($it))
				$caption[] = Array($it->id, $it->text, $it->hint, $it->image, $it->accel);
			else
				$caption[] = $it;
		}
		$this->address = wbtemp_create_menu(
			$this->pre_built["parent"]->address,
			$caption
		);
		$this->reload();

		// Initialize all the "MenuItems"
		for($x = 0; $x < sizeof($this->pre_built["items"]); $x++)
			if(get_class($this->pre_built["items"][$x]) == "WbMenuItem"){
				$this->pre_built["items"][$x]->__construct($this, true);
			}
	}
}

class WbMenuItem extends WbControl{
	public $parent;
	public $id;
	public $text;
	public $hint;
	public $image;
	public $accel;

	Function __construct($parent, $confirm=false){
		if(!$confirm)
			$this->parent = $parent;
		else{
			$this->address = wb_get_control($this->parent->address, $this->id);
			$this->reload();
		}
	}
	Function __get($var){
		if(!$this->address){
			echo "OOi: You can't get properties from a Menu Item before finish it... Try \$obj->parent->finished().\r\n";
			return false;
		}
		if($var == "checked")
			$var = "value";
		return parent::__get($var);
	}
	Function __set($var, $value){
		if(!$this->address){
			echo "OOi: You can't set properties of a Menu Item before finish it... Try \$obj->parent->finished().\r\n";
			return false;
		}
		if($var == "checked")
			$var = "value";
		return parent::__set($var, $value);
	}
}
