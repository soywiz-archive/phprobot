<?php
	import('Winbinder.winbinder');
	import('Winbinder.ooi.ooi');

	/*
	echo WBC_VERSION;

	$main = new WbAppWindow('Window title', 400, 300);
	$main->connect("double-clicked", "close_window");

	$dw = $main->addControl('WbPushButton',    5,  5, 150, 20, 'Do what?');
	$dw->connect("clicked", "dwclick");

	$clickme = $main->addControl('WbPushButton', 5, 25, 150, 20, 'Clickme');
	$clickme->connect("focus-in", Array("clickme", "focused"));
	$clickme->connect("clicked",  Array("clickme", "clicked"));

	Wb::mainLoop();

	function close_window($window){
		echo "Window was double-clicked!";
		$window->destroy();
	}

	function dwclick($button){
		$button->width = $button->width + 10;
		print("Do what button was clicked.\n");
	}

	class clickme{
		function focused($button, $uparam, $lparam){
			global $dw;
			$button->setSize(230, 40);
			$button->setPosition(rand(0,150), rand(0,200));
			$button->text = "If you cannot focus me, you cannot click me!";
			$button->parentObject->text = "Try again! :-O";
			$dw->setFocus();
		}
		function clicked($button){
			echo "Button was clicked...\n";
			$button->text = "Ok, you did! :(";
		}
	}
	*/

define("APPNAME",           "Hello world!");    // Application name
define("PATH_RES",          "../res/");         // Path for resources

//-------------------------------------------------------------------- CONSTANTS

// Control identifiers

define("ID_ABOUT",          101);
define("ID_OPEN",           102);

//-------------------------------------------------------------- EXECUTABLE CODE

// Create main window, then assign a procedure and an icon to it

$w["main"] = new WbAppWindow(APPNAME . " - PHP " . PHP_VERSION, 320, 240);
$w["main"]->setHandler("process_main");
$w["main"]->setImage(PATH_RES . "hyper.ico");

// Create menu

$men = $w["main"]->addControl('WbMenu');
$men->addItem("&File");
$men->addItem(ID_OPEN, "&Open...", PATH_RES."menu_open.bmp", "Ctrl+O");
$men->addItem();
$men->addItem(IDCLOSE, "E&xit",    PATH_RES."menu_exit.bmp", "Alt+F4");
$men->addItem("&Help");
$men->addItem(ID_ABOUT,"&About...",PATH_RES."menu_help.bmp", "F1");
$men->finished();

// Create toolbar

$toolbar = $w["main"]->addControl('WbToolbar');
$toolbar->setIconPallete(PATH_RES."toolbar.bmp", 16, 15);
$toolbar->addButton(); // Add a separator
$toolbar->addButton(ID_OPEN,  "Open a file",            1);
$toolbar->addButton(ID_ABOUT, "About this application", 13);
$toolbar->addButton(IDCLOSE,  "Exit this application",  14);
$toolbar->finished();

// Create status bar

$w["statusbar"] = new WbStatusBar($w["main"]);
$w["statusbar"]->text = APPNAME;

// Create label control inside the window

$txt  = "This is a demo 'Hello world'\n";
$txt .= "application made with WinBinder.\n";
$txt .= "It has a toolbar, a status bar and a menu.";
$w["label"] = new WbLabel($w["main"], 10, 70, 290, 80, $txt, 0, WBC_CENTER);

// Enter application loop

Wb::mainLoop();

//-------------------------------------------------------------------- FUNCTIONS

/* Process main window commands */

function process_main($window, $id)
{
    global $w;

    static $file_filter = array(
        array("PHP source code",    "*.php?"),
        array("Web page",           "*.htm?"),
        array("Text document",      "*.txt"),
        array("All files",          "*.*")
    );

    switch($id) {
        case ID_ABOUT:
            Wb::messageBox($w["main"], "WinBinder version: " . WBC_VERSION .
                "\nOOi Version: " . Wb::$ooi_version, "About " . APPNAME);
            break;

        case ID_OPEN:
            $filename = Wb::sysDlgOpen($w["main"], "Get It", $file_filter);
            if($filename)
                $w["statusbar"]->text = $filename;
            break;

        case IDCLOSE:       // IDCLOSE is predefined
            $w["main"]->destroy();;
            break;
    }
}
?>