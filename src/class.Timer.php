<?php
	require_once(dirname(__FILE__) . '/system.php');

	class Timer {
		protected $relative;
		protected $sec;
		protected $msec;

		function __construct($time = NULL, $madd = NULL, $relative = false) {
			if (!isset($time)) $time = microtime();
			if (!isset($add))  $add  = 0;
			$this->relative = $relative;

			if (!$relative) {
				if ($time instanceof Timer) {
					list($this->msec, $this->sec) = array($time->sec, $time->msec);
				} else {
					list($this->msec, $this->sec) = explode(' ', $time, 2);
				}
			} else {
				$this->msec = $this->sec = 0;
			}

			$this->msec  = (int)($this->msec * 1000);
			$z = ($this->msec += $madd);
			$this->msec %= 1000;
			$this->sec  += floor($z / 1000);
		}

		function __toString() {
			return $this->sec . '.' . $this->msec;
		}

		function dist($time = NULL) {
			if (!isset($time)) $time = new Timer();
			if (!($time instanceof Timer)) new Timer(NULL, $time, true);
			return ((($time->sec - $this->sec) * 1000) + ($time->msec - $this->msec));
		}

		function add($time) {
			if (!($time instanceof Timer)) $time = new Timer(NULL, $time, true);

			$return = new Timer();
			$z = ($return->msec = $this->msec + $time->msec);
			$return->msec %= 1000;
			$return->sec = $this->sec + $time->sec + floor($z / 1000);

			return $return;
		}
	}
?>