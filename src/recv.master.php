<?php
	if (!defined('GB_SYSTEM_LODADED')) die('Se requiere el sistema de GenericBot');

	// 0069 - Account Info
	function parse_recv_0069(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id_login1;id_account;id_login2;date_last_login;sex;servers]llll-z[24]w-bx[rest][a[ip;port;name;users]rlnf[ip]wz[20]w-w-w]');

		$o->connectionData['id_login1']  = $d['id_login1'];
		$o->connectionData['id_login2']  = $d['id_login2'];
		$o->connectionData['id_account'] = $d['id_account'];

		$o->sex        = $d['sex'];
		$o->serverList = $d['servers'];

		$o->step = GB_STEP_CHARA_LOGIN;

		$o->setErrorSuccess();
	}

	// 006a - Connection Failed
	function parse_recv_006a(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[error]bs[20]-');
		$error = &$d['error'];

		switch ($error) {
			case 0:  $o->setError($error, 'ID incorrecto...'); break;
			case 1:  $o->setError($error, 'Contraseña incorrecta...'); break;
			case 2:  $o->setError($error, 'El ID ha expirado...'); break;
			case 3:  $o->setError($error, 'Conexión denegada...'); break;
			case 4:  $o->setError($error, 'No hay certificación de E-Mail para esta ID!...'); break;
			case 5:  $o->setError($error, 'Tu cliente no tiene la ultima version. Por favor actualizalo primero...'); break;
			case 6:  $o->setError($error, 'Conexión bloqueada temporalmente...'); break;
			default: $o->setError($error, 'Error desconocido'); break;
		}

		$o->step = GB_STEP_MASTER_LOGIN_ERROR;
	}
?>