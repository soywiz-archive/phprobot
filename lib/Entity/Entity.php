<?php
	Import('Ragnarok.Status');
	Import('Ragnarok.EntityList');

	class Entity {
		public $EntityList = null;
		public $Id;
		public $Name;
		public $Map;
		public $MapName;
		public $Position;
		public $Sex        = Sex::UNKNOWN;
		public $Visible    = false;
		public $Type;

		protected function SetType()      { $this->Type = EntityType::UNKNOWN; }
		protected function SetName($Name) { $this->Name = $Name; $this->Update(); }

		public function Dump() {
			$__Acquire = $this->__ListAcquire(array('EntityList'));

			print_r($this);

			$this->__ListRelease($__Acquire);
		}

		public function Update() {
			$this->EntityList->Update($this);
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
			$this->Id = $Id;
			if ($EntityList instanceof EntityList) $this->EntityInit($EntityList);
		}

		function __destruct() {
			$this->EntityDestroy();
		}

		protected function __ListAcquire($List) {
			$Return = array();
			foreach ($List as $v) {
				$Return[$v] = &$this->$v;
				//$this->$v   = null;
				$this->$v   = '{' . $v . '}';
			}
			return $Return;
		}

		protected function __ListRelease($List) {
			foreach ($List as $k => $v) $this->$k = &$v;
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