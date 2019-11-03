<?php
class WbToolbar extends WbControl{
	private $pre_built;
	Function __construct($parent){
		$this->pre_built["parent"] = $parent;
	}
	Function setIconPallete($filename, $width, $height){
		$this->pre_built["filename"] = $filename;
		$this->pre_built["width"]    = $width;
		$this->pre_built["height"]   = $height;
	}
	Function addButton($id=false, $description=false, $icon_index=false){
		global $__ooiReservedIDs;
		if(is_string($id) || is_array($id)){
			$action = $id;
			$id = $__ooiReservedIDs++;
			Wb::connectEvent($this->pre_built["parent"], $id, $action);
		}
		if($description == false)
			$this->pre_built["items"][] = NULL;
		else
			$this->pre_built["items"][] = Array($id, NULL, $description, $icon_index);
	}
	Function finished(){
		$this->address = wbtemp_create_toolbar(
			$this->pre_built["parent"]->address,
			$this->pre_built["items"],
			$this->pre_built["width"],
			$this->pre_built["height"],
			$this->pre_built["filename"]
		);
	}
}
