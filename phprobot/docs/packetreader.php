<?php
	// Packets/PacketStructure.php

	class PacketStructure {
		public  $Id;
		public  $Length;
		public  $Description;
		private $Structure;

		// Crea una estructura de paquete a partir de un SimpleXMLElement
		function __construct(SimpleXMLElement $packet) {
			list($PacketId, $PacketLength, $PacketDescription) = array('', '', '');

			$this->Id          = &$PacketId;
			$this->Length      = &$PacketLength;
			$this->Description = &$PacketDescription;

			// Examina los atributos del paquete
			foreach ($packet->attributes() as $aname => $avalue) {
				switch (trim(strtolower($aname))) {
					case 'id':          $PacketId          = trim($avalue); break;
					case 'length':      $PacketLength      = trim($avalue); break;
					case 'description': $PacketDescription = trim($avalue); break;
					case 'structure':   $this->Structure   = trim($avalue); break;
				}
			}

			if (substr($PacketId, 0, 2) == '0x') {
				// Hexadecimal
				$PacketId = hexdec(substr($PacketId, 2));
			//} else if (substr($PacketId, 0, 1) == '0') {
			//  // Octal
			//	$PacketId = octdec(substr($PacketId, 1));
			} else {
				// Decimal
				$PacketId = (int)$PacketId;
			}

			if ($PacketLength == '-') {
				$PacketLength = -1;
			} else {
				$PacketLength = (int)$PacketLength;
			}

			if (!isset($this->Structure)) $this->Structure = $this->ProcessEntryGroup($packet);

			//echo $this->Structure . "\n";
		}

		private function ProcessEntryGroup(SimpleXMLElement $group) {
			$Structure = '';
			$NameList  = array();

			foreach ($group as $k => $entry) {
				list($name, $type, $length, $unused, $data) = array(null, '', 0, false, null);

				foreach ($entry->attributes() as $aname => $avalue) {
					switch (strtolower(trim($aname))) {
						case 'name':   $name   = trim($avalue); break;
						case 'type':   $type   = strtolower(trim($avalue)); break;
						case 'length': $length = trim($avalue); break;
						case 'unused': $unused = strtolower(trim($avalue)); break;
					}
				}

				if ($unused == 'false') {
					$unused = false;
				} else {
					$unused = (bool)$unused;
				}

				if ($name != null && !$unused) $NameList[] = $name;

				switch (strtolower(trim($type))) {
					case 'uint8':  case 'int8':  $Structure .= 'b'; break;
					case 'uint16': case 'int16': $Structure .= 'w'; break;
					case 'uint32': case 'int32': $Structure .= 'l'; break;
					case 'pos24':                $Structure .= 'p'; break;
					case 'pos40':                $Structure .= 'q'; break;
					case 'stringz':              $Structure .= 'z[' . $length . ']'; break;
					case 'string':               $Structure .= 's[' . $length . ']'; break;
					case 'group':
						$Structure .= 'x[' . $length . '][' . $this->ProcessEntryGroup($entry) . ']';
					break;
					default:
						throw(new Exception('Unknown Type (' . $type . ')'));
					break;
				}
				//if (isset($data) && $data != null) $Structure[$name]['Data'] = $data;
			}

			$Structure = 'a[' . implode(';', $NameList) . ']' . $Structure;

			return $Structure;
		}

		public function Extract(string $String) {
		}
	}

	// Packets/PacketList.php

	//import('Packets.PacketStructure');

	class PacketList {
		private $PacketStructures = array();

		static public function LoadFromFile($FileName) {
			$Return = new PacketList();

			$o = simplexml_load_file($FileName);
			foreach ($o as $k => $packet) {
				if (strtolower($k) == 'packet') {
					$packeto = new PacketStructure($packet);
					$Return->PacketStructures[$packeto->Id] = &$packeto;
				}
			}

			return $Return;
		}

		public function Merge(PacketList $PacketList) {
			foreach (array_keys($PacketList->PacketStructures) as $id) {
				$this->PacketStructures[$id] = $PacketList->PacketStructures[$id];
			}
		}
	}

	print_r(
	PacketList::LoadFromFile(dirname(__FILE__) . '/packets.xml')
	);
?>