<?php
    require_once(dirname(dirname(__FILE__)) . '/Bots/TestBot/TestBot.php');

	$Bot = new TestBot();

	import('Winbinder.winbinder');

	$cmd = 1001;
	define('ID_APP_TIMER', $cmd++);
	define('IDC_RESULT',   $cmd++);
	define('ID_SELECT',    $cmd++);
	define('ID_WEBSITE',   $cmd++);
	define('ID_ABOUT',     $cmd++);

	$mainwin = wb_create_window(NULL, 101, 'Lunea', WBC_CENTER, WBC_CENTER, 320, 240, WBC_VISIBLE, 0);
	wb_set_image($mainwin, dirname(dirname(__FILE__)) . '/bin/lunea.ico');

	//wb_create_window(NULL, PopupWindow, "Hello world!", 480, 320);

	$Box = wb_create_control($mainwin, EditBox, "", 0, 0, 313, 213, IDC_RESULT, WBC_VISIBLE | WBC_ENABLED | WBC_LINES);

	$mainmenu = wb_create_control($mainwin, Menu, array(
	"&File",
		array(ID_SELECT,	"Select &Bot...\tCtrl+B", "", "", "Ctrl+B"),
		null,
		array(IDCLOSE,		"E&xit\tAlt+F4", "", PATH_RES . "menu_exit.bmp"),
	"&Help",
		array(ID_WEBSITE,	"&Web site..."),
		null,
		array(ID_ABOUT,		"&About...", "", PATH_RES . "menu_help.bmp"),

	), $mainwin);

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
				if (strlen($data)) {
					$sl = strlen($set = wb_get_text($Box) . $data);
					wb_set_text($Box, $set);
					//wb_send_message($Box, 0xf0b1, 30, 1);
					wb_refresh($Box, 1);

					//wb_send_message($Box, EM_SETSEL, $sl, $sl);
					//wb_send_message(IDC_RESULT, 0xf0b1, 0, 0);
					echo wb_send_message($Box, 0xF0B0, 0, 0);
				}
			break;
			case ID_WEBSITE:
				if (!wb_exec("http://phprobot.sourceforge.net/"))
					wb_message_box($window, "Problem opening web site.");
				break;
			case IDCLOSE:
				wb_destroy_window($window);
			break;
		}
	}

/*
EM_GETSEL
An application sends an EM_GETSEL message to get the starting and ending character positions of the current selection in an edit control.

EM_GETSEL
wParam = (WPARAM) (LPDWORD) lpdwStart; // receives starting position
lParam = (LPARAM) (LPDWORD) lpdwEnd;   // receives ending position


Parameters

lpdwStart

Value of wParam. Points to a 32-bit value that receives the starting position of the selection. This parameter can be NULL.

lpdwEnd

Value of lParam. Points to a 32-bit value that receives the position of the first nonselected character after the end of the selection. This parameter can be NULL.

Return Values

The return value is a zero-based 32-bit value with the starting position of the selection in the low-order word and the position of the first character after the last selected character in the high-order word. If either of these values exceeds 65, 535, the return value is -1.

Remarks

In a rich edit control, if the selection is not entirely contained in the first 64K, use the message EM_EXGETSEL.

See Also

EM_EXGETSEL, EM_SETSEL

EM_REPLACESEL

An application sends an EM_SETSEL message to select a range of characters in an edit control.

EM_SETSEL
wParam = (WPARAM) (INT) nStart;    // starting position
lParam = (LPARAM) (INT) nEnd;      // ending position

*/
?>
