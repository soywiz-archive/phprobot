<?php
	class Entity {
		public $Id;
		public $Name;
		public $Map;
		public $Position;
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