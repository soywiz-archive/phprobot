<?php
	Import('Ragnarok.GenericBot');

	class TestBot extends GenericBot {
		function OnBegin() {
			$this->OnDisconnected();
		}

		function OnDisconnected() {
			echo "OnDisconnect()\n";
			$this->ConnectMaster('localhost', 'Test', 'Test');
		}

		function OnMasterLoginError() {
			$this->Disconnect();
		}

		function OnMasterLoginSuccess(&$ServerList) {
			//print_r($this->ServerCharaList);
			$this->ConnectChara('eAthena');
		}

		function OnTick() {
		}
	}

	//sleep(10);

	$gb = new TestBot();

	while (true) {
		$gb->Check();
	}
?>