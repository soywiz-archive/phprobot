<?php
	import('Net.Socket');
	import('Packets.PacketList');

	class SocketPacket extends Socket {
		private $PacketList;
		private $SockBuffer = '';

		function __construct(PacketList $PacketList) {
			$this->PacketList = $PacketList;
		}

		function HavePackets() {
			if ($this->Connected) {
				// Extrae Información
				if ($this->GetReadLength() > 0) {
					if (!(($read = $this->Extract(10240)) === false || $read == '')) {
						$this->SockBuffer .= $read;
					} else {
						$this->Connected = false;
					}
				}

				$s = &$this->SockBuffer;

				// Comprueba información
				if (strlen($s) >= 2) {
					$p = GetR16(substr($s, 0, 2));

					$l = $this->PacketList->PacketLength($p);

					if ($l !== false) {
						if ($l == -1) $l = GetR16(substr($s, 2, 2));

						if (strlen($s) >= $l) return true;
					} else {
						// Paquete no definido
						throw(new Exception('Paquete desconocido: 0x' . dechex($p) . '. Borrando buffer (Es posible que se pierda información)' . "\n"));
						$s = '';
						//throw(new Exception('Paquere desconocido 0x' . dechex($p)));
					}
				}
			}

			return false;
		}

		function ExtractPacket() {
			if ($this->HavePackets()) {
				$s = &$this->SockBuffer;

				$p = ExtR16($s);

				// No está definida la estructura del paquete
				if (($l = $this->PacketList->PacketLength($p)) === false) {
					return false;
				}

				if ($l == -1) $l = ExtR16($s);

				//return array($p, StrShift($s, $l));
				$se = StrShift($s, $l);
				return array($p, $this->PacketList->Packet($p)->Extract($se), $se);
			}

			return false;
		}
	}
?>