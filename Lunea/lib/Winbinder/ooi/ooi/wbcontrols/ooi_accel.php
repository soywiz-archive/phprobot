<?php
class WbAccel extends WbControl{
	private $pre_built;
	Function __construct($parent){
		$this->pre_built["parent"] = $parent;
	}
	Function addAccel($id, $accel){
		global $__ooiReservedIDs;
		if(is_string($id) || is_array($id)){
			$action = $id;
			$id = $__ooiReservedIDs++;
			Wb::connectEvent($this->pre_built["parent"], $id, $action);
		}
		$this->pre_built["table"][] = Array($id, $accel);
	}
	Function finished(){
		wbtemp_set_accel_table($this->pre_built["parent"]->address, $this->pre_built["table"]);
	}
}
