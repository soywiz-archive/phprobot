<?php
/** Example of a $__ooiDefaultHndl:
[(window address)]=>(window_handler)
[0001]=>"proccess_main"
**/

/** Example of a $__ooiSignalTable:
[(window address)][(widget ID)][(signal)]=>Array([action]=>(action), [parameter]=>(parameter))
[0001][0xF00]["clicked"]=>Array([action]=>"user_function", [parameter]=>"user_parameter")
**/

$__ooiDefaultHndl = Array(); // Default window handler [if user wish to ignore new OOi S-C]
$__ooiSignalTable = Array(); // Action for each signal, on each widget "connected"
$__ooiReservedIDs = 0xF00; // 0xF00 to 0xFFF (reserved for auto-id)
$__ooiSignals     = Array(
//  'destroy'=>WBC_DEFAULT, // special... there's an 'if' just for it at the handler function
	'focus-in'=>WBC_GETFOCUS,
	'double-clicked'=>WBC_DBLCLICK,
	'mouse-move'=>WBC_MOUSEMOVE,
	'mouse-down'=>WBC_MOUSEDOWN,
	'mouse-up'=>WBC_MOUSEUP,
	'clicked'=>WBC_DEFAULT,
	'key-pressed'=>WBC_DEFAULT, // this is for the WbEditBox
);

/**
	This function will be the default handler for all the created windows or dialogs.
**/
function __ooi_proccess_control($window, $id, $ctrl=0, $lparam=0, $lparam2=0){
	if($id == 0 && $lparam == 0)
		return;
	global $__ooiSignals, $__ooiSignalTable, $__ooiDefaultHndl;

	// echo "> Event received: ID=$id CTRL=$ctrl LPARAM=$lparam LPARAM2=$lparam2\n";
	$whatiswindow = Wb::addressToObject($window, true); // Protection against "TabControl"
	if($whatiswindow["object_class"] == "WbTabControl")
		$window = $whatiswindow["parent"];
	$ooi_window = Wb::addressToObject($window);

	// Make the fucking window indestructible, so the widget won't be destroyed at the
	// end of this proccess_control function, when all the variables (including the
	// fucking mother fucker $ooi_window var) are destroyed, and the classes call their
	// __destruct() fucking methods! [why so many affwords? it took me almost three hours!
	// to find out what was going on! Shit!]
	$ooi_window->indestructible = true;

	if(isset($__ooiDefaultHndl[$window])){
		// Ignore new OOi event handling
		call_user_func($__ooiDefaultHndl[$window], $window, $id, $ctrl, $lparam, $lparam2);
	}

	if($id == IDCLOSE && $lparam == WBC_DEFAULT){
		// Default window closing...
		if(!isset($__ooiSignalTable[$window][0]["destroy"])){
			$ooi_window->destroy();
			return;
		}
		else{
			$action = &$__ooiSignalTable[$window][0]["destroy"];
			call_user_func($action['handler'], $action['widget'], $action['param'], $lparam2);
		}
	}

	if(isset($__ooiSignalTable[$window][$id])){
		// Ok, let's make the user-function to work!
		$widget_signals = &$__ooiSignalTable[$window][$id];
		foreach($widget_signals as $signal=>$action){
			if($lparam == $__ooiSignals[$signal] || ($lparam&$__ooiSignals[$signal])){
				// Call the user function, with four arguments:
				// First argument is the object.
				// Second is the user-defined parameter.
				// Third is the 'lparam'.
				// Fourth is the 'lparam2'.
				// echo ">> User function FN=$action[handler] PARAM=$action[param]\n";
				call_user_func($action['handler'], $action['widget'], $action['param'], $lparam, $lparam2);
			}
		}
	}

	return true;
}
