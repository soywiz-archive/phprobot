<?php
	if (!defined('GB_SYSTEM_LODADED')) die('Se requiere el sistema de GenericBot');

	require_once(dirname(__FILE__) . '/system.php');

	require_class('Exception');
	require_class('Timer');

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	class IdList {
		public $o;
		public $id;
		public $register;

		function trace() {
			$o = &$this->o;
			unset($this->o);
			print_r($this);
			$this->o = $o;
		}

		function __construct(GenericBot &$o, $id, $register = true) {
			$this->id = $id;
			if ($this->register = $register) {
				$this->o  = $o;
			}

			$this->setOnList(true);
		}

		function disappear() {
			$this->setOnList(false);
		}

		function setOnList($flag) {
			if ($this->register) {
				if ($flag) {
					// Añade a la lista de visibles y de memorizados
					if (($gpc = get_parent_class($this)) != __CLASS__) {
						$z = &$this->o->lists[$gpc]; if (!isset($z)) $z = array();
						$this->o->lists[$gpc][$this->id] = &$this;

						$z = &$this->o->lists[$gpc . '_memo']; if (!isset($z)) $z = array();
						$this->o->lists[$gpc . '_memo'][$this->id] = &$this;
					}

					$z = &$this->o->lists[$gpc]; if (!isset($z)) $z = array();
					$this->o->lists[get_class($this)][$this->id]      = &$this;

					$z = &$this->o->lists[$gpc . '_memo']; if (!isset($z)) $z = array();
					$this->o->lists[get_class($this) . '_memo'][$this->id] = &$this;
				} else {
					// Borra solo de la lista de visibles
					if (($gpc = get_parent_class($this)) != __CLASS__) {
						unset($this->o->lists[get_class($this)][$this->id]);
					}

					unset($this->o->lists[get_class($this)][$this->id]);
				}
			}
		}

		function __destruct() {
			if ($this->register) {
				if (isset($this->o)) {
					// Lo borra de la lista de visibles y de memorizados
					if (($gpc = get_parent_class($this)) != __CLASS__) {
						unset($this->o->lists[$gpc][$this->id]);
						unset($this->o->lists[$gpc . '_memo'][$this->id]);
					}

					unset($this->o->lists[get_class($this)][$this->id]);
					unset($this->o->lists[get_class($this) . '_memo'][$this->id]);
				}
			}
		}
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	class Entity extends IdList {
		public $_name;
		//protected $_name;

		// Movimiento de la entidad
		public $path = array();
		public $moving = false;
		public $from_x = 0;
		public $from_y = 0;
		public $from_time;
		public $to_x = 0;
		public $to_y = 0;
		public $to_time_t;

		public $x;
		public $y;
		public $view_class;
		public $group;
		public $speed;

		public $guild  = NULL;
		public $emblem = NULL;
		public $party  = NULL;
		public $party_leader = false;
		public $map_name;

		function trace() {
			$o1 = &$this->emblem; $this->emblem = NULL;
			$o2 = &$this->guild;  $this->guild  = NULL;
			$o4 = &$this->party;  $this->party  = NULL;

			parent::trace();

			$this->emblem = $o1;
			$this->guild  = $o2;
			$this->party  = $o3;
		}

		function setXY($x, $y) {
			$this->to_x = $this->from_x = $this->x = $x;
			$this->to_y = $this->from_y = $this->y = $y;
		}

		function move($from_x = NULL, $from_y = NULL, $to_x, $to_y, $speed = NULL) {
			if (!isset($from_x)) $from_x = $this->x;
			if (!isset($from_y)) $from_y = $this->y;
			if (!isset($speed))  $speed  = $this->speed;

			$this->path = (isset($this->o) && isset($this->o->map)) ? $this->o->map->getPath($from_x, $from_y, $to_x, $to_y) : array();

			$this->from_time = new Timer();
			$this->to_time_t = ($speed * sizeof($this->path));
			$this->moving    = true;
			$this->from_x    = $this->x = $from_x;
			$this->from_y    = $this->y = $from_y;
			$this->to_x      = $to_x;
			$this->to_y      = $to_y;
			$this->to_y      = $to_y;

			if (isset($this->o)) $this->o->onMoveStart($this);

			//$this->trace(); exit; // Debug
		}

		function __destruct() {
			if ($this->register) {
				if (isset($this->view_class)) {
					unset($this->o->lists['entity_view_class_name'][$this->view_class]);
				}

				if (isset($this->_name)) {
					unset($this->o->lists['entity_name'][strtolower(trim($this->_name))]);
				}

				parent::__destruct();
			}
		}

		function __set($name, $val) {
			if ($this->register) {
				switch ($name) {
					case 'name':
						$entity_name_list = &$this->o->lists['entity_name_list']; $entity_view_class_name_list = &$this->o->lists['entity_view_class_name_list'];
						if ($this->view_class > 1000) {
							$entity_view_class_name_list[$this->view_class] = $val;
						} else {
							if (isset($this->_name) && isset($entity_name_list[$ns = strtolower(trim($this->_name))])) unset($entity_name_list[$ns]);

							$entity_name_list[strtolower(trim($this->_name = $val))] = $this->id;
						}
					break;
					default:
						$this->$name = $val;
					break;
				}
			} else {
				$this->$name = $val;
			}
		}

		function __get($name) {
			if ($this->register) {
				switch ($name) {
					case 'name':
						if (isset($this->_name)) return $this->_name;

						$entity_name_list = &$this->o->lists['entity_name_list']; $entity_view_class_name_list = &$this->o->lists['entity_view_class_name_list'];
						if (isset($entity_view_class_name_list[$this->view_class])) {
							$this->_name = $entity_view_class_name_list[$this->view_class];
						} else {
							// Se pide el nombre de la entidad
							sendGetEntityName($this->o, $this->id);
							$entity_name_list[$this->_name = 'unknown_' . $this->id] = $this->id;
						}
					break;
				}

				return isset($this->$name) ? $this->$name : false;
			}
		}

		static function getEntityById(GenericBot &$o, $id) {
			$z = &$o->lists['Entity_memo'][$id];
			return isset($z) ? $z : false;
		}

		static function getEntityByIdCreate(GenericBot &$o, $id) {
			$z = &$o->lists['Entity_memo'][$id];
			if (!isset($z)) $z = new Entity($o, $id);
			return $z;
		}

		static function getEntityByName(GenericBot &$o, $name) {
			if (isset($o->lists['Entity_name'][strtolower(trim($name))])) {
				return entity::getEntityById(
					$o->lists['Entity_name'][strtolower(trim($name))]
				);
			}

			return false;
		}

		static function getNameById(GenericBot &$o, $id) {
			return ($z = entity::getEntityById($o, $id)) ? $z->name : false;
		}

		static function deleteAll(GenericBot &$o) {
			foreach (get_declared_classes() as $class) {
				if ($class == __CLASS__ || get_parent_class($class) == __CLASS__) {
					if (isset($o->lists[$class . '_memo'])) {
						foreach ($o->lists[$class . '_memo'] as $k => $myo) {
							unset($o->lists[$class . '_memo'][$k]);
							unset($myo);
						}
					}

					if (isset($o->lists[$class])) {
						foreach ($o->lists[$class] as $k => $myo) {
							unset($o->lists[$class][$k]);
							unset($myo);
						}
					}
				}
			}
		}
	}

///////////////////////////////////////////////////////////////////////////////

	class Emblem extends IdList {
		public $data;

		static function getEmblemByIdCreate(GenericBot &$o, $id) {
			$z = &$o->lists['Emblem_memo'][$id];
			if (!isset($z)) $z = new Guild($o, $id);
			return $z;
		}
	}

///////////////////////////////////////////////////////////////////////////////

	class Guild extends IdList {
		public $name;
		public $emblem;
		public $member_list = array();

		function trace() {
			$o1 = &$this->emblem;      $this->emblem      = NULL;
			$o2 = &$this->member_list; $this->member_list = NULL;
			parent::trace();
			$this->emblem = $o1;
			$this->member_list = $o2;
		}

		static function getGuildByIdCreate(GenericBot &$o, $id) {
			$z = &$o->lists['Guild_memo'][$id];
			if (!isset($z)) $z = new Guild($o, $id);
			return $z;
		}
	}

	class Party extends IdList {
		public $name;
		public $share_exp;
		public $share_item;
		public $member_list = array();

		function getMemberNameList() {
			$list = array();
			foreach ($this->member_list as $member) $list[] = $member->name;
			return $list;
		}

		function trace() {
			$o2 = &$this->member_list; $this->member_list = NULL;
			parent::trace();
			$this->member_list = $o2;
		}

		static function getPartyByIdCreate(GenericBot &$o, $id) {
			$z = &$o->lists['Party_memo'][$id];
			if (!isset($z)) $z = new Party($o, $id);
			return $z;
		}
	}

///////////////////////////////////////////////////////////////////////////////

	class Player extends Entity {
		public $head;
		public $body;
	}

///////////////////////////////////////////////////////////////////////////////

	class MainPlayer extends Entity {
		public $hp;
		public $hp_max;
		public $sp;
		public $sp_max;
		public $flee;
		public $head;
		public $body;

		public $party;
	}

///////////////////////////////////////////////////////////////////////////////

	class Npc     extends Entity { }
	class Monster extends Entity { }
	class Pet     extends Entity { }
	class Warp    extends Entity { }

///////////////////////////////////////////////////////////////////////////////

	class Skill extends IdList {
		protected $_name = NULL;
		protected $_title;

		public $target;
		public $level_max;
		public $sp_max;
		public $range;
		public $canup;

		function __destruct() {
			if ($this->register) {
				unset($this->o->lists['Skill_name'][strtolower(trim($this->_title))]);
				unset($this->o->lists['Skill_title'][strtolower(trim($this->_name))]);

				parent::__destruct();
			}
		}

		function __toString() {
			return $this->title;
		}

		function __set($name, $val) {
			if ($this->register) {
				switch ($name) {
					case 'name':
						$vr = strtolower(trim($val));
						$skill_name_list = &$this->o->lists['skill_name_list'];
						if (!isset($this->_name)) unset($skill_name_list[$vr]);
						$this->_name = $val;
						$skill_name_list[$vr] = &$this;
					break;
					case 'title':
						$vr = strtolower(trim($val));
						$skill_title_list = &$this->o->lists['skill_title_list'];
						if (!isset($this->_name)) unset($skill_title_list[$vr]);
						$this->_title = $val;
						$skill_title_list[$vr] = &$this;
					break;
					default:
						$this->$name = $val;
					break;
				}
			}
		}

		function __get($name) {
			if ($this->register) {
				switch ($name) {
					case 'name':  return $this->_name; break;
					case 'title': return $this->_title; break;
				}

				return $this->$name;
			}
		}

		static function getSkillById(GenericBot &$o, $id)    { $z = &$o->lists['Skill_memo'][$id]; return isset($z) ? $z : $this->o->lists['Skill_memo'][-1]; }
		static function getSkillByTitle(GenericBot &$o, $id) { $z = &$o->lists['Skill_title'][strtolower(trim($id))]; return isset($z) ? $z : $o->lists['Skill_memo'][-1]; }
		static function getSkillByName(GenericBot &$o, $id)  { $z = &$o->lists['Skill_name'][strtolower(trim($id))]; return isset($z) ? $z : $o->lists['Skill_memo'][-1]; }

		static function load(GenericBot &$o, $file) {
			$z = &$o->lists['skill_names'];  $skill_names = &$z;  if (!isset($z)) $z = array();
			$z = &$o->lists['skill_titles']; $skill_titles = &$z; if (!isset($z)) $z = array();
			$z = &$o->lists['skill_delays']; $skill_delays = &$z; if (!isset($z)) $z = array();

			if (file_exists($file) && is_readable($file)) {
				foreach (file($file) as $line) {
					$line = trim($line);
					if (strlen($line) == 0 || substr($line, 0, 1) == "'" || substr($line, 0, 1) == '#' || substr($line, 0, 2) == '//') continue;
					$opts = explode(' ', $line, 3); $id = $opts[0];

					$w = explode('#', $opts[1], 2);
					$opts[1] = $w[0];
					if (!isset($w[1])) $w[1] = 0;

					$skill_titles[$id] = $opts[2];
					$skill_names[$id]  = $opts[1];
					$skill_delays[$id] = $w[1];
					define('SKILL_' . $opts[1], $id);
				}
			}
		}
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

?>