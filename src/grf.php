<?php
	$grf = new Grf('c:\\juegos\\ragnarok\\sprodata.grf');

	print_r($grf);

	echo $grf->Read('data\\mapnametable.txt');

	//sleep(20);
?>