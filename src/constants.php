<?php
	class DealConst {
		const ERROR_FAR     = 0x00;
		const SUCCESS       = 0x03;
		const ERROR_CANCEL  = 0x04;

		const OK_SELF       = 0x00;
		const OK_OTHER      = 0x01;
	}

	class SkillConst {
		const TARGET_PASSIVE = 0x00;
		const TARGET_ENEMY   = 0x01;
		const TARGET_MAP     = 0x02;
		const TARGET_NONE    = 0x04;
		//const TARGET_UNKNOWN = 0x04;
		const TARGET_ALLY    = 0x16;
		const TARGET_TRAP    = 0x32;
	}

?>