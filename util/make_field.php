<?php
	$path = 'e:/ro-gat/data';
	$map_water_weights = array();

	foreach (file('water_height.txt') as $line) {
		$line = trim($line);
		if (($pos = strpos($line, '//')) !== false) $line = substr($line, 0, $pos);
		if (strlen($line = trim($line))) {
			$data = explode(' ', str_replace("\t", ' ', $line), 2);
			if (sizeof($data) >= 2) {
				list($name) = explode('.', $data[0], 2);

				$map_water_weights[strtolower(trim($name))] = $data[1];
			}
		}
	}

	//print_r($map_water_weights);

	// float high[4]; int type;

	foreach (scandir($path) as $file) {
		if ($file != '.' && $file != '..') {
			$ext = (($p = strrpos($file, '.')) !== false) ? strtolower(trim(substr($file, $p + 1))) : '';
			if ($ext == 'gat') {
				list($mname) = explode('.', $file, 2); $mname = strtolower(trim($mname));
				$aname = $path . '/' . $file;

				echo "$mname...";

				if ($fd = fopen($aname, 'rb')) {
					if ($fw = fopen('../maps/' . $mname . '.map', 'wb')) {
						if (fread($fd, 4) != 'GRAT') { echo "Error\n"; continue; }
						fread($fd, 2);
						$size = unpack('Lw/Lh', fread($fd, 8));
						list($w, $h) = array(&$size['w'], &$size['h']);
						fwrite($fw, pack('SS', $w, $h));

						// 0 -> ??
						// 1 -> ??
						// 3 -> Water
						for ($y = 0; $y < $h; $y++) {
							for ($x = 0; $x < $w; $x++) {
								$line = unpack('f4h/Ltype', fread($fd, 20));
								list($h1, $h2, $h3, $h4, $t) = array(&$line['h1'], &$line['h2'], &$line['h3'], &$line['h4'], &$line['type']);

								if ($t == 0 && isset($map_water_weights[$mname])) {
									$wh = &$map_water_weights[$mname];
									$t = (($h1 > $wh) || ($h2 > $wh) || ($h3 > $wh) || ($h4 > $wh)) ? 2 : 0;
								}

								if ($x <= 0 || $y <= 0 || $x >= $w || $y >= $h) $t = 1;
								if ($t != 0 && $t != 2) $t = 1;

								fwrite($fw, chr($t));
							}
						}

						fclose($fw);
					}

					fclose($fd);
				}

				echo "Ok\n";

			}
		}
	}
?>