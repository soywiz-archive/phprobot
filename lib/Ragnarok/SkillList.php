<?php
	class Skill {
		public $Id;
		public $Target;
		public $LevelMax;
		public $SpMax;
		public $Range;
		public $Name;
		public $CanUp;

		function __construct($Id, $Target, $LevelMax, $SpMax, $Range, $Name, $CanUp) {
			list(
				$this->Id,
				$this->Target,
				$this->LevelMax,
				$this->SpMax,
				$this->Range,
				$this->Name,
				$this->CanUp
			) = array(
				$Id,
				$Target,
				$LevelMax,
				$SpMax,
				$Range,
				$Name,
				$CanUp
			);
		}

		// OnSelf, OnPlayer, OnEnemy, OnAuto
		function UseOn(Entity &$Entity) {
		}

		// OnMap
		function UseAt(Position &$Position) {
		}
	}

	class SkillList {
		private $ListId = array();

		// Skill
		function Register(Skill &$Skill) {
			$this->ListId[$Skill->Id] = &$Skill;
		}

		function Update(Skill &$Skill) {
		}

		function UnRegister(Skill &$Skill) {
			if (isset($this->ListId[$Skill->Id])) unset($this->ListId[$Skill->Id]);
		}
	}
?>