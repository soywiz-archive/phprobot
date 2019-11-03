<?php
	if (!defined('GB_SYSTEM_LODADED')) die('Se requiere el sistema de GenericBot');

	function sendMasterLogin(GenericBot &$o, $user, $pass, $code, $version) {
		$o->sock->send(maker16(0x0064) . maker32($code) . makeZS($user, 24) . makeZS($pass, 24) . maker16($version));
	}
?>