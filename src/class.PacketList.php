<?php
	require_once(dirname(__FILE__) . '/system.php');

	class PacketList {
		public $packet_len = array();
		public $packet_des = array();

		function add($id, $len, $des = NULL) {
			$this->packet_len[$id] = $len;
			$this->packet_des[$id] = isset($des) ? $des : 'Undefined';
		}

		function load($file) {
			if (file_exists($file)) {
				foreach (file($file) as $line) {
					if (strlen($line = trim($line)) == 0) continue;
					if ($line[0] == ';' || $line[0] == '#') continue;

					$data = explode(' ', $line, 3);

					if (sizeof($data) < 2) continue;

					$this->add(hexdec($data[0]), $data[1], isset($data[2]) ? $data[2] : NULL);
				}
			}
		}

		function __construct($file = NULL) {
			if (!isset($file)) return;
			if (!is_array($file)) $file = array($file);
			foreach ($file as $f) $this->load($f);
		}
	}
?>