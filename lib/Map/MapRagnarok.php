<?php
	Import('System.Buffer');

	class MapRagnarok extends Map {
		function __construct($name) {
			list($name) = explode('.', $name, 2);

			// alberta.map.bz2

			$map_name = LUNEA_MAPS . $name . '.map';

			if (file_exists($map_name_r = $map_name)) {
				$data = file_get_contents($map_name_r);
			} else if (file_exists($map_name_r = $map_name . '.bz2')) {
				$data = bzdecompress(file_get_contents($map_name_r));
			} else if (file_exists($map_name . '.gz')) {
				$data = gzuncompress(file_get_contents($map_name_r));
			} else {
				// Aquí se comprobaría en el Gat
				/*
				if (isset($GLOBALS['Gat']) && sizeof($GLOBALS['Gat']) >= 1) {
					global $Gat;
					$gat_data = $Gat->Read('data\\' . $name . '.gat');

					unset($gat_data);
				}
				*/

				throw(new Exception('Map \'' . $name . '\' doesn\'t exists'));
			}

			$width  = ExtR16($data);
			$height = ExtR16($data);

			Map::__construct($data, $width, $height);
		}
	}
?>