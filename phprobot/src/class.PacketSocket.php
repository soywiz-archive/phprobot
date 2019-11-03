<?php
	require_once(dirname(__FILE__) . '/system.php');

	require_class('PacketList');
	require_class('Socket');

	class PacketSocket extends Socket {
		private $packetList;

		function __construct(PacketList $pl) {
			$this->packetList = $pl;
		}

		function havePackets() {
			if ($this->isAlive()) {
				// Extrae Información
				$ls = array($this->sock);
				if (@socket_select($ls, $a = NULL, $a = NULL, 0) > 0) {
					if (!(($read = @socket_read($this->sock, 10240)) === false || $read == '')) {
						$this->sockBuffer .= $read;
					} else {
						$this->sockConnected = false;
					}
				}

				$s = &$this->sockBuffer;

				// Comprueba información
				if (strlen($s) >= 2) {
					$packet_len = &$this->packetList->packet_len;
					$p = getr16(substr($s, 0, 2));

					if (isset($packet_len[$p])) {
						if (($l = $packet_len[$p]) < 2) $l = getr16(substr($s, 2, 2));

						if (strlen($s) >= $l) return true;
					} else {
						// Paquete no definido
						$s = '';
						throw(new Exception('Paquere desconocido 0x' . dechex($p)));
						//trigger_error("Paquete desconocido: 0x" . dechex($p) . ". Borrando buffer (Es posible que se pierda información)\n");
					}
				}
			}

			return false;
		}

		function extractPacket() {
			if ($this->havePackets()) {
				$packet_len = &$this->packetList->packet_len;

				$s = &$this->sockBuffer;

				$p = exr16($s);

				if (!isset($packet_len[$p])) return false;
				if (($l = $packet_len[$p] - 2) <= -2) $l = exr16($s) - 4;

				return array($p, str_shift($s, $l));
			}

			return false;
		}
	}
?>