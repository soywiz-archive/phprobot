<?php
	Import('Grf.Grf');

    //$grf = new Grf('Test.grf');
    $grf = new Grf('e:\\juegos\\ragnarok\\sprodata.grf');

	print_r($grf);

	echo $grf->Read('data\\test.txt');

	echo "\n";

	print_r($grf->Find('*.spr'));
?>
