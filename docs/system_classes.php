<?php
	class Error {
		public $Id;
		public $Text;
	}

	class Socket {
		protected $Sock;
		protected $Connected;

		function Connect($Ip, $Port) {
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
				socket_close($this->Sock);
				$this->Connected = false;
			}
		}

		function Extract($Length) {
			return $this->Connected ? @socket_read($this->Sock, $Length) : false;
		}

		function Send($Data) {
			if ($this->Connected) {
				@socket_write($this->Sock, $Data);
				return true;
			}

			return false;
		}
	}

	class PacketStructure {
		function Length() {
		}
	}

	class PacketList {
		private $PacketStructures;
	}

	class PacketSocket : Socket {
		private $PacketList;
	}

	class Connection {
		private $SocketPacket;
		private $ConnectionStep;
		private $ConnectionStatus;
	}

	class Map {
	}

	class Position {
		public $X;
		public $Y;
	}

	class Direction {
		public $Body;
		public $Head;
	}

	class Path {
		public $Positions;

		function Length() {
			return sizeof($this->Positions);
		}
	}

	class Moving {
		public $Path;
		public $PositionFrom;
		public $PositionTo;
		public $Velocity;
		public $TimeFrom;
		public $TimeTo;
	}

	class Entity {
		public $Id;
		public $Name;
		public $Map;
		public $Position;
	}

	class EntityPortal extends Entity {
	}

	class EntityMoveable extends Entity {
		public $Moving;
		public $Direction;
	}

	class EntityMoveableMonster extends EntityMoveable {
	}

	class EntityMoveableNPC extends EntityMoveable {
	}

	class EntityMoveablePlayer extends EntityMoveable {
	}

	class EntityMoveablePlayerMain extends EntityMoveablePlayer {
	}

	class GenericBot extends EntityMoveablePlayerMain {
		protected $Connection;
	}
?>