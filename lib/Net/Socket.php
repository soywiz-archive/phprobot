<?php
	Import('System.Buffer');

	class Socket {
		protected $Sock;
		protected $Connected;
		protected $Simulated;
		protected $SimulatedBuffer;

		function Connect($Ip = null, $Port = null) {
			// Simular conexión
			if (!isset($Ip) || !isset($Port)) {
				$this->Connected       = true;
				$this->Simulated       = true;
				$this->SimulatedBuffer = '';
				return true;
			}

			$this->Simulated = false;

			if (!extension_loaded('sockets') && !dl('php_sockets.dll')) return false;

			if ($this->Sock = @socket_create(AF_INET, SOCK_STREAM, 0)) {
				if (@socket_set_option($this->Sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
					if (@socket_connect($this->Sock, @gethostbyname($Ip), $Port)) {
						$this->Connected = true;
						return true;
					}
				}
			}

			return false;
		}

		function Close() {
			if ($this->Connected) {
				if (!$this->Simulated) socket_close($this->Sock);
				$this->Connected = false;
			}
		}

		function Extract($Length) {
			if (!$this->Simulated) {
				return $this->Connected ? @socket_read($this->Sock, $Length) : false;
			} else {
				return $this->Connected ? StrShift($this->SimulatedBuffer, $Length) : false;
			}
		}

		function Send($Data) {
			if ($this->Connected) {
				if (!$this->Simulated) @socket_write($this->Sock, $Data);
				return true;
			}

			return false;
		}

		function GetReadLength() {
			if ($this->Connected) {
				if (!$this->Simulated) {
					$ls = array($this->Sock);
					//return @socket_select($ls, $a = null, $a = null, 0);
					return @socket_select($ls, null, null, 0);
				} else {
					return strlen($this->SimulatedBuffer);
				}
			}
		}
	}
?>