<?php
abstract class WbContainer extends WbControl{
	public Function addItems($items, $clear=false, $param=false){
		return wb_create_items($this->address, $items, $clear, $param);
	}
	public Function deleteItems($startItem, $length){
		return wb_delete_items($this->address, $startItem, $length);
	}
	public Function getItemCount(){
		return wb_get_item_count($this->address);
	}
	public Function setItem($index, $item){
		return wb_set_item($this->address, $index, $item);
	}
	public Function setItemImage($index, $image){
		return wb_set_item_image($this->address, $index, $image);
	}
	public Function sort($ascending=false, $subitem=false){
		return wb_sort($this->address, $ascending, $subitem);
	}
}

final class WbListView extends WbContainer{
	public Function __construct(wbWindow $parent, $posx=false, $posy=false, $width=false, $height=false, $cols=false, $id=false, $style=false, $lparam=false){
		parent::__construct($parent, ListView, "", $posx, $posy, $width, $height, $id, $style, $lparam);
		$this->text = $cols;
	}
}
final class WbTreeView extends WbContainer{
	public Function __construct(wbWindow $parent, $posx=false, $posy=false, $width=false, $height=false, $id=false, $style=false, $lparam=false){
		parent::__construct($parent, TreeView, '', $posx, $posy, $width, $height, $id, $style, $lparam);
	}
}
