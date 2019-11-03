<?php
	require_once(dirname(__FILE__) . '/system.php');

	define('PATH_MAP', dirname(dirname(__FILE__)) . '/maps/');

	class Map {
		public         $w;
		public         $h;
		public         $name;
		private        $data;
		static private $path_ffi;
		static private $conversion_list;

		function open($name) {
			$this->name = $nname = substr($name, 0, (($z = strpos($name, '.')) === false) ? strlen($name) : $z);

			if (isset(Map::$conversion_list[$nname])) $nname = Map::$conversion_list[$nname];

			foreach (array('map', 'map.bz2', 'map.gz') as $ext) {
				$cname = $nname . '.' . $ext;
				foreach (array(PATH_MAP . $cname, $cname) as $rname) {
					if (file_exists($rname)) {
						$data = file_get_contents($rname);
						// BZ2
						if ($ext == 'map.bz2') {
							extension_loaded('bz2') or dl('php_bz2.dll') or die('Se requiere la extensión bz2');
							$data = bzdecompress($data);
						// GZ
						} else if ($ext == 'map.gz') {
							$data = gzuncompress($data);
						}

						$p = unpack('vw/vh', substr($data, 0, 4));
						list($this->w, $this->h, $this->data) = array($p['w'], $p['h'], substr($data, 4));
						if (strlen($data) < $this->w * $this->h - 4) throw(new Exception());
						return;
					}
				}
			}

			throw(new Exception('No se pudo encontrar el mapa ' . $nname));
		}

		function isFree($x, $y) {
			if (isset($this->data[$p = $y * $this->w + $x])) return (ord($this->data[$p]) != 1);
			return false;
		}

		function getPath($x1, $y1, $x2, $y2) {
			// Comprueba bordes
			if ($x1 < 0 || $y1 < 0 || $x2 >= $this->w || $y2 >= $this->h) return array();

			$f = &Map::$path_ffi;
			$e = array(); $list = explode("\n", $f->path_get($this->data, $this->w, $this->h, $x1, $y1, $x2, $y2, 1000));
			foreach ($list as $v) if (strpos($v, ',') !== false) $e[] = explode(',', $v);
			$f->free($v); return $e;
		}

		function __construct($name = NULL) {
			// La primera vez que se instancia un mapa carga la extensión php_ffi y el módulo "path.dll"
			if (!isset(Map::$path_ffi)) {
				if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
					// Para windows
					extension_loaded('ffi') or dl('php_ffi.dll') or die("Please install FFI extension.\n");

					Map::$path_ffi = new ffi("[lib='msvcrt.dll'] void free(char *ptr); [lib='path.dll'] char *path_get(char *map_data, int map_w, int map_h, int x_src, int y_src, int x_dst, int y_dst, int time);");
				} else {
					throw(new Exception("Esta versión de PHP Ro Bot solo está preparada para el SO Windows. Si puedes compilar util/ext/path/ agradeceríamos su colaboración. Gracias."));
					exit;
				}
			}

			if (!isset(Map::$conversion_list)) {
				Map::$conversion_list = array();

				$lines = explode('#', file_get_contents(PATH_SYSTEM . '/resnametable.txt'));

				while (sizeof($lines)) {
					$line = array_shift($lines);
					if (sizeof($lines) == 0) break;
					$line = trim($line);
					$line_res = trim(array_shift($lines));

					if (strlen($line) == 0 || strlen($line_res) == 0) continue;

					$ext1 = (($p = strrpos($line, '.')) !== false) ? strtolower(trim(substr($line, $p + 1))) : '';
					$ext2 = (($p = strrpos($line, '.')) !== false) ? strtolower(trim(substr($line, $p + 1))) : '';

					list($line2)     = explode('.', $line, 2);     $line2 = strtolower(trim($line2));
					list($line_res2) = explode('.', $line_res, 2); $line_res2 = strtolower(trim($line_res2));

					if ($ext1 == 'rsw' || $ext1 == 'gat' || $ext1 == 'gnd') {
						Map::$conversion_list[$line2] = $line_res2;
					}
				}
			}

			if (isset($name)) $this->open($name);
		}
	}

	/*
	$map = new Map('pvp_n_1-2');
	print_r($map->getPath(50, 50, 52, 52));
	exit;
	*/
?>