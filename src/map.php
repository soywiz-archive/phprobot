<?php
	Import('Map.MapRagnarok');

	$map = new MapRagnarok('alberta.gat');

	print_r($map->Find(60, 100, 100, 100, FIND_WALK));
?>