<?php
	Import('Ragnarok.Status');
	Import('Entity.EntityType');
	Import('Entity.*');
/*
	class EntityType {
		const UNKNOWN   = 0;
		const PORTAL    = 1;
		const ENEMY     = 2;
		const PLAYER    = 3;
		const NPC       = 4;
		const PET       = 5;
	}
*/
	class EntityList {
		private $ListId      = array();
		private $ListName    = array();
		private $ListVisible = array();
		private $ListType    =
			array(
				EntityType::UNKNOWN = array(),
				EntityType::PORTAL  = array(),
				EntityType::ENEMY   = array(),
				EntityType::PLAYER  = array(),
				EntityType::NPC     = array(),
				EntityType::PET     = array()
			);

		function GetListByType($Type) {
		}

		function GetListByVisibility() {
		}

		function GetEntityByName($Name) {
		}

		function GetEntityById($Id) {
		}

		// Entity
		function Register(Entity &$Entity) {
		}

		function Update(Entity &$Entity) {
		}

		function UnRegister(Entity &$Entity) {
		}
	}
?>