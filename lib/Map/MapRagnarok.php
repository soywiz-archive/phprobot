<?php
	Import('System.Buffer');

	class MapRagnarok extends Map {
		// Constructor
		public function __construct($name) {
			list($name) = explode('.', $name, 2);

			// alberta.map.bz2

			$map_name = LUNEA_MAPS . $name . '.map';

			if (file_exists($map_name_r = $map_name)) {
				$data = file_get_contents($map_name_r);
			} else if (file_exists($map_name_r = $map_name . '.bz2')) {
				$data = bzdecompress(file_get_contents($map_name_r));
			} else if (file_exists($map_name . '.gz')) {
				$data = gzuncompress(file_get_contents($map_name_r));
			} else {
				// Aquí se comprobaría en el Gat
				/*
				if (isset($GLOBALS['Gat']) && sizeof($GLOBALS['Gat']) >= 1) {
					global $Gat;
					$gat_data = $Gat->Read('data\\' . $name . '.gat');

					unset($gat_data);
				}
				*/

				throw(new Exception('Map \'' . $name . '\' doesn\'t exists'));
			}

			$width  = ExtR16($data);
			$height = ExtR16($data);

			parent::Update($data, $width, $height);
		}

		// Obtiene la posición mas alejada entre dos puntos y menor que $range que se pueda pasar "volando" (flechas/hechizos)
		public function FindAttackPosition($x_src, $y_src, $x_dst, $y_dst, $range, $type = FIND_FLY) {
			$path = array_slice(array_reverse($this->Find($x_src, $y_src, $x_dst, $y_dst, $type)), 0, $range + 1);

			while (sizeof($path)) {
				list($x, $y) = array_pop($path);

				$p = $this->Get($x, $y);

				if ($p !== 1 && $p !== 5) return array($x, $y);
			}

			if ($type == FIND_FLY) {
				return $this->FindAttackPosition($x_src, $y_src, $x_dst, $y_dst, $range, FIND_WALK);
			}

			return false;
		}

		// Muy lenta: Optimizar
		public function DistanceToAttack($x_src, $y_src, $x_dst, $y_dst, $range) {
			$pos = $this->FindAttackPosition($x_src, $y_src, $x_dst, $y_dst, $range);
			if ($pos === false) return log(0);
			list($x, $y) = $pos;
			return sizeof($this->Find($x_src, $y_src, $x, $y, FIND_WALK));
		}
	}
?>