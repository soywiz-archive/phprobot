<?php
class WbObject{
	public $address; // Address to that object (Note that, if this var is null, then the object has not been initialized yet!)
	private $properties; // Ok, all the attributes below will be virtual, so you won't need to 'update()' them
	/**
		These attributes are now virtual.
		Its properties will be stored in $properties array.
		var $handle;
		var $id;
		var $wbclass;
		var $item;
		var $subitem;
		var $style;
		var $parent;
		var $handler;
		var $lparam;
	**/

	Function __construct($address=false){
		if(!$address && !$this->address)
			die("OOi: Can't construct an object without its address.");
		if(!$this->address)
			$this->address = $address;
		$this->reload();
	}
	Function __get($var){
//		echo "Getting 'Properties[$var]'\r\n";
		if(isset($this->properties[$var]))
			return $this->properties[$var];
		return false;
	}
	Function __set($var, $value){
		switch($var){
			case 'handle':
			case 'id':
			case 'wbclass':
			case 'item':
			case 'subitem':
			case 'style':
			case 'parent':
			case 'handler':
			case 'lparam':
//				echo "Setting 'properties[$var] = $value'\r\n";
				$this->properties[$var] = $value;
				$this->update();
				$this->reload();
				break;
			default:
				return false;
		}
		return false;
	}
	/**
		reload($wbObj)
			Imports all variables from memory, and fit them into the class.
	**/
	protected Function reload(){
		$details = unpack(WBOBJ, wb_peek($this->address, WBOBJ_SIZE));
		$this->properties = $details;
	}

	/**
		update()
			Export all variables from this class, to the memory.
	**/
	protected Function update(){
		$pack_format = "";
		$temp = explode("/", WBOBJ);
		for($x = 0; $x < sizeof($temp); $x++)
			$pack_format .= $temp[$x][0];

		//echo "Pack Format: $pack_format\r\n";
		$packed = pack($pack_format,
			$this->properties["handle"],
			$this->properties["id"],
			$this->properties["wbclass"],
			$this->properties["item"],
			$this->properties["subitem"],
			$this->properties["style"],
			$this->properties["parent"],
			$this->properties["handler"],
			$this->properties["lparam"]
		);
		wb_poke($this->address, $packed, WBOBJ_SIZE);
	}
}
