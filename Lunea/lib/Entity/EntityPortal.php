<?php
	Import('Entity.Entity');

	class EntityPortal extends Entity {
		protected function SetType($Type = EntityType::PORTAL) { parent::SetType($Type); }
	}
?>