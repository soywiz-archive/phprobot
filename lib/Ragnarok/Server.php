<?php
	class ServerChara {
		public $Ip;
		public $Port;
		public $Name;
		public $Count;

		function __construct($Ip, $Port, $Name, $Count) {
			list($this->Ip, $this->Port, $this->Name, $this->Count) = array($Ip, $Port, $Name, $Count);
		}
	}
?>