<?php
	if (!extension_loaded('grf'))
		if (!dl('php_grf.dll'))
			trigger_error("Grf extension could not be loaded.\n", E_USER_ERROR);
?>