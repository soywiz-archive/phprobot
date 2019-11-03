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
			//echo 'tick';
		}

		function OnCharaSelect(EntityList &$EntityList) {
			$this->CharaSelect('Test');
		}

		function OnCharaSelectSuccess(ServerZone &$Zone) {
			$this->ConnectZone();
		}

		function OnZoneSay($Type, $Entity, $Text) {
			echo "$Type: $Text\n";
		}
	}

	//sleep(10);

    $gb = new TestBot();

    while (true) {
      $gb->Check();
    }
?>
