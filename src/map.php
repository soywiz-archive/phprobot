<?php
	$map = new Map('prueba', 5, 5);

	//print_r($map);

	//$map->width = 200;
	//$map->height = 200;

	$map->Find(0, 0, 10, 11, FIND_WALK);

	print_r($map);

	//echo $grf->Read('data\\mapnametable.txt');

	//sleep(20);
?>