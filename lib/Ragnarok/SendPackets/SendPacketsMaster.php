<?php
	Import('Net.Socket');
	Import('System.Buffer');

	function SendMasterLogin(GenericBot &$Bot, $User, $Pass, $Code, $Version) {
		$Bot->SocketPacket->Send(
			MakeR16(0x0064)    .
			MakeR32($Code)     .
			MakeZS ($User, 24) .
			MakeZS ($Pass, 24) .
			MakeR16($Version)
		);
	}
?>