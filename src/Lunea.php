<?php
    require_once(dirname(dirname(__FILE__)) . '/Bots/TestBot/TestBot.php');

	$Bot = new TestBot();

	Import('Winbinder.winbinder');
	Import('Winbinder.FreeImage');

	$cmd = 1001;
	define('ID_APP_TIMER',           $cmd++);
	define('IDC_RESULT',             $cmd++);
	define('ID_SELECT',              $cmd++);
	define('ID_VIEW_MAP',            $cmd++);
	define('ID_WEBSITE_LUNEA',       $cmd++);
	define('ID_WEBSITE_WINBINDER',   $cmd++);
	define('ID_WEBSITE_PHP',         $cmd++);
	define('ID_ABOUT',               $cmd++);

    define('MW_FRAME',               1001);

	$mainwin = wb_create_window(NULL, 101, 'Lunea', WBC_CENTER, WBC_CENTER, 320, 262, WBC_VISIBLE);
	//$mainwin = wb_create_window(NULL, PopupWindow, APPNAME, WBC_CENTER, WBC_CENTER, 300, 200, WBC_NOTIFY | WBC_DBLCLICK | WBC_MOUSEDOWN | WBC_MOUSEUP | WBC_MOUSEMOVE, 0);
	wb_set_image($mainwin, dirname(dirname(__FILE__)) . '/bin/lunea.ico');
	wb_set_visible($mainwin, true);

    $mapwin = wb_create_window(NULL, PopupWindow, "Map", WBC_CENTER, WBC_CENTER, 200, 200, WBC_INVISIBLE | WBC_MOUSEDOWN | WBC_MOUSEUP, 0);
    wb_set_visible($mapwin, false);
    $mapwinframe = wb_create_control($mapwin, Frame, '', 0, 0, 50, 50, 101, WBC_IMAGE);
	wb_set_handler($mapwin, 'process_map');
	wb_set_image($mapwin, dirname(dirname(__FILE__)) . '/bin/lunea.ico');

	$Box = wb_create_control($mainwin, EditBox, "", 0, 0, 313, 213, IDC_RESULT, WBC_VISIBLE | WBC_ENABLED | WBC_LINES);

	$mainmenu = wb_create_control($mainwin, Menu, array(
	"&File",
		array(ID_SELECT,	"Select &Bot...\tCtrl+B", '', "", "Ctrl+B"),
		null,
		//array(IDCLOSE,		"E&xit\tAlt+F4", '', PATH_RES . "menu_exit.bmp"),
		array(IDCLOSE,		"E&xit\tAlt+F4", '', ''),
	"&View",
		array(ID_VIEW_MAP,	"&Map...\tCtrl+M", '', '', 'Ctrl+M'),
	"&Help",
		array(ID_WEBSITE_LUNEA,	"&Web site of Lunea..."),
		null,
		array(ID_WEBSITE_WINBINDER,	"&Web site of Winbinder..."),
		array(ID_WEBSITE_PHP,	"&Web site of Php..."),
		null,
		//array(ID_ABOUT,		"&About...", "", PATH_RES . "menu_help.bmp"),
		array(ID_ABOUT,		"&About...", "", ''),
	), $mainwin);

	// WBC_CHECKBOXES

	wb_set_handler($mainwin, 'process_main');
    wb_create_timer($mainwin, ID_APP_TIMER, 50);
    //wb_create_timer($mainwin, ID_APP_TIMER, 1000);
	wb_main_loop();

	function process_main($window, $id, $ctrl = 0, $lparam = 0, $lparam2 = 0) {
		global $Bot;
		global $Box;

		switch ($id) {
			case 0:
				echo "$ctrl, $lparam, $lparam2\n";
			break;
			case ID_APP_TIMER:
				// Show the current time in hours, minutes and seconds
				//wb_set_text($window, date("h:i:s A"));
				ob_start();
                $data = '';
                try {
                    $Bot->Check();
                } catch (Exception $e) {
                    $data .= "Exception " . $e . "\n" . $e->getTraceAsString();
                }
                $data .= ob_get_contents();
				ob_end_clean();
				if (strlen($data)) {
					$sl = strlen($set = wb_get_text($Box) . $data);
					wb_set_text($Box, $set);
					wb_refresh($Box, 1);
				}
				update_map_points($Bot);
				//$Bot->EntityList->Dump();
				//echo $Bot->Id . "/\n";
			break;
			case ID_VIEW_MAP:
				global $mapwin;
				map_image('prontera');
				map_show(!wb_get_visible($mapwin));
			break;
			case ID_WEBSITE_LUNEA:
				if (!wb_exec("http://phprobot.sourceforge.net/"))
					wb_message_box($window, "Problem opening web site.");
			break;
			case ID_WEBSITE_WINBINDER:
				if (!wb_exec("http://winbinder.sourceforge.net/"))
					wb_message_box($window, "Problem opening web site.");
			break;
			case ID_WEBSITE_PHP:
				if (!wb_exec("http://www.php.net/"))
					wb_message_box($window, "Problem opening web site.");
			break;
			case IDCLOSE:
				wb_destroy_window($window);
			break;
		}
	}

	function update_map_points(GenericBot $Bot) {
		global $mapwinframe, $reset_map;

		if (!isset($reset_map)) $reset_map = array();

		foreach ($reset_map as $m) {
			list($rx, $ry, $Size, $data) = $m;

			for ($y = 0; $y < $Size; $y++) {
				for ($x = 0; $x < $Size; $x++) {
					wb_set_pixel($mapwinframe, $rx + $x, $ry + $y, $data[$y][$x]);
				}
			}
		}

		$reset_map = array();
		foreach ($Bot->EntityList->ListId as $Entity) {
			if (!isset($Entity->Position)) continue;
			$Pos = $Entity->Position;
			if (!($Pos instanceof Position)) continue;
			$RPos = map_conversion($Pos);

			draw_point_in_map($RPos, 2, RED);
		}
	}

	function draw_point_in_map(Position $Pos, $Size, $Type) {
		global $mapwinframe, $reset_map;

		$rx = $Pos->X - ($Size << 1);
		$ry = $Pos->Y - ($Size << 1);

		$data = array();
		for ($y = 0; $y < $Size; $y++) {
			$data[$y] = array();
			for ($x = 0; $x < $Size; $x++) {
				$data[$y][$x] = wb_get_pixel($mapwinframe, $rx + $x, $ry + $y);
			}
		}
		$reset_map[] = array($rx, $ry, $Size, $data);

		wb_draw_rect(
			$mapwinframe,
			$rx,
			$ry,
			$Size,
			$Size,
			$Type,
			true
		);
	}

	function process_map($window, $id, $ctrl = 0, $lparam = 0, $lparam2 = 0) {
		global $Bot;
		global $Box;

		switch($id) {
			case 0:
				$left = ($lparam & WBC_LBUTTON);
				$down = ($lparam & WBC_MOUSEDOWN);
				$x = $lparam2 & 0xFFFF;
				$y = ($lparam2 & 0xFFFF0000) >> 16;

				if ($down && $left) {
					SendZoneMove($Bot, map_conversion(new Position($x, $y)));
					echo "$x, $y\n";
				}
			break;
			case IDCLOSE:
				map_show(false);
			break;
		}
	}

	function map_image($map) {
		global $mapwin, $mapwinframe, $mapwidth, $mapheight;

		list($base) = explode('.', basename($map), 2);

		$file = LUNEA_MAPS_IMG . '/' . $base . '.png';

		if (!file_exists($file)) {
		}

		$dib = FreeImage_Load(FIF_PNG, $file);
		list($width, $height) = array(FreeImage_GetWidth($dib), FreeImage_GetHeight($dib));

		$mapwidth = $width; $mapheight = $height;

		wb_set_size($mapwin, $width + 5, $height + 27);
		wb_set_size($mapwinframe, $width, $height);
		wb_set_position($mapwin);

		$bmp = wb_create_image($width, $height, FreeImage_GetInfoHeader($dib), FreeImage_GetBits($dib));
		FreeImage_Unload($dib);

		//$theimage = $bmp;
		wb_set_image($mapwinframe, $bmp);
		wb_destroy_image($bmp);
	}

	function map_show($show = true) {
		global $mapwin;
		wb_set_visible($mapwin, $show);
	}

	function map_conversion(Position $Position) {
		global $mapwidth, $mapheight;
		return new Position($Position->X, $mapheight - $Position->Y);
	}
?>
