<?php
	Import('Entity.EntityMoveable');

	class EntityMoveablePet extends EntityMoveable {
		protected function SetType() { $this->Type = EntityType::PET; }
	}
?>