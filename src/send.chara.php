<?php
	if (!defined('GB_SYSTEM_LODADED')) die('Se requiere el sistema de GenericBot');

	function sendCharaServerSelect(GenericBot &$o, $id) {
		// Selecciona un servidor
		$o->sock->send(maker16(0x0065) . maker32($o->connectionData['id_account']) . maker32($o->connectionData['id_login1']) . maker32($o->connectionData['id_login2']) . maker16(0x0000) . maker8($o->sex));
	}

	function sendCharaSelect(GenericBot &$o, $id) {
		// Elige un personaje
		$o->sock->send(maker16(0x0066) . maker8($id));
	}

	// ---------------

	//  4 >= a_b >= 1

	//  a = 5 + a_b
	//  b = 5 - a_b
	// ---------------
	function sendCharaCreate(GenericBot &$o, $name, $head_type, $head_color, $str_int, $vit_dex, $luk_agi) {
		// Crea un nuevo personaje
	}

	function sendCharaDelete(GenericBot &$o, $id, $email) {
		// Borra un personaje
	}
?>