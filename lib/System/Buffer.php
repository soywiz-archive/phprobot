<?php
	class Buffer {
		private $Buffer;

		//function __call() { }
	}

	function GetInteger($str, $octal = false) {
		if (substr($str, 0, 2) == '0x') {
			// Hexadecimal
			$str = hexdec(substr($str, 2));
		} else if ($octal && substr($str, 0, 1) == '0') {
		//  // Octal
			$str = octdec(substr($str, 1));
		} else {
			// Decimal
			//$str = (int)$str;
		}

		return (int)$str;
	}

	// String functions
	function Make8($num)               { return chr($num); }
	function Make16($num)              { return chr($num >> 8) . chr($num & 0xFF); }
	function Make32($num)              { return Make16($num >> 16) . Make16($num & 0xFFFF); }

	function MakeR8($num)              { return chr($num); }
	function MakeR16($num)             { return chr($num & 0xFF) . chr($num >> 8); }
	function MakeR32($num)             { return MakeR16($num & 0xFFFF) . MakeR16($num >> 16); }

	function MakeStr8($str)            { $str = substr($str, 0, 255); return Make8(strlen($str)) . $str; }
	function MakeStr16($str)           { $str = substr($str, 0, pow(2, 16) - 1); return Make16(strlen($str)) . $str; }
	function MakeStr32($str)           { $str = substr($str, 0, pow(2, 32) - 1); return Make32(strlen($str)) . $str; }

	function MakeZS($str, $len)        { return str_pad($str, $len, chr(0)); }

	function Get8($str)                { return ord($str[0]); }
	function Get16($str)               { return ord($str[1]) + (ord($str[0]) * 256); }
	function Get32($str)               { return ord($str[3]) + (ord($str[2]) * 256) + (ord($str[1]) * 65536) + (ord($str[0]) * 16777216); }

	function GetR8($str)               { return ord($str[0]); }
	function GetR16($str)              { return ord($str[0]) + (ord($str[1]) * 256); }
	function GetR32($str)              { return ord($str[0]) + (ord($str[1]) * 256) + (ord($str[2]) * 65536) + (ord($str[3]) * 16777216); }

	function GetStrZero($str)          { if (($pos = strpos($str, chr(0))) !== false) { return substr($str, 0, $pos); } else { return $str; } }

	// Shift from strings
	function StrShift(&$str, $n)       { $return = substr($str, 0, $n); $str = substr($str, $n); return $return; }

	function Ext8(&$str)               { return Get8(StrShift($str, 1));   }
	function Ext16(&$str)              { return Get16(StrShift($str, 2));  }
	function Ext32(&$str)              { return Get32(StrShift($str, 4));  }

	function ExtR8(&$str)              { return GetR8(StrShift($str, 1));  }
	function ExtR16(&$str)             { return GetR16(StrShift($str, 2)); }
	function ExtR32(&$str)             { return GetR32(StrShift($str, 4)); }

	function ExtStr8(&$str)            { return StrShift($str, ex8($str)); }
	function ExtStr16(&$str)           { return StrShift($str, ex16($str)); }
	function ExtStr32(&$str)           { return StrShift($str, ex32($str)); }

	function ExtZS(&$str, $len)        { return GetStrZero(StrShift($str, $len)); }

	// Add to strings
	function StrAdd(&$str, $add)       { $str .= $add; }

	function Add8(&$str, $v)           { StrAdd($str, Make8($v)); }
	function Add16(&$str, $v)          { StrAdd($str, Make16($v)); }
	function Add32(&$str, $v)          { StrAdd($str, Make32($v)); }

	function AddR8(&$str, $v)          { StrAdd($str, MakeR8($v)); }
	function AddR16(&$str, $v)         { StrAdd($str, MakeR16($v)); }
	function AddR32(&$str, $v)         { StrAdd($str, MakeR32($v)); }

	function AddStr8(&$str, $add)      { StrAdd($str, MakeStr8($add)); }
	function AddStr16(&$str, $add)     { StrAdd($str, MakeStr16($add)); }
	function AddStr32(&$str, $add)     { StrAdd($str, MakeStr32($add)); }

	function AddZS(&$str, $add, $len)  { StrAdd($str, str_pad($add, $len, chr(0))); }

	function ParseStrPacket(&$d, $fmt) {
		$return = array();

		$fl = strlen($fmt = strtolower(trim($fmt)));
		$reverse = false;

		for ($n = 0; $n < $fl; $n++) {
			$cl = strlen($d);
			switch ($c = $fmt[$n]) {
				case 'a':
					if ($fmt[++$n] == '[') {
						$n++;
						if (($p = strpos($fmt, ']', $n)) === false) {
							trigger_error('ParseStrPacket : "[" must close at pos :' . $n . ' >>>' . substr($fmt, 0, $n) . '<<<', E_USER_WARNING);
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
						$return[] = getXY2(StrShift($d, 5));
					} else {
						trigger_error('ParseStrPacket : ("q") buffer overflow', E_USER_WARNING);
					}
				break;
				case 'p': // position XY (3 bytes) Array (x,y)
					if ($cl >= 3) {
						$return[] = getXY(StrShift($d, 3));
					} else {
						trigger_error('ParseStrPacket : ("p") buffer overflow', E_USER_WARNING);
					}
				break;
				case 'l': // long
					if ($cl >= 4) {
						$return[] = $reverse ? ex32($d) : exr32($d);
					} else {
						trigger_error('ParseStrPacket : ("l") string buffer extract overflow', E_USER_WARNING);
					}
				break;
				case 'w': // word
					if ($cl >= 2) {
						$return[] = $reverse ? ex16($d) : exr16($d);
					} else {
						trigger_error('ParseStrPacket : ("w") string buffer extract overflow', E_USER_WARNING);
					}
				break;
				case 'b': // byte
					if ($cl >= 1) {
						$return[] = $reverse ? ex8($d) : exr8($d);
					} else {
						trigger_error('ParseStrPacket : ("b") string buffer extract overflow', E_USER_WARNING);
					}
				break;
				case 'x': // repeat X
					if ($fmt[++$n] == '[') {
						$n++;
						if (($p = strpos($fmt, ']', $n)) === false) {
							trigger_error('ParseStrPacket : "[" must close at pos :' . $n . ' >>>' . substr($fmt, 0, $n) . '<<<', E_USER_WARNING);
							return false;
						}

						$ls = strtolower(trim(substr($fmt, $n, $p - $n))); $n = $p + 1;

						if ($fmt[$n++] != '[') {
							trigger_error('ParseStrPacket : ("x") malformed', E_USER_WARNING);
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
										$return[$retpos][] = ParseStrPacket($d, $fmtrep);

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
											trigger_error('ParseStrPacket : can\'t get desired param: ' . $ls . ' - ' . $pp, E_USER_WARNING);
											return false;
										}
									}

									$ls = (int)$ls;
									for ($m = 0; $m < $ls; $m++) {
										$return[$retpos][] = ParseStrPacket($d, $fmtrep);
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
						trigger_error('ParseStrPacket : haven\'t params to extract', E_USER_WARNING);
					}
				break;
				case 'z': case 's': // string/stringZ [x] [rest]
				case 'f':
					if ($fmt[++$n] == '[') {
						$n++;
						if (($p = strpos($fmt, ']', $n)) === false) {
							trigger_error('ParseStrPacket : "[" must close at pos :' . $n . ' >>>' . substr($fmt, 0, $n) . '<<<', E_USER_WARNING);
							return false;
						}

						$ls = strtolower(trim(substr($fmt, $n, $p - $n))); $n = $p;

						if ($ls == 'rest') $ls = $cl;

						switch($c) {
							case 'z':
								if ($cl >= $ls) {
									$return[] = exZS($d, (int)$ls);
								} else {
									trigger_error('ParseStrPacket : ("z") string buffer extract overflow', E_USER_WARNING);
								}
							break;
							case 's':
								if ($cl >= $ls) {
									$return[] = StrShift($d, (int)$ls);
								} else {
									trigger_error('ParseStrPacket : ("s") string buffer extract overflow', E_USER_WARNING);
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
									trigger_error('ParseStrPacket : haven\'t params to extract', E_USER_WARNING);
								}
							break;
							default:
								trigger_error('ParseStrPacket : unknown error : unexpected "' . $c . '" strarg', E_USER_WARNING);
							break;
						}
					} else {
						trigger_error('ParseStrPacket : s[int]', E_USER_WARNING);
						return false;
					}
				break;
				default:
					trigger_error("ParseStrPacket : unknown Parse Type: '$c'\n", E_USER_WARNING);
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