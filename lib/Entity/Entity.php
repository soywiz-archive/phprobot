<?php
	Import('Ragnarok.Status');
	Import('Entity.EntityType');

	class Entity {
		public $EntityList;
		public $Id;
		public $Name;
		public $Map;
		public $Position;
		public $Sex        = Sex::UNKNOWN;
		public $Visible    = false;
		public $Type;

		private SetType() { parent::SetType(EntityType::UNKNOWN); }
		private SetName() { $this->EntityList->Update($this); }

		function __construct(EntityList &$EntityList) {
			$this->SetType();
			$this->EntityList = &$EntityList;
			$EntityList->Register($this);
		}

		function __destruct() {
			$this->EntityList->Unregister($this);
		}
	}

	class Position {
		public $X;
		public $Y;
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