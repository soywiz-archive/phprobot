<?php
	Import('Ragnarok.Status');
	Import('Ragnarok.EntityList');

	class Entity {
		static public $EntityIdCt = 0;
		public $EntityId;
		public $EntityList = null;
		public $__Id;
		public $Name;
		public $Map;
		public $MapName;
		public $Position;
		public $Sex        = Sex::UNKNOWN;
		public $Visible    = false;
		public $Type;

		protected function SetType()      { $this->Type = EntityType::UNKNOWN; }
		protected function SetName($Name) { $this->Name = $Name; $this->Update(); }

		public function __set($k, $v) {
			switch (strtolower($k)) {
				case 'id':
					$bid = $this->Id;
					$this->Id = $v;
					$this->Update($bid);
				break;
				default:
					$this->$k = $v;
				break;
			}
		}

		public function __get($k) {
			switch (strtolower($k)) {
				case 'id':
					return $this->__Id;
				break;
				default:
					return $this->$k;
				break;
			}
		}

		public function Dump() {
			$__Acquire = $this->__ListAcquire(array('EntityList'));

			print_r($this);

			$this->__ListRelease($__Acquire);
		}

		public function Update($Id = null) {
			$this->EntityList->Update($this, $Id);
		}

		protected function EntityInit(EntityList &$EntityList) {
			$this->EntityList = &$EntityList;
			$this->SetType();

			$EntityList->Register($this);
		}

		protected function EntityDestroy() {
			if (isset($this->EntityList) && $this->EntityList instanceof EntityList)
				$this->EntityList->UnRegister($this);
		}

		function __construct($EntityList, $Id) {
			$this->EntityId = Entity::$EntityIdCt++;
			$this->__Id = $Id;
			if ($EntityList instanceof EntityList) $this->EntityInit($EntityList);
		}

		function __destruct() {
			$this->EntityDestroy();
		}

		protected function __ListAcquire($List) {
			$Return = array();
			foreach ($List as $v) {
				$Return[$v] = $this->$v;
				//$this->$v   = null;
				$this->$v   = '{' . $v . '}';
			}
			return $Return;
		}

		protected function __ListRelease($List) {
			foreach ($List as $k => $v) $this->$k = $v;
		}
	}

	class Position {
		public $X;
		public $Y;

		function __construct($X, $Y) {
			list($this->X, $this->Y) = array($X, $Y);
		}
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
?>