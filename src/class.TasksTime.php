<?php
	require_once(dirname(__FILE__) . '/system.php');

	require_class('Exception');
	require_class('Tasks');
	require_class('Timer');

	class TasksTime extends Tasks {

// ----------------------------------------------------------------------------
// ADD
// ----------------------------------------------------------------------------

		function add($task, $time = NULL)         { if (!isset($time)) $time = new Timer(); return parent::addLast(array($time, $task)); }
		function addFirst($task, $time = NULL)    { if (!isset($time)) $time = new Timer(); return parent::addFirst(array($time, $task)); }
		function addLast($task, $time = NULL)     { if (!isset($time)) $time = new Timer(); return parent::addLast(array($time, $task)); }
		function addAt($task, $pos, $time = NULL) { if (!isset($time)) $time = new Timer(); return parent::addAt(array($time, $task), $pos); }

// ----------------------------------------------------------------------------
// DEL
// ----------------------------------------------------------------------------

		function del($mask) {
			if (!is_array($mask)) $mask = array();

			do {
				foreach ($this->task_list as $e => $tv2) {
					$tv = $tv2[1];
					$eq = true;
					foreach ($mask as $tm) {
						if (array_shift($tv) != $tm) { $eq = false; break; }
					}
					if ($eq) { $this->delAt($e); break;}
				}
			} while ($eq);
		}

		function delFirst() { array_shift($this->task_list); }
		function delLast()  { array_pop($this->task_list); }

		function delAt($pos) {
			array_splice($this->task_list, $pos, 1);
		}

		function delBefore($time) {
			if (!isset($time)) $time = new Timer();

			foreach ($this->task_list as $e => $tv2) {
				$tv = $tv2[0];
				//if ($tv < $time) $this->delAt($e);
				if ($time->dist($tv) > 0) $this->delAt($e);
			}
		}

		function delAfter($time) {
			if (!isset($time)) $time = new Timer();

			foreach ($this->task_list as $e => $tv2) {
				$tv = $tv2[0];
				if ($time->dist($tv) < 0) $this->delAt($e);
			}
		}

// ----------------------------------------------------------------------------
// RUN
// ----------------------------------------------------------------------------

		function runAll(&$o, $time = NULL) {
			if (!isset($time)) $time = new Timer();

			$return = false;
			do {
				$next = false;
				foreach ($this->task_list as $k => $v) {
					if ($this->runAt($o, $k, $time)) { $next = true; $return = true; break; }
				}
			} while ($next);

			return $return;
		}

		function run(&$o, $time = NULL) {
			if (!isset($time)) $time = new Timer();

			return $this->runFirst($o, $time);
		}

		function runFirst(&$o, $time = NULL) {
			if (!isset($time)) $time = new Timer();

			return $this->runAt($o, 0, $time);
		}

		function runLast(&$o, $time = NULL) {
			if (!isset($time)) $time = new Timer();

			return $this->runAt($o, sizeof($this->task_list) - 1, $time);
		}

		function runAt(&$o, $pos, $time = NULL) {
			if (!isset($time)) $time = new Timer();

			if ((sizeof($this->task_list) > 0) && ($this->task_list[$pos][0]->dist($time) >= 0)) {
				$p = array_splice($this->task_list, $pos, 1);
				Tasks::runTask($o, $p[0][1]);
				return true;
			}
			return false;
		}

	}
	class test {
		function __construct() { }

		function test($a, $b) {
			echo "$a, $b\n";
		}
	}

	/*

	$o = new test();

	$a = new TasksTime();
	//$a->add(time() + 10, array('test', 10, 20));
	$a->add(time() + 10, array('test', 10, 20));
	$a->add(time(), array('test', 10, 20));
	$a->add(time(), array('test', 10, 20));
	$a->runAll($o);
	print_r($a);
	*/
?>