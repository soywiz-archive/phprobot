<?php
    require_once(dirname(dirname(__FILE__)) . '/Bots/TestBot/TestBot.php');

	$Bot = new TestBot();

	import('Winbinder.winbinder');

	define('ID_APP_TIMER', 1001);
	define('IDC_RESULT',   1002);

	$mainwin = wb_create_window(NULL, 101, 'Lunea', WBC_CENTER, WBC_CENTER, 320, 240, WBC_VISIBLE, 0);
	//wb_create_window(NULL, PopupWindow, "Hello world!", 480, 320);

	$Box = wb_create_control($mainwin, EditBox, "", 0, 0, 320, 240, IDC_RESULT, WBC_VISIBLE | WBC_ENABLED | WBC_LINES);

	wb_set_handler($mainwin, 'process_main');
    wb_create_timer($mainwin, ID_APP_TIMER, 50);
    //wb_create_timer($mainwin, ID_APP_TIMER, 1000);
	wb_main_loop();

	function process_main($window, $id) {
		global $Bot;
		global $Box;

		switch($id) {
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
				if (strlen($data)) wb_set_text($Box, wb_get_text($Box) . $data);
			break;
			case IDCLOSE:
				wb_destroy_window($window);
			break;
		}
	}
?>
