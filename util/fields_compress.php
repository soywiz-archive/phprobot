<?php
	extension_loaded('bz2') or dl('php_bz2.dll') or die('Se requiere la extensión bz2');

	$dir = '../maps/';
	foreach (scandir($dir) as $file) {
		if (strtolower(substr($file, strrpos($file, '.'))) == '.fld') {
			echo "{$file}...";
			if (!file_exists($file2 = $dir . $file . '.bz2')) {
				//echo "$file\n";
				file_put_contents($file2, bzcompress(file_get_contents($dir . $file), 9));
				echo 'Ok';
			} else {
				echo 'Exists';
			}
			echo "\n";
		}
	}
?>