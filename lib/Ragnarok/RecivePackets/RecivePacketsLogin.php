<?php
	// 0069 - Account Info
	function RecivePacket0x0069(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		print_r($Data);

		$Bot->ConnectionStep = GenericBot::STEP_MASTER_LOGIN;
  	}

	// 006a - Connection Failed
	function RecivePacket0x006a(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$error = &$Data['Error'];

		switch ($error) {
			case 0:  $Bot->SetError($error, 'ID incorrecto...'); break;
			case 1:  $Bot->SetError($error, 'Contraseña incorrecta...'); break;
			case 2:  $Bot->SetError($error, 'El ID ha expirado...'); break;
			case 3:  $Bot->SetError($error, 'Conexión denegada...'); break;
			case 4:  $Bot->SetError($error, 'No hay certificación de E-Mail para esta ID!...'); break;
			case 5:  $Bot->SetError($error, 'Tu cliente no tiene la ultima version. Por favor actualizalo primero...'); break;
			case 6:  $Bot->SetError($error, 'Conexión bloqueada temporalmente...'); break;
			default: $Bot->SetError($error, 'Error desconocido'); break;
		}

		$Bot->ConnectionStep = GenericBot::STEP_MASTER_LOGIN_ERROR;
	}

?>