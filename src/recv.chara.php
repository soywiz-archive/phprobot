<?php
	if (!defined('GB_SYSTEM_LODADED')) die('Se requiere el sistema de GenericBot');

	// 006b - Recieved characters from Game Login Server
	function parse_recv_006b(GenericBot &$o, $p, $d) {
		global $player_data, $config, $player;

		$d = parse_str_packet($d, 'a[charas]s[20]-x[rest][a[id;base_exp;zeny;job_exp;job_level;option;karma;manner;status_points;hp;hp_max;sp;sp_max;walk_speed;class;hair_type;weapon;base_level;skill_points;head_bottom;shield;head_top;head_mid;hair_color;clothes_color;name;str;agi;vit;int;dex;luk;char_num]llllll-l-lllwwwwwwwwwwwwwwwwwz[24]bbbbbbbb-]');

		$o->characterList = array();

		foreach ($d['charas'] as $c) {
			$id = &$c['id']; $z = &new MainPlayer($o, $d['charas'][0]['id'], false);
			$o->characterList[] = $z;
			foreach ($c as $k => $v) $z->$k = $v;
		}

		$o->setErrorSuccess();

		$o->step = GB_STEP_CHARA_LOGIN_SUCCESS;
	}

	// 006c - Error logging into Game Login Server (invalid character specified)...
	function parse_recv_006c(GenericBot &$o, $p, $d) {
		$o->setError(1, 'Error logging into Game Login Server (invalid character specified)');

		$o->step = GB_STEP_CHARA_LOGIN_ERROR;
	}

	// 006e -
	function parse_recv_006e(GenericBot &$o, $p, $d) {
		echo "Unparsed packet: 0x006e\n";
	}

	// 0071 - Recieved character ID and Map IP from Game Login Server
	function parse_recv_0071(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;map;ip;port]lz[16]rlnf[ip]w');

		$o->map = new Map($d['map']);

		if ($o->overwriteIp) list($d['ip']) = getIpAndPort($o->masterServer, 6900);
		$o->mapServer = $d['ip'] . ':' . $d['port'];
		$o->sock->connect($d['ip'], $d['port']);

		sendMapLogin($o);

		$o->connectionData['id_map'] = getr32($o->sock->extract(4));

		$o->step = GB_STEP_MAP_PROCESS;
	}
?>