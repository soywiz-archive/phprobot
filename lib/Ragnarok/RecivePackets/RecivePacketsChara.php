<?php
	Import('Ragnarok.Server');

/*
R 006c <error No>.B
	�L�����N�^�I�����s
	fail to select character
R 006d <charactor select data>.106B
	�L�����N�^�쐬����
	success to create new character
R 006e <error No>.B
	�L�����N�^�쐬���s
	fail to create new character
R 006f
	�L�����N�^�폜����
	success to delete character
R 0070 <error No>.B
	�L�����N�^�폜���s
	fail to delete character
R 0071 <charactor ID>.l <map name>.16B <ip>.l <port>.w
	�L�����N�^�I�𐬌�&�}�b�v��&�Q�[���IIP/port
	success to select character & map name and ip/port number for game server
*/

	// 006b - Recieved characters from Game Login Server
	function RecivePacket0x006b(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		print_r($Data);
		$Bot->SetStepCallBack('OnCharaSelect');
		exit;
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
		$Bot->SetStepCallBack('OnCharaSelectSuccess');
	}
?>