<?php
    Import('Winbinder.Winbinder');
    Import('Winbinder.FreeImage');

    define('MW_FRAME', 1001);

	$dib = FreeImage_Load(FIF_PNG, 'e:\\lunea\\data\\maps_img\\alberta.png');
	list($width, $height) = array(FreeImage_GetWidth($dib), FreeImage_GetHeight($dib));
	$mainwin = wb_create_window(NULL, PopupWindow, "Map", $width + 5, $height + 27);

	$bmp = wb_create_image($width, $height, FreeImage_GetInfoHeader($dib), FreeImage_GetBits($dib));
	FreeImage_Unload($dib);

	$frame = wb_create_control($mainwin, Frame, "", 0, 0, $width, $height, 101, WBC_IMAGE);
	wb_set_image($frame, $bmp);
	wb_destroy_image($bmp);


	wb_main_loop();
?>
