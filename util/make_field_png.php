<?php
	extension_loaded('gd') or dl('php_gd2.dll') or die('Se requiere php_gd2.dll');

	$path = '../maps';
	$pathi = '../maps_img';
	$map_water_weights = array();

	foreach (scandir($path) as $file) {
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
					$v[0]  = ImageColorAllocate($i, 255, 255, 255);
					$v[1]  = ImageColorAllocate($i, 0, 0, 0);
					$v[2]  = ImageColorAllocate($i, 0, 0, 255);
					//$v[5]  = ImageColorAllocate($i, 255, 2, 0);
					$v[-1] = ImageColorAllocate($i, 0, 255, 0);

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