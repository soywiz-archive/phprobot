<?php
	Import('Grf.Grf');

	$grf = new Grf('Test.grf');

	print_r($grf);

	echo $grf->Read('data\\test.txt');
?>