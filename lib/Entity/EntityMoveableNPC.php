<?php
	Import('Entity.EntityMoveable');

	class EntityMoveableNPC extends EntityMoveable {
		protected function SetType() { $this->Type = EntityType::NPC; }
	}
?>