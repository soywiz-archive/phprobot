<?php
	Import('Entity.EntityMoveable');

	class EntityMoveableEnemy extends EntityMoveable {
		protected function SetType() { $this->Type = EntityType::ENEMY; }
	}
?>