<?php
/*

Directories:

	bin
		php5ts.dll
		php.exe
		php.ini
		system.php
	map
		mapas
	cfg
		...
	lib
		php_grf.dll
		php_path.dll
		php_bz2.dll
	src
		Net
			Socket
		Entity
			...
	util
Notas:
	En "php.ini" el fichero de preppend debe ser "system.php"

Interfaz de las extensiones:

php_grf.dll

class Grf {
	function __construct($filename) {
		//...
	}

	function __destruct() {
		//...
	}

	function Find($name) {
		//...
		// return id
	}

	function Read($id) {
		// ...
		// return string
	}
}

php_map.dll

class Map {
	public $width, $height;

	function __construct($width, $height, $string) {
		//...
	}

	function __destruct() {
		//...
	}

	function Get($x, $y) {
		//...
	}

	function Put($x, $y, $type) {
		//...
	}

	// -------------------------------------
	// $type
	//    0 - Buscar camino (andar)
	//    1 - Buscar camino (flecha/hechizo)
	// -------------------------------------

	function Find($x1, $y1, $x2, $y2, $type) {
		//...
		return Path;
	}
}

*/

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