<?php
	if (!defined('GB_SYSTEM_LODADED')) die('Se requiere el sistema de GenericBot');

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
?>