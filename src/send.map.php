<?php
	if (!defined('GB_SYSTEM_LODADED')) die('Se requiere el sistema de GenericBot');

	function sendMove(GenericBot &$o, $x, $y) {
		$o->sock->send(maker16(0x0085) . makeXY($x, $y));
	}

	function sendMapLogin(GenericBot &$o) {
		//print_r($o->connectionData); foreach ($o->connectionData as $k => $v) echo "$k -> " . dechex($v) . "\n"; exit;
		$o->sock->send(
			maker16(0x0072) .
			maker32($o->connectionData['id_account']) .
			maker32($o->connectionData['id_character']) .
			maker32($o->connectionData['id_login1']) .
			maker32(getTickCount()) .
			 maker8($o->sex)
		);
	}

	function sendMapLoaded(GenericBot &$o) {
		$o->sock->send(maker16(0x007D));
	}

	function sendGetEntityName(GenericBot &$o, $id) {
		$o->sock->send(maker16(0x0094) . maker32($id));
	}

	function sendSkillUse(GenericBot &$o, $skillId, $level, $id) {
		$o->sock->send(maker16(0x0113) . maker8($level) . maker8(0) . maker16($skillId) . maker32($id));
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
?>