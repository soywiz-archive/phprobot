<?php
	extension_loaded('bz2') or dl('php_bz2.dll') or die('Se requiere la extensión bz2');

	$dir = '../maps/';
	foreach (scandir($dir) as $file) {
		if (strtolower(substr($file, strpos($file, '.'))) == '.map.bz2') {
			$file3 = substr($file, 0, strpos($file, '.')) . '.map';
			echo "{$file}...";
			if (!file_exists($file2 = $dir . $file3)) {
				//echo "$file\n";
				file_put_contents($file2, bzdecompress(file_get_contents($dir . $file)));
				echo 'Ok';
			} else {
				echo 'Exists';
			}
			echo "\n";
		}
	}
?>