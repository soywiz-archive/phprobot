<?php
	Import('Entity.EntityMoveable');

	class EntityMoveablePlayer extends EntityMoveable {
		protected function SetType() { $this->Type = EntityType::PLAYER; }
	}
?>