<?php
	Import('Ragnarok.GenericBot');

	class TestBot extends GenericBot {
		function OnDisconnect() {
			echo "OnDisconnect()\n";
			$this->ConnectMaster('localhost', 'Test', 'Test2');
		}

		function OnMasterLoginError() {
			$this->Disconnect();
		}
	}

	//sleep(10);

	$gb = new TestBot();

	while (true) {
		$gb->Check();
	}
?>