<?php
	Import('Entity.Entity');
	Import('Ragnarok.Status');

	class EntityMoveable extends Entity {
		public $Moving;
		public $Direction;
		public $Status;
		public $IdAccount;

		public $Hp;
		public $HpMax;
		public $Sp;
		public $SpMax;

		public $Str;
		public $Agi;
		public $Vit;
		public $Int;
		public $Dex;
		public $Luk;

		public $HairType;
		public $HairColor;

		public $HeadTop;
		public $HeadMid;

		public $Weapon;
		public $Shield;

		public $Class;
	}
?>