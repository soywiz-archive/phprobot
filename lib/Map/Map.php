<?php
	if (!extension_loaded('map'))
		if (!dl('php_map.dll'))
			trigger_error("Map extension could not be loaded.\n", E_USER_ERROR);
?>