<?php
	Import('Ragnarok.Server');

	// 006b - Recieved characters from Game Login Server
	function RecivePacket0x006b(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$EntityList = new EntityList();

		$CharaList = array();
		foreach ($Data['Charas'] as $Chara) {
			//print_r($Chara);
			$Entity = new EntityMoveablePlayerMain($EntityList, $Chara['Id']);
			foreach ($Chara as $k => $v) $Entity->$k = $v;
			$Entity->Visible = true;
		}

		$Bot->ServerCharaList = &$EntityList;

		$Bot->SetStepCallBack('OnCharaSelect', $EntityList);
  	}

  	// 006c - Failed to Select Character
  	function RecivePacket0x006c(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$Bot->SetStepCallBack('OnCharaSelectError');
	}

  	// 006d - Success to Create New Character
  	function RecivePacket0x006d(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$Bot->SetStepCallBack('OnCharaCreateSuccess');
	}

  	// 006e - Failed to Create New Character
  	function RecivePacket0x006e(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$Bot->SetStepCallBack('OnCharaCreateError');
	}

  	// 006f - Success to Delete Character
  	function RecivePacket0x006f(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$Bot->SetStepCallBack('OnCharaDeleteSuccess');
	}

  	// 0070 - Fail to Delete Character
  	function RecivePacket0x0070(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$Bot->SetStepCallBack('OnCharaDeleteError');
	}

  	// 0071 - Success to Select Character & Map Name And Ip/Port Number for Game Server
  	function RecivePacket0x0071(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$ServerZone = new ServerZone($Data['Map'], $Data['Ip'], $Data['Port']);
		$Bot->Id = $Data['Id'];
		$Bot->Update();
		$Bot->ServerZone = $ServerZone;

		$Bot->SetStepCallBack('OnCharaSelectSuccess', $ServerZone);
	}
?>