<?php
	define('PATH_SYSTEM', dirname(__FILE__));

	class Common {
	}

	/*
	function get_angle($x1, $y1, $x2, $y2) {
		return (($x = $x2 - $x1) != 0) ? ((atan(($y2 - $y1) / $x) * 180) / M_PI) : (($y2 - $y1 > 0) ? -90 : 90);
	}
	*/

	function get_angle($x1, $y1, $x2, $y2) {
		list($dx, $dy) = array($x2 - $x1, $y2 - $y1);
		if ($dx == 0) return ($dy > 0) ? -90 : 90;
		$a = -((atan($dy / $dx) * 180) / M_PI);
		if ($dx < 0) $a += (($dy < 0) ? 180 : -180);
		return $a;
	}

	function require_class($className) {
		//echo "Require... $className\n";
		require_once(PATH_SYSTEM . '/class.' . $className . '.php');
	}

	function __autoload($className) { require_class($className); }
	function make_enum() { $c = 0; foreach (func_get_args() as $v) if (!defined($v)) define($v, $c++); }


	function getIpAndPort($string, $default_port) {
		list($host, $port) = (strpos($string, ':') !== false) ? explode(':', $string, 2) : array($string, $default_port);

		return array(gethostbyname($host), (int)$port);
	}

	function getSimilarArray($array, $value) {
		$return = array();
		foreach ($array as $k => $v) {
			similar_text($v, $value, $per);
			$return[$k] = round($per, 2);
		}
		arsort($return);
		return $return;
	}

	function getTickCount() {
		$time = (string)(time() * 1000);
		if (strlen($time) > 9) {
			return substr($time, -8, 8);
		} else {
			return $time;
		}
	}

	// STRING (Must UptoDate)

	// String functions
    function make8($num)  { return chr($num); }
    function make16($num) { return chr($num >> 8) . chr($num & 0xFF); }
    function make32($num) { return make16($num >> 16) . make16($num & 0xFFFF); }

    function maker8($num)  { return chr($num); }
    function maker16($num) { return chr($num & 0xFF) . chr($num >> 8); }
    function maker32($num) { return maker16($num & 0xFFFF) . maker16($num >> 16); }

    function makestr8($str) { $str = substr($str, 0, 255); return make8(strlen($str)) . $str; }
    function makestr16($str) { $str = substr($str, 0, pow(2, 16) - 1); return make16(strlen($str)) . $str; }
    function makestr32($str) { $str = substr($str, 0, pow(2, 32) - 1); return make32(strlen($str)) . $str; }

    function makeZS($str, $len) { return str_pad($str, $len, chr(0)); }

    function get8($str)   { return ord($str[0]); }
    function get16($str)  { return ord($str[1]) + (ord($str[0]) * 256); }
    function get32($str)  { return ord($str[3]) + (ord($str[2]) * 256) + (ord($str[1]) * 65536) + (ord($str[0]) * 16777216); }

    function getr8($str)   { return ord($str[0]); }
    function getr16($str)  { return ord($str[0]) + (ord($str[1]) * 256); }
    function getr32($str)  { return ord($str[0]) + (ord($str[1]) * 256) + (ord($str[2]) * 65536) + (ord($str[3]) * 16777216); }

	function getStrZero($str) { if (($pos = strpos($str, chr(0))) !== false) { return substr($str, 0, $pos); } else { return $str; } }

	// Shift from strings
	function str_shift(&$str, $n) { $return = substr($str, 0, $n); $str = substr($str, $n); return $return; }

	function ex8(&$str)   { return get8(str_shift($str, 1));   }
	function ex16(&$str)  { return get16(str_shift($str, 2));  }
	function ex32(&$str)  { return get32(str_shift($str, 4));  }

	function exr8(&$str)  { return getr8(str_shift($str, 1));  }
	function exr16(&$str) { return getr16(str_shift($str, 2)); }
	function exr32(&$str) { return getr32(str_shift($str, 4)); }

	function exStr8(&$str) { return str_shift($str, ex8($str)); }
	function exStr16(&$str) { return str_shift($str, ex16($str)); }
	function exStr32(&$str) { return str_shift($str, ex32($str)); }

	function exZS(&$str, $len) { return getStrZero(str_shift($str, $len)); }

	// Add to strings
	function str_add(&$str, $add) { $str .= $add; }

	function ad8(&$str, $v) { str_add($str, make8($v)); }
	function ad16(&$str, $v) { str_add($str, make16($v)); }
	function ad32(&$str, $v) { str_add($str, make32($v)); }

	function adr8(&$str, $v) { str_add($str, maker8($v)); }
	function adr16(&$str, $v) { str_add($str, maker16($v)); }
	function adr32(&$str, $v) { str_add($str, maker32($v)); }

	function adStr8(&$str, $add) { str_add($str, makestr8($add)); }
	function adStr16(&$str, $add) { str_add($str, makestr16($add)); }
	function adStr32(&$str, $add) { str_add($str, makestr32($add)); }

	function adZS(&$str, $add, $len) { str_add($str, str_pad($add, $len, chr(0))); }

	// Other

	/*

	function getXY($str) {
		$x = ((ord($str[0]) << 2) | ((ord($str[1]) & 0xC0) >> 6));
		$y = (((ord($str[1]) & 0x3f) << 4) | ((ord($str[2]) & 0xf0) >> 4));

		return array($x - 1, $y - 1);
	}

	function getXY2($str) {
		$x1 = (((ord($str[0]) & 0xff) << 2) | ((ord($str[1]) & 0xC0) >> 6));
		$y1 = (((ord($str[1]) & 0x3f) << 4) | ((ord($str[2]) & 0xf0) >> 4));

		$x2 = (((ord($str[3]) & 0xfc) >> 2) | ((ord($str[2]) & 0x0f) << 6));
		$y2 = (((ord($str[3]) & 0x03) << 8) | (ord($str[4]) & 0xff));

		return array($x1 - 1, $y1 - 1, $x2 - 1, $y2 - 1);
	}

	function makeXY($x, $y) {
		//$x++; $y++;
		$ret = '';
		adr8($ret, (int)($x >> 2));
		adr8($ret, (int)((($x % 4) << 6) | (int)($y >> 4)));
		adr8($ret, (int)(($y % 16) << 4));

		return $ret;
	}
	*/

	function getXY($str) {
		$x = ((ord($str[0]) << 2) | ((ord($str[1]) & 0xC0) >> 6));
		$y = (((ord($str[1]) & 0x3f) << 4) | ((ord($str[2]) & 0xf0) >> 4));

		return array($x, $y);
	}

	function getXY2($str) {
		$x1 = (((ord($str[0]) & 0xff) << 2) | ((ord($str[1]) & 0xC0) >> 6));
		$y1 = (((ord($str[1]) & 0x3f) << 4) | ((ord($str[2]) & 0xf0) >> 4));

		$x2 = (((ord($str[3]) & 0xfc) >> 2) | ((ord($str[2]) & 0x0f) << 6));
		$y2 = (((ord($str[3]) & 0x03) << 8) | (ord($str[4]) & 0xff));

		return array($x1, $y1, $x2, $y2);
	}

	function makeXY($x, $y) {
		$ret = '';
		adr8($ret, (int)($x >> 2));
		adr8($ret, (int)((($x % 4) << 6) | (int)($y >> 4)));
		adr8($ret, (int)(($y % 16) << 4));

		return $ret;
	}

	// Explicado en "doc/parse_str_packet.txt"
	function parse_str_packet(&$d, $fmt) {
		$return = array();

		$fl = strlen($fmt = strtolower(trim($fmt)));
		$reverse = false;
		for ($n = 0; $n < $fl; $n++) {
			$cl = strlen($d);
			switch($c = $fmt[$n]) {
				case 'a':
					if ($fmt[++$n] == '[') {
						$n++;
						if (($p = strpos($fmt, ']', $n)) === false) {
							trigger_error('parse_str_packet : "[" must close at pos :' . $n . ' >>>' . substr($fmt, 0, $n) . '<<<', E_USER_WARNING);
							return false;
						}

						$ls = strtolower(trim(substr($fmt, $n, $p - $n))); $n = $p;

						$pname = explode(';', $ls);
					}
				break;
				case 'r': $reverse = true;  break;
				case 'n': $reverse = false;	break;
				case 'q': // position XY-XY (5 bytes) Array (x,y,x_to,y_to)
					if ($cl >= 5) {
						$return[] = getXY2(str_shift($d, 5));
					} else {
						trigger_error('parse_str_packet : ("q") buffer overflow', E_USER_WARNING);
					}
				break;
				case 'p': // position XY (3 bytes) Array (x,y)
					if ($cl >= 3) {
						$return[] = getXY(str_shift($d, 3));
					} else {
						trigger_error('parse_str_packet : ("p") buffer overflow', E_USER_WARNING);
					}
				break;
				case 'l': // long
					if ($cl >= 4) {
						$return[] = $reverse ? ex32($d) : exr32($d);
					} else {
						trigger_error('parse_str_packet : ("l") string buffer extract overflow', E_USER_WARNING);
					}
				break;
				case 'w': // word
					if ($cl >= 2) {
						$return[] = $reverse ? ex16($d) : exr16($d);
					} else {
						trigger_error('parse_str_packet : ("w") string buffer extract overflow', E_USER_WARNING);
					}
				break;
				case 'b': // byte
					if ($cl >= 1) {
						$return[] = $reverse ? ex8($d) : exr8($d);
					} else {
						trigger_error('parse_str_packet : ("b") string buffer extract overflow', E_USER_WARNING);
					}
				break;
				case 'x': // repeat X
					if ($fmt[++$n] == '[') {
						$n++;
						if (($p = strpos($fmt, ']', $n)) === false) {
							trigger_error('parse_str_packet : "[" must close at pos :' . $n . ' >>>' . substr($fmt, 0, $n) . '<<<', E_USER_WARNING);
							return false;
						}

						$ls = strtolower(trim(substr($fmt, $n, $p - $n))); $n = $p + 1;

						if ($fmt[$n++] != '[') {
							trigger_error('parse_str_packet : ("x") malformed', E_USER_WARNING);
							return false;
						}

						$p = $n;
						for ($ct = 1; (($ct > 0) && ($n < $fl)); $n++) {
							switch($fmt[$n]) {
								case '[': $ct++; break;
								case ']': $ct--; break;
							}
						}

						$fmtrep = substr($fmt, $p, $n - $p - 1);

						$retpos = sizeof($return);
						$return[$retpos] = array();

						if (strlen($ls)) {
							switch($ls) {
								case 'rest':
									$cl = strlen($d);
									while($cl) {
										$return[$retpos][] = parse_str_packet($d, $fmtrep);

										// No se ha extraido nada mas, se sale para evitar un
										// bucle infinito
										if ($cl == ($cl2 = strlen($d))) break;
										$cl = $cl2;
									}
								break;
								default:
									if (strlen($ls) >= 2 && $ls[0] == 'p' && $ls[1] == ':') {
										$pp = sizeof($return) - (int)substr($ls, 2);
										if (isset($return[$pp])) {
											$ls = $return[$pp];
										} else {
											trigger_error('parse_str_packet : can\'t get desired param: ' . $ls . ' - ' . $pp, E_USER_WARNING);
											return false;
										}
									}

									$ls = (int)$ls;
									for ($m = 0; $m < $ls; $m++) {
										$return[$retpos][] = parse_str_packet($d, $fmtrep);
									}
								break;
							}
						}
					}
				break;
				case '-':
					if (sizeof($return)) {
						array_pop($return);
					} else {
						trigger_error('parse_str_packet : haven\'t params to extract', E_USER_WARNING);
					}
				break;
				case 'z': case 's': // string/stringZ [x] [rest]
				case 'f':
					if ($fmt[++$n] == '[') {
						$n++;
						if (($p = strpos($fmt, ']', $n)) === false) {
							trigger_error('parse_str_packet : "[" must close at pos :' . $n . ' >>>' . substr($fmt, 0, $n) . '<<<', E_USER_WARNING);
							return false;
						}

						$ls = strtolower(trim(substr($fmt, $n, $p - $n))); $n = $p;

						if ($ls == 'rest') $ls = $cl;

						switch($c) {
							case 'z':
								if ($cl >= $ls) {
									$return[] = exZS($d, (int)$ls);
								} else {
									trigger_error('parse_str_packet : ("z") string buffer extract overflow', E_USER_WARNING);
								}
							break;
							case 's':
								if ($cl >= $ls) {
									$return[] = str_shift($d, (int)$ls);
								} else {
									trigger_error('parse_str_packet : ("s") string buffer extract overflow', E_USER_WARNING);
								}
							break;
							case 'f':
								if (sizeof($return)) {
									switch($ls) {
										case 'ip': $return[] = long2ip(array_pop($return)); break;
										default:
											// Si no está definido comprueba si existe una función con ese nombre y
											// trata de llamarla con el parámetro anterior como parámetro.
											if (function_exists($ls)) {
												$return[] = $ls(array_pop($return));
											}
										break;
									}
								} else {
									trigger_error('parse_str_packet : haven\'t params to extract', E_USER_WARNING);
								}
							break;
							default:
								trigger_error('parse_str_packet : unknown error : unexpected "' . $c . '" strarg', E_USER_WARNING);
							break;
						}
					} else {
						trigger_error('parse_str_packet : s[int]', E_USER_WARNING);
						return false;
					}
				break;
				default:
					trigger_error("parse_str_packet : unknown Parse Type: '$c'\n", E_USER_WARNING);
					return false;
				break;
			}
		}

		if (isset($pname)) {
			$return2 = array();
			foreach($pname as $k => $n) $return2[$n] = $return[$k];
			$return = $return2;
		}

		return $return;
	}

?>