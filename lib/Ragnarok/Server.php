<?php
	class Server {
		public $Ip;
		public $Port;
		public $Name;
		public $Count;

		function __construct($Ip, $Port, $Name, $Count) {
			list($this->Ip, $this->Port, $this->Name, $this->Count) = array($Ip, $Port, $Name, $Count);
		}
	}

	class ServerMaster extends Server { }
	class ServerChara  extends Server { }

	class ServerZone   extends Server {
		public $MapName;

		function __construct($MapName, $Ip, $Port) {
			list($this->MapName, $this->Ip, $this->Port) = array($MapName, $Ip, $Port);
		}
	}
?>