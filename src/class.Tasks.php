<?php
	require_once(dirname(__FILE__) . '/system.php');

	require_class('Exception');

	class Tasks {
		protected $task_list = array();

// ----------------------------------------------------------------------------
// ADD
// ----------------------------------------------------------------------------

		function add($task)      { if (!is_array($task) || sizeof($task) < 1) throw(new Exception()); $this->addLast($task); }
		function addFirst($task) { if (!is_array($task) || sizeof($task) < 1) throw(new Exception()); array_unshift($this->task_list, $task); }
		function addLast($task)  { if (!is_array($task) || sizeof($task) < 1) throw(new Exception()); $this->task_list[] = $task; }
		function addAt($task, $pos) {
			if (!is_array($task) || sizeof($task) < 1) throw(new Exception());
			if ($pos < 0) $pos = sizeof($this->task_list) + $pos;
			$rest = array_splice($this->task_list, $pos);
			$this->task_list[] = $task;
			foreach ($rest as $ctask) $this->task_list[] = $ctask;
		}

// ----------------------------------------------------------------------------
// DEL
// ----------------------------------------------------------------------------

		function del($mask) {
			if (!is_array($mask)) $mask = array();

			do {
				foreach ($this->task_list as $e => $tv) {
					$eq = true;
					foreach ($mask as $tm) {
						if (array_shift($tv) != $tm) { $eq = false; break; }
					}
					if ($eq) { $this->delAt($e); break;}
				}
			} while ($eq);
		}

		function delFirst() { array_shift($this->task_list); }
		function delLast() { array_pop($this->task_list); }

		function delAt($pos) {
			array_splice($this->task_list, $pos, 1);
		}

// ----------------------------------------------------------------------------
// RUN
// ----------------------------------------------------------------------------

		function run(&$o) { return $this->runFirst($o); }
		function runFirst(&$o) {
			if (sizeof($this->task_list) > 0) Tasks::runTask($o, array_shift($this->task_list));
			return sizeof($this->task_list);
		}

		function runLast(&$o) {
			if (sizeof($this->task_list) > 0) Tasks::runTask($o, array_pop($this->task_list));
			return sizeof($this->task_list);
		}

		function runAt(&$o, $pos) {
			if (sizeof($this->task_list) > 0) Tasks::runTask($o, array_splice($this->task_list, $pos, 1));
			return sizeof($this->task_list);
		}

		static function runTask(&$o, $task) {
			$method = array_shift($task);
			call_user_func_array(array($o, $method), $task);
			return true;
		}
	}
/*
	$a = new Tasks();
	$a->add(array(1));
	$a->add(array(2));
	$a->add(array(3));
	$a->addFirst(array(4));
	$a->addAt(1, array(5));
	$a->addAt(0, array(6));
	$a->addAt(-1, array(7));

	$a->del(array(1));
	print_r($a);
	$a->delLast();
	print_r($a);
*/
?>