<?php
	Import('Ragnarok.Server');

	// 0069 - Account Info
	function RecivePacket0x0069(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$Bot->ServerCharaList = array();
		$Scl = &$Bot->ServerCharaList;

		$Bot->IdLogin1      = $Data['IdLogin1'];
		$Bot->IdLogin2      = $Data['IdLogin2'];
		$Bot->IdAccount     = $Data['IdAccount'];
		$Bot->Sex           = $Data['Sex'];
		$Bot->DateLastLogin = $Data['DateLastLogin'];

		foreach ($Data['Servers'] as $Server) {
			$Scl[] = new ServerChara($Server['Ip'], $Server['Port'], $Server['Name'], $Server['Count']);
		}

		$Bot->SetStepCallBack('OnMasterLoginSuccess', $Scl);
  	}

	// 006a - Connection Failed
	function RecivePacket0x006a(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$Error = &$Data['Error'];

		switch ($error) {
			case 0:  $Bot->SetError($Error, 'ID incorrecto...'); break;
			case 1:  $Bot->SetError($Error, 'Contraseña incorrecta...'); break;
			case 2:  $Bot->SetError($Error, 'El ID ha expirado...'); break;
			case 3:  $Bot->SetError($Error, 'Conexión denegada...'); break;
			case 4:  $Bot->SetError($Error, 'No hay certificación de E-Mail para esta ID!...'); break;
			case 5:  $Bot->SetError($Error, 'Tu cliente no tiene la ultima version. Por favor actualizalo primero...'); break;
			case 6:  $Bot->SetError($Error, 'Conexión bloqueada temporalmente...'); break;
			default: $Bot->SetError($Error, 'Error desconocido'); break;
		}

		$Bot->SetStepCallBack('OnMasterLoginError');
	}
?>