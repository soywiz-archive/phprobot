<?php
	import('Winbinder.winbinder');
	import('Winbinder.ooi.ooi');

	echo WBC_VERSION;

	$main = new WbAppWindow('Window title', 400, 300);
	$main->connect("double-clicked", "close_window");

	$dw = $main->addControl('WbPushButton',    5,  5, 150, 20, 'Do what?');
	$dw->connect("clicked", "dwclick");

	$clickme = $main->addControl('WbPushButton', 5, 25, 150, 20, 'Clickme');
	$clickme->connect("focus-in", Array("clickme", "focused"));
	$clickme->connect("clicked",  Array("clickme", "clicked"));

	Wb::mainLoop();

	Function close_window($window){
		echo "Window was double-clicked!";
		$window->destroy();
	}

	Function dwclick($button){
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
?>