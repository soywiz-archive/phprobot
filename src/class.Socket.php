<?php
	require_once(dirname(__FILE__) . '/system.php');

	class Socket {
		protected $sock;
		protected $sockConnected;
		protected $sockBuffer;

		function connect($ip, $port) {
			$this->sockBuffer = '';

			list($ip, $port) = getIpAndPort($ip, $port);

			if (!extension_loaded('sockets') && !dl('php_sockets.dll')) return false;

			if ($this->sock = @socket_create(AF_INET, SOCK_STREAM, 0)) {
				if (@socket_set_option($this->sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
					if (@socket_connect($this->sock, @gethostbyname($ip), $port)) {
						$this->sockConnected = true;
						return true;
					}
				}
			}

			return false;
		}

		function close() {
			if ($this->isAlive()) {
				socket_close($this->sock);
				$this->sockConnected = false;
			}
		}

		function isAlive()     { return $this->sockConnected; }
		function extract($len) { return $this->isAlive() ? @socket_read($this->sock, $len) : false; }

		function send($data) {
			if ($this->isAlive()) { @socket_write($this->sock, $data); return true; }
			return false;
		}
	}
?>