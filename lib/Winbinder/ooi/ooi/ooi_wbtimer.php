<?php
class WbTimer{
	public $interval;
	public $action;
	public $id;
	public $parent;

	Function __construct($parent=false, $action=false, $interval=1000, $id=false){
		global $__ooiReservedIDs;

		if(!$id)
			$id = $__ooiReservedIDs++;

		$this->parent = $parent;
		$this->action = $action;
		$this->id = $id;
		$this->interval = $interval;
	}
	Function setInterval($ms){
		$this->interval = $ms;
	}
	Function setAction($action){
		$this->action = $action;
	}
	Function start(){
		Wb::connectEvent($this->parent, $this->id, $this->action);
		$this->parent->startTimer($this->id, $this->interval);
	}
	Function stop(){
		$this->parent->destroyTimer($this->id);
	}
}
