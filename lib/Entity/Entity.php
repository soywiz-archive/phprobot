<?php
	Import('Ragnarok.Status');
	Import('Ragnarok.EntityList');

	class Entity {
		public $EntityList;
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
			$EntityList = &$this->EntityList;
			$this->EntityList = null;

			print_r($this);

			$this->EntityList = &$EntityList;
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
			if (isset($this->EntityList)) $this->EntityList->Unregister($this);
		}

		function __construct(EntityList &$EntityList, $Id) {
			$this->Id = $Id;
			$this->EntityInit($EntityList);
		}

		function __destruct() {
			$this->EntityDestroy();
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