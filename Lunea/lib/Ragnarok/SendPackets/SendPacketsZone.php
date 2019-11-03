<?php
	Import('Net.Socket');
	Import('System.Buffer');

	function SendZoneLogin(GenericBot &$Bot) {
		$Bot->SocketPacket->Send(
			MakeR16(0x0072)          .
			MakeR32($Bot->IdAccount) .
			MakeR32($Bot->Id)        .
			MakeR32($Bot->IdLogin1)  .
			MakeR32(GetTickCount())  .
			MakeR8 ($Bot->Sex)
		);

		$Bot->IdZone = MakeR32($Bot->SocketPacket->Extract(4));
	}

	function SendZoneLoaded(GenericBot &$Bot) {
		$Bot->SocketPacket->Send(
			MakeR16(0x007D)
		);
	}

	function SendZoneMove(GenericBot &$Bot, Position $Position) {
		echo "- Send Position (" . $Position->X . ', ' . $Position->Y . ")\n";
		$Bot->SocketPacket->Send(
			MakeR16(0x0085)    .
			MakeXYP($Position)
		);
	}

	function SendZoneGetEntityName(GenericBot &$Bot, $Id) {
		// El Id no es correcto?
		$Bot->SocketPacket->Send(
			MakeR16(0x0094)    .
			MakeR32($Id)
		);
	}

	function SendZoneSkillUse(GenericBot &$Bot, $SkillId, $Level, $Id) {
		$Bot->SocketPacket->Send(
			MakeR16(0x0113)   .
			MakeR8($Level)    .
			MakeR8(0)         .
			MakeR16($SkillId) .
			MakeR32($Id)
		);
	}

	function SendZoneSkillUseMap(GenericBot &$Bot, $SkillId, $Level, Position $Position) {
		$Bot->SocketPacket->Send(
			MakeR16(0x0116) .
			MakeR16($Level) .
			MakeR16($SkillId) .
			MakeR16($Position->X) .
			MakeR16($Position->Y)
		);
	}

/*
	function sendSkillUseMap(GenericBot &$o, $skillId, $level, $x, $y) {
		//sendTick($o);
		//echo "------\n";
		$o->sock->send(maker16(0x0116) . maker16($level) . maker16($skillId) . maker16($x) . maker16($y));
		//$o->rawSendData = (maker16(0x0116) . maker16($level) . maker16($skillId) . maker16($x) . maker16($y));
		//echo "++++++\n";
		//<level>.w <skill ID>.w <X>.w <Y>.w
	}

	function sendChangeView(GenericBot &$o, $pos_head, $pos_body) {
		if ($pos_head < 0) $pos_head = 8 - (-$pos_head % 8);
		if ($pos_body < 0) $pos_body = 8 - (-$pos_body % 8);
		$o->sock->send(maker16(0x009b) . maker16($pos_head % 8) . maker8($pos_body % 8));
	}

	function sendSay(GenericBot &$o, $text, &$to = NULL) {
		if (isset($to) && !($to instanceof Entity)) throw(new Exception(''));

		$text = $o->player->name . ' : ' . $text;

		if (!isset($to)) {
			$o->sock->send(maker16(0x008c) . maker16(strlen($text) + 5) . makeZS($text, strlen($text) + 1));
		} else {
			// TODO
		}
	}

///////////////////////////////////////////////////////////////////////////////

	function sendRequestResponse(GenericBot &$o, $accept = false) {
		$o->sock->send(maker16(0x00e6) . maker8($accept ? 3 : 4));
	}

	function sendTradeZeny(GenericBot &$o, $zeny) {
		$o->sock->send(maker16(0x00eb) . maker16(0) . maker32($zeny));
	}

	function sendTradeOk(GenericBot &$o) {
		//echo "sendTradeOk(GenericBot &$o)\n";
		$o->sock->send(maker16(0x00eb));
	}

	function sendTradeFinish(GenericBot &$o) {
		echo "sendTradeFinish(GenericBot &$o)\n";
		$o->sock->send(maker16(0x00ef));
	}

	function sendTick(GenericBot &$o) {
		$o->sock->send(maker16(0x007e) . maker32(getTickCount()));
	}

*/
?>