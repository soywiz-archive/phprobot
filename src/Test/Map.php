<?php
	Import('Map.MapRagnarok');

	/*
	$map = new MapRagnarok('alberta.gat');

	print_r($map->Find(60, 100, 100, 100, FIND_FLY));
	*/

	$map = new MapRagnarok('cmd_fild01');

	//print_r($map->Find(225, 298, 220, 310, FIND_WALK));
	//print_r($map->Find(225, 298, 220, 310, FIND_FLY));

	//print_r($map->FindAttackPosition(225, 298, 220, 310, 1));
	echo 'Atacando de cerca: ' . $map->DistanceToAttack(225, 298, 220, 310, 1) . "\n";
	echo 'Atacando a 20 casillas: ' . $map->DistanceToAttack(225, 298, 220, 310, 20) . "\n";

	//print_r(class_get_methods);
	//print_r(get_class_methods(get_class($map)));
?>