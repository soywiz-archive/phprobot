<?php
	Import('Ragnarok.Status');
	Import('Entity.*');

	class EntityList {
		public  $ListId      = array();
		private $ListName    = array();
		private $ListVisible = array();
		private $ListType    =
			array(
				EntityType::UNKNOWN => array(),
				EntityType::PORTAL  => array(),
				EntityType::ENEMY   => array(),
				EntityType::PLAYER  => array(),
				EntityType::NPC     => array(),
				EntityType::PET     => array()
			);

		const ENTITYLIST_SIMILAR_NAME = 50;

		function Dump() {
			echo 'EntityList { ' . implode(', ', array_keys($this->ListId)) . " } \n";
		}

		function __construct() {
		}

		function GetListById() {
			return $this->ListId;
		}

		function GetListByType($Type) {
			return isset($this->ListType[$Type]) ? $this->ListType[$Type] : array();
		}

		function GetListByVisibility() {
			return $this->ListVisible;
		}

		function GetEntityByName($Name) {
			return isset($this->ListName[$Name]) ? $this->ListName[$Name] : false;
		}

		function GetEntityById($Id) {
			return isset($this->ListId[$Id]) ? $this->ListId[$Id] : false;
		}

		function GetEntityByIdCreate($Id, $Type = EntityType::UNKNOWN) {
			if (!isset($this->ListId[$Id])) {
				$Entity = new Entity($this, $Id);
				$Entity->Type = $Type;
				$this->ListId[$Id] = $Entity;
			} else {
				$Entity = $this->ListId[$Id];
			}

			return $Entity;
		}

		// Entity
		function Register(Entity &$Entity) {
			$this->ListId[$Entity->Id]                  = &$Entity;
			$this->ListType[$Entity->Type][$Entity->Id] = &$Entity;
			if (isset($Entity->Visible) && $Entity->Visible)   $this->ListVisible[$Entity->Visible] = &$Entity;
			if (isset($Entity->Name) && strlen($Entity->Name)) $this->ListName[$Entity->Name]       = &$Entity;
		}

		function Update(Entity &$Entity, $LId = null) {
			if (isset($LId)) {
				unset($this->ListId[$LId]);
				$this->ListId[$Entity->Id] = $Entity;
			}
		}

		function UnRegister(Entity &$Entity) {
			if (isset($this->ListId[$Entity->Id])) unset($this->ListId[$Entity->Id]);
		}

		/*
		static function SelectByNameSimilar() {
			$similar = array();
			foreach ($this->characterList as $id => $_character) $similar[$id] = str_replace(' ', '_', strtolower(trim($_character->name)));

			$similar = getSimilarArray($similar, str_replace(' ', '_', strtolower(trim($character))));

			$ms = &$this->characterList[$id = array_shift(array_keys($similar))];
			$this->characterSelected = &$ms;

			$this->connectionData['id_character']  = $ms->id;

			sendCharaSelect($this, $id);

			$this->step = GB_STEP_CHARA_PROCESS;
		}
		*/

		public function GetEntityBySimilarName($Name, $MapName = null) {
			//foreach ($this->GetListBySimilarName($Name, $MapName) as $v) {
				//return ($v[1] > 70) ? $v[0] : false;
			//}
			$l = $this->GetListBySimilarName($Name, $MapName);
			return sizeof($l) ? $l[0][0] : false;
		}

		public function GetListBySimilarName($Name, $MapName = null) {
			$Name     = strtolower(trim($Name));
			$z        = &$this->ListId;
			$Return   = array();
			$Percents = array();
			$Entities = array();
			$Fixn     = 0;

			foreach ($z as $k => $e) {
				similar_text(strtolower(trim($e->Name)), $Name, $Per);

				$MapSame = isset($MapName) ? ($e->MapName == $MapName) ? 1 : 0 : 1;

				$Per *= $MapSame ? $e->Visible ? 1 : 0.75 : 0.50;

				$Entities['f' . (string)$Fixn] = $e;
				$Percents['f' . (string)$Fixn] = round($Per, 3);
				$Fixn++;
				//echo "Similar-Entity: " . $e->name . ' - ' . round($per, 2) . "\n";
			}

			arsort($Percents);
			$Percents = array_slice($Percents, 0, 5);
			foreach ($Percents as $k => $Percent) $Return[] = array(&$Entities[$k], $Percent);

			return $Return;
		}

		// Acquire, Release for Dump
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
?>