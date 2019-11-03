<?php
	extension_loaded('gd') or dl('php_gd2.dll') or die('Se requiere php_gd2.dll');

	$path = '../maps';
	$pathi = '../maps_img';
	$map_water_weights = array();

	foreach (scandir($path) as $file) {
	//foreach (array('anthell01', 'anthell02', 'force_map1', 'force_map2', 'job_hunter', 'job_sword1', 'moc_fild08', 'moc_fild09', 'moc_fild12', 'moc_fild13', 'moc_fild17', 'pay_arche') as $file) { $file .= '.map';
		if ($file != '.' && $file != '..') {
			$ext = (($p = strrpos($file, '.')) !== false) ? strtolower(trim(substr($file, $p + 1))) : '';
			if ($ext == 'map') {
				list($mname) = explode('.', $file, 2); $mname = strtolower(trim($mname));
				$aname = $path . '/' . $file;
				echo "$mname...";

				if ($fd = fopen($aname, 'rb')) {
					$v = array();
					$l = unpack('Sw/Sh', fread($fd, 4));
					list($w, $h) = array($l['w'], $l['h']);

					$i = imageCreate($w, $h);
					$v[0]  = ImageColorAllocate($i, 0xff, 0xff, 0xff); // Blanco
					$v[1]  = ImageColorAllocate($i, 0x00, 0x00, 0x00); // Negro
					$v[3]  = ImageColorAllocate($i, 0x00, 0x00, 0xff); // Amarillo
					$v[5]  = ImageColorAllocate($i, 0xff, 0x00, 0x00); // Rojo
					$v[6]  = ImageColorAllocate($i, 0xff, 0xff, 0x00); // Amarillo

					$v[-1] = ImageColorAllocate($i, 0x00, 0xff, 0x00); // Verde

					///////////////////////////////////
					// 0 -> Pasable
					// 1 -> Bloqueado
					// 3 -> Agua
					// 5 -> Atravesar paredes
					// 6 -> Cosa rara
					///////////////////////////////////


					for ($y = 0; $y < $h; $y++) {
						for ($x = 0; $x < $w; $x++) {
							$c = ord(fread($fd, 1));
							if (!isset($v[$c])) $c = -1;
							imageSetPixel($i, $x, $h - $y - 1, $v[$c]);
						}
						usleep(200);
					}
					fclose($fd);

					imagePng($i, $pathi . '/' . $mname . '.png');
				}

				echo "Ok\n";
			}
		}
	}

?>