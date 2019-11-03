<?php
	class Item {
		public $Index;
		public $Id;
		public $Type;
		public $Identify;
		public $Point;
		public $Equip;
		public $Attribute;
		public $Refined;
		public $Amount;
		public $Card1;
		public $Card2;
		public $Card3;
		public $Card4;

		function __construct($Index, $Id) {
			list(
				$this->Index,
				$this->Id
			) = array(
				$Index,
				$Id
			);
		}
	}

	class ItemList {
		public  $Type;
		public  $Weight;
		public  $WeightMax;
		public  $AmountMax;
		private $ListId    = array();
		private $ListIndex = array();

		// Skill
		function Register(Item &$Item) {
			$this->ListId[$Item->Id] = &$Item;
			$this->ListIndex[$Item->Index] = &$Item;
		}

		function Update(Item &$Item) {
		}

		function UnRegister(Item &$Item) {
			if (isset($this->ListId[$Item->Id]))       unset($this->ListId[$Item->Id]);
			if (isset($this->ListIndex[$Item->Index])) unset($this->ListIndex[$Item->Index]);
		}
	}
?>