<?php
final class WbImage{
	public $address;
	public Function __get($var){
		switch($var){
			case "width":
				$ret = wb_get_image_size($this->address);
				$ret = $ret[0];
				break;
			case "height":
				$ret = wb_get_image_size($this->address);
				$ret = $ret[1];
				break;
			default:
				$ret = false;
		}
		return $ret;
	}
	public Function __set($var, $value){
		switch($var){
		//	case "width":
		//		wb_set_image_size($this->address, $value, $this->height);
		//		break;
		//	case "height":
		//		wb_set_image_size($this->address, $this->width, $value);
		//		break;
		}
	}
	public Function __construct($width=false, $height=false){
		if(is_int($width))    $this->create($width, $height);
		if(is_string($width)) $this->load($width, $height)  ; // filename, transparent_index
	}
	public Function __destroy(){
		$this->destroy();
	}
	public Function create($width, $height){
		$this->address = wb_create_image($width, $height);
	}
	public Function createMask($transparentColor){
		return wb_create_mask($this->address, $transparentColor);
	}
	public Function destroy(){
		return wb_destroy_image($this->address);
	}
	public Function load($filename, $index=0){
		$this->address = wb_load_image($filename, $index);
	}
	public Function getImageSize($filename){
		return wb_get_image_size($filename);
	}
	public Function save($filename){
		return wb_save_image($this->address, $filename);
	}
	public Function getPixel($xpos, $ypos){
		return wb_get_pixel($this->address, $xpos, $ypos);
	}
	public Function setPixel($xpos, $ypos, $color){
		return wb_set_pixel($this->address, $xpos, $ypos, $color);
	}
}
