<?php
	Import('Entity.*');
	Import('Net.SocketPacket');
	Import('Ragnarok.SendPackets.*');
	Import('Ragnarok.RecivePackets.*');
	Import('Ragnarok.Server');
	Import('Ragnarok.EntityList');
	Import('Ragnarok.ItemList');
	Import('System.Ragnarok');

	abstract class GenericBot extends EntityMoveablePlayerMain {
		public  $SocketPacket;
		public  $ConnectionStep;
		public  $ConnectionStatus;
		public  $ConnectionServer;

		public  $StepCallback;
		public  $StepCallbackParameters;
		public  $StepCallbackQueue;

		private $WaitMilliseconds;

		public  $ClientCode               = 0x14;
		public  $ClientProtocolVersion    = 0x02;

		const   SPEECH_GLOBAL             = 0;
		const   SPEECH_PRIVATE            = 1;
		const   SPEECH_GLOBAL_FROM        = 2;

		const   SERVER_NONE               = 0;
		const   SERVER_MASTER             = 1;
		const   SERVER_CHARA              = 2;
		const   SERVER_ZONE               = 3;

		const   STATUS_OK                 = 0;
		const   STATUS_ERROR              = 1;

		public  $IdLogin1                 = 0x00000000;
		public  $IdLogin2                 = 0x00000000;

		public  $DateLastLogin            = '';

		public  $ServerCharaList          = array();
		public  $ServerZone;

		public  $SkillList;
		public  $ItemList;

		public  $Cart;

		public function Dump() {
			$__Acquire = $this->__ListAcquire(array(
				'EntityList', 'SocketPacket', 'ServerCharaList', 'Map',
				'SkillList', 'ItemList', 'Cart'
			));

			print_r($this);

			$this->__ListRelease($__Acquire);
		}

		function __construct() {
			$this->ConnectionServer  = self::SERVER_NONE;
			$this->SocketPacket      = new SocketPacket(PacketList::LoadFromFile($this->ClientProtocolVersion));
			$this->ConnectionStatus  = self::STATUS_OK;
			$this->StepCallbackQueue = array();
			$this->WaitMilliseconds  = 0;
			$this->ItemList          = new ItemList();
			$this->Cart              = new ItemList();
			$this->SetStepCallBack('OnBegin');

			$this->EntityInit(new EntityList());
		}

		// Set information
		function SetClientVersion($ProtocolVersion = null, $Code = null) {
			if (isset($ProtocolVersion)) $this->ClientProtocolVersion = $ProtocolVersion;
			if (isset($Code))            $this->ClientCode            = $Code;

			$this->SocketPacket = new SocketPacket(new PacketList($this->ClientProtocolVersion));
		}

		// TODO
		public function SetError($Id, $Text) {
			// TODO
			echo "Error: {$Id} - {$Text}";
		}

		public function SetStepCallBack() {
			$array = func_get_args();
			array_push($this->StepCallbackQueue, $array);

			$this->StepCallback = array_shift($this->StepCallbackParameters = array_shift($this->StepCallbackQueue));
			//$this->StepCallback = array_shift($this->StepCallbackParameters = func_get_args());
		}

		public function Wait($Milliseconds) {
			$this->WaitMilliseconds += $Milliseconds;
		}

		function Check() {
			if ($this->WaitMilliseconds > 0) {
				usleep($this->WaitMilliseconds * 100);
				$this->WaitMilliseconds = 0;
			}

			usleep(1000);

			// Proceso de movimiento
			//$this->ProcessMoving();

			while ($Packet = $this->SocketPacket->ExtractPacket()) {
				list($Id, $Data, $DataRaw) = $Packet;

				$hex = str_pad(dechex($Id), 4, '0', STR_PAD_LEFT);

				$f = "RecivePacket0x{$hex}";
				echo '- ' . $f . '(' . @implode(', ', array_values($Data)) . ")\n";

				if (!function_exists($f)) {
					throw(new Exception("La función '{$f}' no está definida\n"));
				} else {
					// GenericBot &$Bot, $PId, $Data, $DataRaw
					$f($this, $Id, $Data, $DataRaw);
				}
			}

			call_user_func_array(array(&$this, $this->StepCallback), $this->StepCallbackParameters);

			$this->SetStepCallBack('OnTick');
		}

		function __call($m, $a) {
			if (strcasecmp(substr($m, 0, 2), 'on') == 0) {
				echo "Called $m(...);\n";
			}
		}

		// Interface

		public function ConnectMaster($Host, $User, $Password) {
			$this->SocketPacket->Connect($Host, 6900);

			SendMasterLogin($this, $User, $Password, $this->ClientCode, $this->ClientProtocolVersion);

			$this->ConnectionServer = self::SERVER_MASTER;
		}

		public function ConnectChara($ServerChara) {
			if (!($ServerChara instanceof ServerChara)) {
				if (isset($this->ServerCharaList[$ServerChara])) {
					$ServerChara = &$this->ServerCharaList[$ServerChara];
				} else {
					// Comprobación por nombre
					//GetSimilarValue($this->ServerCharaList);
					$ServerChara = GetSimilarObjectValue($this->ServerCharaList, 'Name', $ServerChara);

					//throw(new Exception('El servidor no existe'));
				}
			}

			if (!($ServerChara instanceof ServerChara)) {
				throw(new Exception('No se pudo elegir ningún servidor'));
			}

			$this->SocketPacket->Connect($ServerChara->Ip, $ServerChara->Port);

			SendCharaLogin($this);

			$this->IdAccount2 = GetR32($this->SocketPacket->Extract(4));

			$this->ConnectionServer = self::SERVER_CHARA;
		}

		public function ConnectZone($ServerZone = null) {
			if (!isset($ServerZone)) $ServerZone = $this->ServerZone;

			$this->SocketPacket->Connect($ServerZone->Ip, $ServerZone->Port);

			SendZoneLogin($this);

			$this->ConnectionServer = self::SERVER_ZONE;
		}

		public function CharaSelect($Chara) {
			if (!($Chara instanceof Entity)) {
				$List = $this->ServerCharaList->GetListById();
				if (isset($List[$Chara])) {
					$Chara = &$List[$Chara];
				} else {
					$Chara = $this->ServerCharaList->GetEntityBySimilarName($Chara);
				}
			}

			// $Chara instanceof Entity
			if (!($Chara instanceof Entity)) {
				throw(new Exception('No se pudo elegir el character "' . $Chara . '"'));
			}

			foreach ($Chara as $k => $v) {
				if (!isset($v) || !@strlen($v)) continue;
				if ($k == 'EntityList') continue;
				if ($k == 'Id') continue;
				if ($k == 'Sex') continue;
				$this->$k = $v;
			}

			SendCharaSelect($this, $Chara->Position);

			//$this->SocketPacket->Extract(0);
			//echo GetR32($) . "\n";
		}

		public function Disconnect() {
			switch ($this->ConnectionServer) {
				case self::SERVER_MASTER: $this->DisconnectMaster(); break;
				case self::SERVER_CHARA:  $this->DisconnectChara();  break;
				case self::SERVER_ZONE:   $this->DisconnectZone();   break;
			}

			$this->SocketPacket->Disconnect();

			$this->SetStepCallBack('OnDisconnected');
		}

		// Private Interface

		private function DisconnectMaster() {
			// None
		}

		private function DisconnectChara() {
			// None
		}

		private function DisconnectZone() {
			// Send Exit Packet
		}

		// Prototypes
	}
?>