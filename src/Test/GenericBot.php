<?php
	Import('Ragnarok.GenericBot');

	class TestBot extends GenericBot {
		function OnDisconnect() {
			echo "OnDisconnect()\n";
			$this->ConnectMaster('localhost', 'test2', 'test2');
		}
	}

	$gb = new TestBot();

	while (true) {
		$gb->Check();
	}
?>