<?php
	Import('Net.Socket');
	Import('System.Buffer');

/*
S 0065 <account ID>.l <login ID1>.l <login ID2>.l ?.2B <sex>.B
	キャラセレ鯖接続要求
	request connection to character select server
S 0066 <charactor number>.B
	キャラクタ選択要求
	request to select character
S 0067 <charactor name>.24B <param etc>.11B
	キャラクタ作成要求
	request to create new character
S 0068 <charactor ID>.l <mail address>.40B
	キャラクタ削除要求
	request to delete character
*/

	function SendCharaLogin(GenericBot &$Bot) {
		$Bot->SocketPacket->Send(
			MakeR16(0x0065)          .
			MakeR32($Bot->IdAccount) .
			MakeR32($Bot->IdLogin1)  .
			MakeR32($Bot->IdLogin2)  .
			MakeR16(0x0000)          .
			MakeR8 ($Bot->Sex)
		);
	}

	function SendCharaSelect(GenericBot &$Bot, $Position) {
		$Bot->SocketPacket->Send(
			MakeR16(0x0066)          .
			MakeR8 ($Bot->Position)
		);
	}

	function SendCharaCreate(GenericBot &$Bot, $Name, Status $Status, $Style, $Color) {
		$Bot->SocketPacket->Send(
			MakeR16(0x0067)       .
			MakeZS ($Name, 24)    .
			MakeR8 ($Status->Str) .
			MakeR8 ($Status->Agi) .
			MakeR8 ($Status->Vit) .
			MakeR8 ($Status->Int) .
			MakeR8 ($Status->Dex) .
			MakeR8 ($Status->Luk) .
			MakeR16($Style)       .
			MakeR16($Color)
		);
	}

	function SendCharaDelete(GenericBot &$Bot, $Id, $Email) {
		$Bot->SocketPacket->Send(
			MakeR16(0x0068)     .
			MakeR32($Id)        .
			MakeZS ($Email, 40)
		);
	}
?>