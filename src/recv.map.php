<?php
	if (!defined('GB_SYSTEM_LODADED')) die('Se requiere el sistema de GenericBot');

	// 0081 - Disconnected from Server
	function parse_recv_0081(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[error]b');
		$o->disconnect();
	}

	// 0073 - Enter Map
	function parse_recv_0073(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[tick;pos]lpw-');

		Entity::deleteAll($o);

		$z = $o->characterSelected;

		$o->player = new MainPlayer($o, $o->connectionData['id_map']);
		$o->characterSelected = &$o->player;

		foreach (array('hp', 'hp_max', 'sp', 'sp_max', 'flee', 'head', 'body', 'x', 'y', 'view_class',
		'group', 'speed', 'base_exp', 'zeny', 'job_exp', 'job_level', 'option', 'karma',
		'manner', 'status_points', 'walk_speed', 'class', 'hair_type', 'weapon', 'base_level',
		'skill_points', 'head_bottom', 'shield', 'head_top', 'head_mid', 'hair_color',
		'clothes_color', /*'name',*/ 'str', 'agi', 'vit', 'int', 'dex', 'luk') as $p) {
			$o->player->$p = $z->$p;
		}
		//$o->player->trace();

		list($o->player->x, $o->player->y) = $d['pos'];

		// Mapa Cargado correctamente
		sendMapLoaded($o);
		sendGetEntityName($o, $o->player->id);

		$o->onMapStart();
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	// 00b1 - Your Status Info (Exp, Job Exp, Zeny)
	function parse_recv_00b1(GenericBot &$o, $p, $d) {
		parse_recv_00b0($o, $p, $d);
	}

	// 00b0 - Your Status Info
	function parse_recv_00b0(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[type;value]wl');
		$type = &$d['type']; $value = &$d['value'];

		$c = $o->player;

		switch ($type) {
			case 0x0000: $c->walk_speed    = $value; break;

			case 0x0001: $c->base_exp      = $value; break;
			case 0x0002: $c->job_exp       = $value; break;

			case 0x0005: $c->hp            = $value; break;
			case 0x0006: $c->hp_max        = $value; break;
			case 0x0007: $c->sp            = $value; break;
			case 0x0008: $c->sp_max        = $value; break;
			case 0x0009: $c->status_points = $value; break;
			case 0x000b: $c->base_level    = $value; break;
			case 0x000c: $c->skill_points  = $value; break;

			case 0x0014: $c->zeny          = $value; break;
			case 0x0016: $c->base_exp_next = $value; break;
			case 0x0017: $c->job_exp_next  = $value; break;

			case 0x0018: $c->weight        = $value; break;
			case 0x0019: $c->weight_max    = $value; break;

			case 0x0029: $c->atk           = $value; break;
			case 0x002A: $c->atk_per       = $value; break;
			case 0x002B: $c->matk          = $value; break;
			case 0x002C: $c->matk_max      = $value; break;

			case 0x002D: $c->def           = $value; break;
			case 0x002E: $c->def_per       = $value; break;

			case 0x002F: $c->mdef          = $value; break;
			case 0x0030: $c->mdef_per      = $value; break;

			case 0x0031: $c->hit           = $value; break;
			case 0x0032: $c->flee          = $value; break;
			case 0x0033: $c->flee_per      = $value; break;

			case 0x0034: $c->crit          = $value; break;
			case 0x0035: $c->atack_speed   = $value; break;

			case 0x0037: $c->job_level     = $value; break;

			default: echo "Unknown 0x00B0/0x00B1 type: {$type} : {$value}\n"; break;
		}

		$o->onCharaInfoUpdate($type, $value);
	}

	// 00bd - Your Status Info (Calculated)
	function parse_recv_00bd(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[status_points;str;str_b;agi;agi_b;vit;vit_b;int;int_b;dex;dex_b;luk;luk_b;atk;atk_per;matk;matk_max;def;def_per;mdef;mdef_per;hit;flee;flee_per;crit;karma;manner]wbbbbbbbbbbbbwwwwwwwwwwwwww');
		foreach ($d as $k => $v) $o->player->$k = $v;
		//$o->player->trace(); exit;

		$o->onCharaInfoUpdate(false, false);
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	// 0095 - Character Name
	function parse_recv_0095(GenericBot &$o, $p, $d) {
		$d        = parse_str_packet($d, 'a[id;name]lz[24]');
		$id       = &$d['id']; $name = &$d['name'];
		$names    = &$o->lists['names'];
		$names_id = &$o->lists['names_id'];
		$e        = &$o->lists['Entity'][$id];
		$monster_names = &$o->lists['monster_names'];

		$names[$id] = $name;
		$names_id[trim(strtolower($name))] = $id;

		if (isset($e->group) && ($e->group == 'monsters')) {
			$monster_names[$e->view_id] = $name;
		}

		$e->name = $name;

		//echo "[{$id}] = '{$name}';\n";
		if (isset($e->_name)) $o->onAppear($e);
	}

	// 0194 - Character Name
	function parse_recv_0194(GenericBot &$o, $p, $d) {
		parse_recv_0095($o, $p, $d);
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	// 0119 - Unit Status (Freeze, Poison, ...)
	function parse_recv_0119(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;opt1;opt2;option]lwww');
		$entity = &Entity::getEntityByIdCreate($o, $d['id']);

		$entity->opt1   = $d['opt1'];
		$entity->opt2   = $d['opt2'];
		$entity->option = $d['option'];

		$o->onCharaInfoUpdate();
	}

	// 013a - Attack Range
	function parse_recv_013a(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[range]w');
		$o->player->atack_range = $d['range'];
	}

	// 013d - Your HP/SP Changed
	function parse_recv_013d(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[type;value]ww');
		$type = &$d['type']; $value = &$d['value'];
		$c = &$o->player;

		switch($type) {
			case 5: $c->hp = $value; break;
			case 7: $c->sp = $value; break;
			default: echo "Unknown 0x013D type: {$type} : {$value}\n"; break;
		}

		$o->onCharaInfoUpdate($type, $value);
	}

	// 0141 - Your Status Changed (Str, Agi, Vit, Int, Dex, Luk, Bonus)
	function parse_recv_0141(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[type;value1;value2]lll');
		$type = &$d['type']; $v1 = &$d['value1']; $v2 = &$d['value2'];
		$c = &$o->player;

		switch ($type) {
			case 0x0D; $c->str = $v1; $c->str_b = $v2; break;
			case 0x0E; $c->agi = $v1; $c->agi_b = $v2; break;
			case 0x0F; $c->vit = $v1; $c->vit_b = $v2; break;
			case 0x10; $c->int = $v1; $c->int_b = $v2; break;
			case 0x11; $c->dex = $v1; $c->dex_b = $v2; break;
			case 0x12; $c->luk = $v1; $c->luk_b = $v2; break;
			default: echo "Unknown 0x0141 type: {$type} : {$v1}, {v2}\n"; break;
		}

		$o->onCharaInfoUpdate();
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	// 0196 - Unit Effect (Skill, Weight, ...)
	function parse_recv_0196(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[type;id;flag]wlb');

		$entity = &Entity::getEntityByIdCreate($o, $d['id']);

		if (!isset($entity->status)) $entity->status = array();

		if ($d['flag']) {
			$entity->status[$d['type']] = time();
		} else {
			unset($entity->status[$d['type']]);
		}

		echo "Status changed\n";
	}

	// 01d0 - Vigor condensation
	function parse_recv_01d0(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;count]lw');
		$entity = &Entity::getEntityByIdCreate($o, $d['id']);

		$entity->spheres = $d['count'];
	}

	// 01e1 - Vigor condensation : 01D0
	function parse_recv_01e1(GenericBot &$o, $p, $d) {
		parse_recv_01d0($o, $p, $d);
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	// 008e - Global Message
	function parse_recv_008e(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[text]z[rest]');
		echo "::" . $d['text'] . "\n";
	}

	// 009c - Unit Look
	function parse_recv_009c(GenericBot &$o, $p, $d) {
		// No se utiliza
		$d = parse_str_packet($d, 'a[id;move1;move2]lwb');
	}

	// 00c0 - Emotion
	function parse_recv_00c0(GenericBot &$o, $p, $d) {
		// No se utiliza
		$d = parse_str_packet($d, 'a[id;emotic]lb');
	}

	// 00c3 - Change Equipment Display
	function parse_recv_00c3(GenericBot &$o, $p, $d) {
		// No se usa para nada
		$d = parse_str_packet($d, 'a[id;type;val]lbb');
	}

	// 01aa - Pet Talk / Emo
	function parse_recv_01aa(GenericBot &$o, $p, $d) {
		// No se usa para nada
	}

	// 01d7 - Weapon / Shield Display
	function parse_recv_01d7(GenericBot &$o, $p, $d) {
		// No se utiliza para nada
		$d = parse_str_packet($d, 'a[id;type;value1;value2]lbww');
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	// [  OK  ] 010f - Skills List
	function parse_recv_010f(GenericBot &$o, $p, $d) {
		// Obtiene la lista de habilidades y parámetros
		$d = parse_str_packet($d, 'a[list]x[rest][a[id;target;level_max;sp_max;range;name;canup]www-wwwz[24]b]');

		// Genera la lista de habilidades
		foreach ($d['list'] as $v) {
			// Genera una nueva habilidad a partir de un ID
			$z = new Skill($o, $v['id']);

			// Introduce los datos de la habilidad
			$z->target    = $v['target'];
			$z->level_max = $v['level_max'];
			$z->sp_max    = $v['sp_max'];
			$z->range     = $v['range'];
			$z->name      = isset($o->lists['skill_names'][$v['id']])  ? $o->lists['skill_names'][$v['id']]  : 'Unknown';
			$z->title     = isset($o->lists['skill_titles'][$v['id']]) ? $o->lists['skill_titles'][$v['id']] : 'Unknown';
			$z->delay     = isset($o->lists['skill_delays'][$v['id']]) ? $o->lists['skill_delays'][$v['id']] : 0;
			$z->canup     = $v['canup'];
		}
	}

	// [ TODO ] 0110 - Skill Use Failed
	function parse_recv_0110(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;btype;type]www-b-b');
		echo "Skill Failed\n";

		$o->setBusy(false);
	}

	// [ TODO ] 011a - Skill Restore
	function parse_recv_011a(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[skill;value;id_dst;id_src;fail]wwllb');
		echo 'Skill Restore: ' . $d['id_src'] . ' -> ' . $d['skill'] . "\n";
		if ($o->player->id == $d['id_src']) $o->setBusy(false);
	}

	// [ TODO ] 013e - Skill Cast
	function parse_recv_013e(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id_src;id_dst;x_dst;y_dst;skill_num;skill;time]llwwwll');
		echo 'Casting from ' . $d['id_src'] . ' to ' . $d['id_dst'] . ' ON (' . $d['x_dst'] . ', ' . $d['y_dst'] . ') SKILL ' . $d['skill_num'] . ', ' . $d['skill'] . ' TIME ' . $d['time'] . "\n";

		if ($o->player->id == $d['id_src']) {
			$o->setBusy(true);
			$o->busy_time->add($d['time']);
		}
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	// 00a3 - Inventory Items List
	function parse_recv_00a3(GenericBot &$o, $p, $d, $pd = false) {
		if (!$pd) {
			$d = parse_str_packet($d, 'a[items]x[rest][a[index;id;type;identify;amount;equip]wwbbww]');
		} else {
			$d = parse_str_packet($d, 'a[items]x[rest][a[index;id;type;identify;amount;equip;card1;card2;card3;card4]wwbbwwwwww]');
		}

		if (!isset($o->player->items)) $o->player->items = array(); $i = &$o->player->items;

		foreach ($d['items'] as $_i) $i[$_i['index']] = $_i;
	}

	// 00a4 - Inventory Equipments List
	function parse_recv_00a4(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[equip]x[rest][a[index;id;type;identify;point;equipped;attr;refine;card1;card2;card3;card4]wwbbwwbbwwww]');
		if (!isset($o->player->equip)) $o->player->equip = array(); $i = &$o->player->equip;

		foreach ($d['equip'] as $_i) $i[$_i['index']] = $_i;
	}

	// 01ee - Inventory Items List : 00A3
	function parse_recv_01ee(GenericBot &$o, $p, $d) {
		parse_recv_00a3($o, $p, $d, true);
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	// 016c - Guild Name
	function parse_recv_016c(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;emblem;count;name]llll-b-z[24]');

		$guild = &Guild::getGuildByIdCreate($o, $d['id']);
		$guild->name   = $d['name'];
		$guild->count  = $d['count'];
		$guild->emblem = &Emblem::getEmblemByIdCreate($o, $d['emblem']);
	}

	// 016f - Guild Notice
	function parse_recv_016f(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[text1;text2]z[60]z[60]');
		echo "Guild Topic:  " . $d['text1'] . "\n";
		echo "Guild Notice: " . $d['text2'] . "\n";
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	function parse_recv_0078_0079(GenericBot &$o, $p, $d) {
		$entity = Entity::getEntityByIdCreate($o, $d['id']);

		list($entity->x, $entity->y) = $d['pos'];

		foreach (array('speed', 'opt1', 'opt2', 'option', 'view_class', 'hair', 'weapon', 'head_bottom',
		'shield', 'head_top', 'head_mid', 'hair_color', 'clothes_color', 'head_dir',
		'manner', 'karma', 'sex', 'dead_sit', 'base_level') as $k) {
			if (isset($d[$k])) $entity->$k = $d[$k];
		}

		//$entity->emblem = &Emblem::getEmblemByIdCreate($o, $d['emblem_id']);
		//$entity->guild  = &Guild::getGuildByIdCreate($o, $d['guild_id']);
		Emblem::getEmblemByIdCreate($o, $d['emblem_id']);
		Guild::getGuildByIdCreate($o, $d['guild_id']);

		//$entity->trace();
		sendGetEntityName($o, $d['id']);

		if (isset($entity->_name)) $o->onAppear($entity);

	}

	// 0078 - Unit Exists
	function parse_recv_0078(GenericBot &$o, $p, $d) {
		parse_recv_0078_0079($o, $p, parse_str_packet($d, 'a[id;speed;opt1;opt2;option;view_class;hair;weapon;head_bottom;shield;head_top;head_mid;hair_color;clothes_color;head_dir;guild_id;emblem_id;manner;karma;sex;pos;dead_sit;base_level]lwwwwwwwwwwwwwwllwbbpw-bw'));
	}

	// 0079 - Unit Connected
	function parse_recv_0079(GenericBot &$o, $p, $d) {
		parse_recv_0078_0079($o, $p, parse_str_packet($d, 'a[id;speed;opt1;opt2;option;view_class;hair;weapon;head_bottom;shield;head_top;head_mid;hair_color;clothes_color;head_dir;guild_id;emblem_id;manner;karma;sex;pos;dead_sit]lwwwwwwwwwwwwwwllwbbpw-b'));
	}

	// 007b - Unit Move
	function parse_recv_007b(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;speed;opt1;opt2;option;view_class;hair;weapon;shield;head_bottom;tick;head_top;head_mid;hair_color;clothes_color;head_dir;guild_id;emblem_id;manner;karma;sex;pos_m;max_level]lwwwwwwwwwlwwwwwllwbbqb-b-b-w');
		/*
		$d['pos'] = array($d['pos_m'][0], $d['pos_m'][1]);
		$d['moving'] = true;
		$d['moving_p'] = call_user_func_array('map_get_path', $d['pos_m']);


		$moving_list[$d['id']] = getMTime();

		__update_entities($d['id'], $d, $d['view_class']);
		ia_moving($d['id']);
		*/
	}

	// 0087 - You Move
	function parse_recv_0087(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[tick;pos_m]lqb-');
		/*
		$p['pos'] = array($d['pos_m'][0], $d['pos_m'][1]);
		$p['moving'] = true;
		$p['moving_p'] = call_user_func_array('map_get_path', $d['pos_m']);
		*/
		/*
		list($w, $h, $m) = $player_data['map']['data'];
		extension_loaded('gd')  or dl('php_gd2.dll') or die("Please install GD2 extension.\n");
		$i = image_map($m, $w, $h);
		image_path($i, $p['moving_p']);
		imageGif($i, 'Map.gif');
		*/
	}

	// 0080 - Unit Lost (Died, Disappeared, Disconnected)
	function parse_recv_0080(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;type]lb');
		/*
		ia_lost($d['id']);

		$z = &$entities['all'][$d['id']];
		if (isset($z)) unset($entities['all'][$d['id']]);
		*/
	}


	// 0088 - Unit Position
	function parse_recv_0088(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;x;y]lww');
		/*
		global $entities;

		$e = &$entities['all'][$d['id']];
		$e['x'] = $d['x'];
		$e['y'] = $d['y'];

		ia_moved($d['id']);
		*/
	}

	// 01d8 - Unit Exists : 0078
	function parse_recv_01d8(GenericBot &$o, $p, $d) {
		parse_recv_0078_0079($o, $p, parse_str_packet($d, 'a[id;speed;opt1;opt2;option;view_class;viewid1;viewid2;head_bottom;head_top;head_mid;hair_color;clothes_color;head_dir;guild_id;emblem_id;manner;karma;sex;pos;dead_sit;base_level]lwwwwwwwwwwwwwllwbbpw-bw'));
	}

	// 01d9 - Unit Connected : 0079
	function parse_recv_01d9(GenericBot &$o, $p, $d) {
		//echo "Using 0x01D9 (Check correct)\n";
		parse_recv_0078_0079($o, $p, $d);
		//echo "Rest: " . strlen($d) . "\n";
	}

	// 01da - Unit Move : 007B
	function parse_recv_01da(GenericBot &$o, $p, $d) {
		parse_recv_007b($o, $p, $d);
		//echo "Unparsed packet: 0x01da\n";
	}

	// 0195 - Player Guild Info
	function parse_recv_0195($o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;name;guild_name;title]lz[24]z[24]-z[24]z[24]');
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

?>