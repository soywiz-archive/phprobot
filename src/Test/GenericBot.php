<?php
	Import('Ragnarok.GenericBot');

	class TestBot extends GenericBot {
		function OnDisconnect() {
			echo "OnDisconnect()\n";
			$this->ConnectMaster('localhost', 'Test', 'Test');
		}

		function OnMasterLoginError() {
			$this->Disconnect();
		}

		function OnMasterLogin(&$ServerList) {
			//print_r($this->ServerCharaList);
			$this->ConnectChara('eAthena');
		}
	}

	//sleep(10);

	$gb = new TestBot();

	while (true) {
		$gb->Check();
	}
?>