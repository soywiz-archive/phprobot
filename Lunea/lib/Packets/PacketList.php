<?php
	Import('Packets.PacketStructure');
	Import('System.Buffer');

	class PacketList {
		private $PacketStructures = array();

		static public function LoadFromFile($ProtocolVersion, $FileName = null) {
			//echo "ProtocolVersion: $ProtocolVersion\n";

			$Return = new PacketList();

			do {
				if (!isset($FileName)) {
					$Path = LUNEA_DATA . '/packets/';

					foreach (scandir($Path) as $file) {
						if (strcasecmp(substr($file, -4, 4), '.xml') == 0)
							$Return->Merge(PacketList::LoadFromFile($ProtocolVersion, $Path . $file));
					}

					break;
				}

				$o = simplexml_load_file($FileName);

				foreach ($o->attributes() as $k => $v) {
					switch (strtolower(trim($k))) {
						case 'protocolversion':
							$ProtocolVersion2 = GetInteger($v);
						break;
					}
				}

				if (!isset($ProtocolVersion2) || $ProtocolVersion != $ProtocolVersion2) break;

				foreach ($o as $k => $Packet) {
					if (strtolower($k) == 'packet') {
						$PackeTo = new PacketStructure($Packet);
						$Return->PacketStructures[$PackeTo->Id] = $PackeTo;
					}
				}
			} while (false);

			return $Return;
		}

		public function Merge(PacketList $PacketList) {
			foreach (array_keys($PacketList->PacketStructures) as $id) {
				$this->PacketStructures[$id] = $PacketList->PacketStructures[$id];
			}
		}

		public function PacketExists($id) {
			return isset($this->PacketStructures[$id]);
		}

		public function PacketLength($id) {
			return $this->PacketExists($id) ? $this->PacketStructures[$id]->Length : false;
		}

		public function Packet($id) {
			return $this->PacketStructures[$id];
		}
	}
?>