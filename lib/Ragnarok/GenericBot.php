<?php
	Import('Entity.*');
	Import('Net.SocketPacket');
	Import('Ragnarok.SendPackets.*');
	Import('Ragnarok.RecivePackets.*');

	abstract class GenericBot extends EntityMoveablePlayerMain {
		public $SocketPacket;
		public $ConnectionStep;
		public $ConnectionStatus;
		public $ConnectionServer;

		public $ClientCode              = 0x14;
		public $ClientProtocolVersion   = 0x02;

		const SERVER_NONE                = 0;
		const SERVER_MASTER              = 1;
		const SERVER_CHARA               = 2;
		const SERVER_ZONE                = 3;

		const STATUS_OK                  = 0;
		const STATUS_ERROR               = 1;

		const STEP_MASTER_LOGIN          = 0;
		const STEP_MASTER_LOGIN_ERROR    = 1;
		const STEP_MASTER_PROCESS        = 2;
		const STEP_CHARA_LOGIN           = 3;
		const STEP_CHARA_LOGIN_ERROR     = 4;
		const STEP_CHARA_LOGIN_SUCCESS   = 5;
		const STEP_CHARA_PROCESS         = 6;
		const STEP_ZONE_LOGIN            = 7;
		const STEP_ZONE_PROCESS          = 8;
		const STEP_DISCONNECTED          = 9;

		function __construct() {
			$this->ConnectionServer = self::SERVER_NONE;
			$this->SocketPacket     = new SocketPacket(PacketList::LoadFromFile($this->ClientProtocolVersion));
			$this->ConnectionStatus = self::STATUS_OK;
			$this->ConnectionStep   = self::STEP_DISCONNECTED;
		}

		// Set information
		function SetClientVersion($ProtocolVersion = null, $Code = null) {
			if (isset($ProtocolVersion)) $this->ClientProtocolVersion = $ProtocolVersion;
			if (isset($Code))            $this->ClientCode            = $Code;

			$this->SocketPacket = new SocketPacket(new PacketList($this->ClientProtocolVersion));
		}

		function Check() {
			usleep(5000);

			switch ($this->ConnectionStep) {
				case self::STEP_DISCONNECTED:        $this->OnDisconnect();       break;
				case self::STEP_MASTER_LOGIN:        $this->OnMasterLogin();      break;
				case self::STEP_MASTER_LOGIN_ERROR:  $this->OnMasterLoginError(); break;
				case self::STEP_CHARA_LOGIN:         $this->OnCharaLogin();       break;
				case self::STEP_CHARA_LOGIN_ERROR:   $this->OnCharaLoginError();  break;
				case self::STEP_CHARA_LOGIN_SUCCESS: $this->OnCharaSelect();      break;

				case self::STEP_ZONE_PROCESS:
					// Proceso de movimiento
					$this->ProcessMoving();

				case self::STEP_MASTER_PROCESS: case self::STEP_CHARA_PROCESS:
					while ($Packet = $this->SocketPacket->ExtractPacket()) {
						list($Id, $Data, $DataRaw) = $Packet;

						$hex = str_pad(dechex($Id), 4, '0', STR_PAD_LEFT);

						$f = "RecivePacket0x{$hex}";

						if (!function_exists($f)) {
							throw(new Exception("La función '{$f}' no está definida\n"));
						} else {
							// GenericBot &$Bot, $PId, $Data, $DataRaw
							$f($this, $Id, $Data, $DataRaw);
						}
					}
				break;

			}
		}

		function __call($m, $a) {
			if (strcasecmp(substr($m, 0, 2), 'on') == 0) {
				echo "Called $m(...);\n";
			}
		}

		// Interface

		public function ConnectMaster($Host, $User, $Password) {
			echo "this->Connect($Host, 6900);\n";
			$this->SocketPacket->Connect($Host, 6900);

			echo "SendMasterLogin($this, $User, $Password, $this->ClientCode, $this->ClientProtocolVersion);\n";
			SendMasterLogin($this, $User, $Password, $this->ClientCode, $this->ClientProtocolVersion);

			$this->ConnectionStep = self::STEP_MASTER_PROCESS;
		}

		public function Disconnect() {
			switch ($this->ConnectionServer) {
				case self::SERVER_MASTER: $this->DisconnectMaster(); break;
				case self::SERVER_CHARA:  $this->DisconnectChara();  break;
				case self::SERVER_ZONE:   $this->DisconnectZone();   break;
			}

			$this->SocketPacket->Disconnect();

			$this->ConnectionStep = self::STEP_DISCONNECTED;
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