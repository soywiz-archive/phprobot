<?php
class WbWidget extends WbObject{
	Function __construct(){
		parent::__construct();
	}
	Function __get($var){
		switch($var){
			case "visible":
				$ret = wb_get_visible($this->address);
				break;
			case "text":
				$ret = wb_get_text($this->address);
				break;
			case "width":
				$ret = wb_get_size($this->address);
				$ret = $ret[0];
				break;
			case "height":
				$ret = wb_get_size($this->address);
				$ret = $ret[1];
				break;
			case "left":
				$ret = wb_get_position($this->address);
				$ret = $ret[0];
				break;
			case "top":
				$ret = wb_get_position($this->address);
				$ret = $ret[1];
				break;
			default:
				$ret = parent::__get($var);
		}
		return $ret;
	}
	Function __set($var, $value){
		switch($var){
			case "visible":
				wb_set_visible($this->address, $value);
				break;
			case "text":
				wb_set_text($this->address, $value);
				break;
			case "width":
				wb_set_size($this->address, $value, $this->height);
				break;
			case "height":
				wb_set_size($this->address, $this->width, $value);
				break;
			case "left":
				wb_set_position($this->address, $value, $this->top);
				break;
			case "top":
				wb_set_position($this->address, $this->left, $value);
				break;
			default:
				parent::__set($var, $value);
		}
	}
	Function refresh(){
		return wb_refresh($this->address);
	}
	Function sendMessage($message, $wparam, $lparam){
		return wb_send_message($this->address, $message, $wparam, $lparam);
	}
	Function setFocus(){
		return wb_set_focus($this->address);
	}
	Function destroy(){
		// echo ">> Called destroy()\n";
		/**
			Particularity: Once the window is destroyed, the functions
			in the Winbinder DLL won't be accessible anymore, so we can
			use the "function_exists" to verify if the window is already
			destroyed.
		**/
		if(function_exists('wb_destroy_window'))
			return wb_destroy_window($this->address);
		else
			return false;

	}
	Function setImage($source, $index=false, $transparentColor=false, $lparam=false){
		/**
			Can be called in two different ways:
				setImage(WbImage $source);
				setImage(string  $source, $index=false, $transparentcolor=false, $lparam=false)
		**/
		return (!is_string($source))?
			wb_set_image($this->address, $source->address):
			wb_set_image($this->address, $source, $index, $transparentColor, $lparam);
	}
	Function setSize($width, $height){
		return wb_set_size($this->address, $width, $height);
	}
	Function setPosition($top, $left){
		return wb_set_position($this->address, $top, $left);
	}
	Function getSize(){
		return wb_get_size($this->address);
	}
	Function getPosition(){
		return wb_get_position($this->address);
	}
	Function setStyle($style){
		return wb_set_style($this->address, $style);
	}
	Function connect($signal, $handler, $param=false){
		global $__ooiSignalTable,$__ooiReservedIDs,$__ooiSignals;

		if(!Wb::isSignalAccepted($this, $signal)){
			echo "OOi Warning: Signal '$signal' is not accepted by this widget.\r\n";
			return false;
		}

		if(isset($this->parentObject)){
			// This is a control, wich has a parent.
			$parent = $this->parentObject;

			// Auto-id
			if(!$id = $this->id){
				$id = $__ooiReservedIDs++;
				$this->id = $id;
			}
		}
		else{
			// This is a window, and has no parent.
			$parent = $this;
			$id = 0;
		}

		{ // Enable the requested event for that 'parent' window.
			if($__ooiSignals[$signal] and !($parent->lparam&$__ooiSignals[$signal])){
				if(!($this->style&WBC_NOTIFY))
					$this->style = $this->style | WBC_NOTIFY;
				// echo "Enabling requested event ($signal):\n";
				// echo "Before: " . $parent->lparam . "\n";
				$parent->lparam = $parent->lparam | $__ooiSignals[$signal];
				// echo "After: "  . $parent->lparam . "\n";
			}

			if($parent === $this && $__ooiSignals[$signal] == WBC_DBLCLICK && !($parent->style&$__ooiSignals[$signal])){
				$parent->style = $parent->style | WBC_DBLCLICK;
				// echo "Enabled dialog double-click\n";
			}
		}

		$__ooiSignalTable[$parent->address][$id][$signal] = Array("widget"=>$this, "handler"=>$handler, "param"=>$param);
		return true;
	}
}
