<?php
	Import('Ragnarok.Server');
	Import('Ragnarok.Status');
	Import('Entity.Entity');
	Import('Map.MapRagnarok');

	// 0073 - Enter Map
	function RecivePacket0x0073(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$Bot->Position = new Position($Data['Position'][0], $Data['Position'][1]);
		$Bot->Map      = new MapRagnarok($Bot->ServerZone->MapName);

		SendZoneLoaded($Bot);
		SendZoneGetEntityName($Bot, $Bot->Id);
  	}

	// 0081 - Disconnect
	function RecivePacket0x0081(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$Bot->Disconnect();
  	}

	// 008e - Global Message
	function RecivePacket0x008e(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$Bot->SetStepCallBack('OnZoneSay', GenericBot::SPEECH_GLOBAL, null, $Data['Text']);
	}

	// 00b1 - Your Status Info (Exp, Job Exp, Zeny)
	function RecivePacket0x00b1(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		RecivePacket0x00b0($Bot, $PId, $Data, $DataRaw);
	}

	// 00b0 - Your Status Info
	function RecivePacket0x00b0(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$Type = &$Data['Type']; $Value = &$Data['Value'];

		switch ($Type) {
			case 0x0000: $Bot->Speed         = $Value; break;
			case 0x0001: $Bot->ExpBase       = $Value; break;
			case 0x0002: $Bot->ExpJob        = $Value; break;
			case 0x0003:                               break;
			case 0x0004:                               break;
			case 0x0005: $Bot->Hp            = $Value; break;
			case 0x0006: $Bot->HpMax         = $Value; break;
			case 0x0007: $Bot->Sp            = $Value; break;
			case 0x0008: $Bot->SpMax         = $Value; break;
			case 0x0009: $Bot->PointsStatus  = $Value; break;
			case 0x000A:                               break;
			case 0x000B: $Bot->LevelBase     = $Value; break;
			case 0x000C: $Bot->PointsSkill   = $Value; break;
			case 0x000D:                               break;
			case 0x000E:                               break;
			case 0x000F:                               break;
			case 0x0010:                               break;
			case 0x0011:                               break;
			case 0x0012:                               break;
			case 0x0013:                               break;
			case 0x0014: $Bot->Zenny         = $Value; break;
			case 0x0015:                               break;
			case 0x0016: $Bot->ExpBaseNext   = $Value; break;
			case 0x0017: $Bot->ExpJobNext    = $Value; break;
			case 0x0018: $Bot->Weight        = $Value; break;
			case 0x0019: $Bot->WeightMax     = $Value; break;
			case 0x001A:                               break;
			case 0x001B:                               break;
			case 0x001C:                               break;
			case 0x001D:                               break;
			case 0x001E:                               break;
			case 0x001F:                               break;
			case 0x0020:                               break;
			case 0x0021:                               break;
			case 0x0022:                               break;
			case 0x0023:                               break;
			case 0x0024:                               break;
			case 0x0025:                               break;
			case 0x0026:                               break;
			case 0x0027:                               break;
			case 0x0028:                               break;
			case 0x0029: $Bot->Atk           = $Value; break;
			case 0x002A: $Bot->AtkPer        = $Value; break;
			case 0x002B: $Bot->MAtk          = $Value; break;
			case 0x002C: $Bot->MAtkMax       = $Value; break;
			case 0x002D: $Bot->Def           = $Value; break;
			case 0x002E: $Bot->DefPer        = $Value; break;
			case 0x002F: $Bot->MDef          = $Value; break;
			case 0x0030: $Bot->MDefPer       = $Value; break;
			case 0x0031: $Bot->Hit           = $Value; break;
			case 0x0032: $Bot->Flee          = $Value; break;
			case 0x0033: $Bot->FleePer       = $Value; break;
			case 0x0034: $Bot->Crit          = $Value; break;
			case 0x0035: $Bot->Aspd          = $Value; break;
			case 0x0036:                               break;
			case 0x0037: $Bot->LevelJob      = $Value; break;

			default: echo "Unknown 0x00B0/0x00B1 type: {$Type} : {$Value}\n"; break;
		}
	}

	// 0097 - Private Message (From Other)
	function RecivePacket0x0097(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		$Entity = new Entity(null, -1);
		$Entity->Name = $Data['Name'];

		$Bot->SetStepCallBack('OnZoneSay', GenericBot::SPEECH_GLOBAL, $Entity, $Data['Text']);
	}


	// 010f - Skills List
	function RecivePacket0x010f(GenericBot &$Bot, $PId, $Data, $DataRaw) {
		print_r($Data);
		//$Bot->Dump();
		exit;
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
			$z->name      = isset($o->lists['Skill_name'][$v['id']])  ? $o->lists['Skill_name'][$v['id']]  : 'Unknown';
			$z->title     = isset($o->lists['Skill_title'][$v['id']]) ? $o->lists['Skill_title'][$v['id']] : 'Unknown';
			$z->delay     = isset($o->lists['Skill_delay'][$v['id']]) ? $o->lists['Skill_delay'][$v['id']] : 0;
			$z->canup     = $v['canup'];
		}
	}

/*
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
		$e        = &Entity::getEntityByIdCreate($o, $id);

		$monster_names = &$o->lists['monster_names'];

		$names[$id] = $name;
		$names_id[trim(strtolower($name))] = $id;

		if (isset($e->group) && ($e->group == 'monsters')) {
			$monster_names[$e->view_id] = $name;
		}

		$appear = !isset($e->_name);

		$e->name = $name;

		if ($appear) $o->onAppear($e);

		//echo "[{$id}] = '{$name}';\n";
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
			case 5: $c->hp = $value; $o->onUpdateHP($o->player); break;
			case 7: $c->sp = $value; $o->onUpdateSP($o->player); break;
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

	// 0098 - Private Message (From Other)
	function parse_recv_0097(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[from;text]z[24]z[rest]');

		//echo "-----\n"; foreach ($o->lists['Entity_memo'] as $k => $v) echo $v->id . ' - ' . $v->name . "\n"; echo "-----\n";
		//print_r($o->lists['Entity_name_list']); echo "DEBUG: PRIVATE MENSAJAE FROM: " . $d['from'] . "\n";

		if ($entity = &Entity::getEntityByName($o, $d['from'])) {
			$o->onSay(GB_SAY_TYPE_PRIVATE, $d['text'], $entity, $d['from']);
		} else {
			// Se envía a sí mismo (PATH) (REVISE)
			$o->onSay(GB_SAY_TYPE_PRIVATE, $d['text'], $o->player, $d['from']);
		}
		//$entity->trace();
	}

	// 008d - Global Message (From Other)
	function parse_recv_008d(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;text]lz[rest]');

		$entity = &Entity::getEntityByIdCreate($o, $d['id']);

		list($from, $message) = explode(' : ', $d['text'], 2);

		$o->onSay(GB_SAY_TYPE_PUBLIC, $message, $entity, $from);
	}

	// 008e - Global Message
	function parse_recv_008e(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[text]z[rest]');
		//echo "::" . $d['text'] . "\n";
		$o->onSay(GB_SAY_TYPE_GLOBAL, $d['text']);
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
			$z->name      = isset($o->lists['Skill_name'][$v['id']])  ? $o->lists['Skill_name'][$v['id']]  : 'Unknown';
			$z->title     = isset($o->lists['Skill_title'][$v['id']]) ? $o->lists['Skill_title'][$v['id']] : 'Unknown';
			$z->delay     = isset($o->lists['Skill_delay'][$v['id']]) ? $o->lists['Skill_delay'][$v['id']] : 0;
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
		$o->onSay(GB_SAY_TYPE_GUILD, $d['text1']);
		$o->onSay(GB_SAY_TYPE_GUILD, $d['text2']);
		//echo "Guild Topic:  " . $d['text1'] . "\n"; echo "Guild Notice: " . $d['text2'] . "\n";
	}

	// 016d - Guild Member Online Status
	function parse_recv_016d(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;account_id;status]lll');

		$entity = Entity::getEntityByIdCreate($o, $d['id']);

		// visible/online
		$entity->online = $d['status'];
		//print_r($d);
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	function parse_recv_0078_0079(GenericBot &$o, $p, $d) {
		$entity = Entity::getEntityByIdCreate($o, $d['id']);

		if (isset($d['pos']) && sizeof($d['pos']) >= 2) {
			$entity->setXY($d['pos'][0], $d['pos'][1]);
		}

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

		if (isset($entity->_name) && $entity->_name) $o->onAppear($entity);

		$entity->visible = true;
	}

	// 0078 - Unit Exists
	function parse_recv_0078(GenericBot &$o, $p, $d) {
		parse_recv_0078_0079($o, $p, parse_str_packet($d, 'a[id;speed;opt1;opt2;option;view_class;hair;weapon;head_bottom;shield;head_top;head_mid;hair_color;clothes_color;head_dir;guild_id;emblem_id;manner;karma;sex;pos;dead_sit;base_level]lwwwwwwwwwwwwwwllwbbpw-bw'));

		//$entity->visible = true;
	}

	// 0079 - Unit Connected
	function parse_recv_0079(GenericBot &$o, $p, $d) {
		parse_recv_0078_0079($o, $p, parse_str_packet($d, 'a[id;speed;opt1;opt2;option;view_class;hair;weapon;head_bottom;shield;head_top;head_mid;hair_color;clothes_color;head_dir;guild_id;emblem_id;manner;karma;sex;pos;dead_sit]lwwwwwwwwwwwwwwllwbbpw-b'));

		//$entity->visible = true;
	}

	// 007b - Unit Move
	function parse_recv_007b(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;speed;opt1;opt2;option;view_class;hair;weapon;shield;head_bottom;tick;head_top;head_mid;hair_color;clothes_color;head_dir;guild_id;emblem_id;manner;karma;sex;pos_m;max_level]lwwwwwwwwwlwwwwwllwbbqb-b-b-w');
		$pm = &$d['pos_m'];

		$entity = &Entity::getEntityByIdCreate($o, $d['id']);

		//echo 'UNIT MOVE: - ' . $entity->id . ' : ' . $p . "\n";

		foreach (array('speed', 'opt1', 'opt2', 'option', 'view_class', 'hair', 'weapon', 'shield',
		'head_bottom', 'tick', 'head_top', 'head_mid', 'hair_color', 'clothes_color', 'head_dir',
		'manner', 'karma', 'sex', 'max_level') as $k) $entity->$k == $d[$k];

		//$entity->visible = true; $entity->setXY($pm[0], $pm[1]);

		//$entity->emblem = &Emblem::getEmblemByIdCreate($o, $d['emblem_id']);
		//$entity->guild  = &Guild::getGuildByIdCreate($o, $d['guild_id']);

		$entity->move($pm[0], $pm[1], $pm[2], $pm[3], $d['speed']);
	}

	// 0087 - You Move
	function parse_recv_0087(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[tick;pos_m]lqb-');
		$pm = &$d['pos_m'];
		//$o->player->trace(); exit;
		$o->player->move($pm[0], $pm[1], $pm[2], $pm[3], $o->player->speed);
		$o->player->visible = true;
		//$p['pos'] = array($d['pos_m'][0], $d['pos_m'][1]);
		//$p['moving'] = true;
		//$p['moving_p'] = call_user_func_array('map_get_path', $d['pos_m']);
		//list($w, $h, $m) = $player_data['map']['data'];
		//extension_loaded('gd')  or dl('php_gd2.dll') or die("Please install GD2 extension.\n");
		//$i = image_map($m, $w, $h);
		//image_path($i, $p['moving_p']);
		//imageGif($i, 'Map.gif');
	}

	// 0080 - Unit Lost (Died, Disappeared, Disconnected)
	function parse_recv_0080(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;type]lb');

		$entity = Entity::getEntityByIdCreate($o, $d['id']);
		$entity->visible = false;
		$entity->disappear();
		$o->onDisAppear($entity);

		//ia_lost($d['id']);

		//$z = &$entities['all'][$d['id']];
		//if (isset($z)) unset($entities['all'][$d['id']]);
	}


	// 0088 - Unit Position
	function parse_recv_0088(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;x;y]lww');
		//global $entities;

		//$e = &$entities['all'][$d['id']];
		//$e['x'] = $d['x'];
		//$e['y'] = $d['y'];

		//ia_moved($d['id']);
	}

	// 01d8 - Unit Exists : 0078
	function parse_recv_01d8(GenericBot &$o, $p, $d) {
		parse_recv_0078_0079($o, $p, parse_str_packet($d, 'a[id;speed;opt1;opt2;option;view_class;viewid1;viewid2;head_bottom;head_top;head_mid;hair_color;clothes_color;head_dir;guild_id;emblem_id;manner;karma;sex;pos;dead_sit;base_level]lwwwwwwwwwwwwwllwbbpw-bw'));
		//$entity->visible = true;
	}

	// 01d9 - Unit Connected : 0079
	function parse_recv_01d9(GenericBot &$o, $p, $d) {
		//echo "Using 0x01D9 (Check correct)\n";
		parse_recv_0078_0079($o, $p, $d);
		//echo "Rest: " . strlen($d) . "\n";

		//$entity->visible = true;
	}

	// 01da - Unit Move : 007B
	function parse_recv_01da(GenericBot &$o, $p, $d) {
		parse_recv_007b($o, $p, $d);
		//echo "Unparsed packet: 0x01da\n";

		//$entity->visible = true;
	}

	// 0195 - Player Guild Info
	function parse_recv_0195(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;name;guild_name;title]lz[24]z[24]-z[24]z[24]');
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	// 00fb - Party Users List
	function parse_recv_00fb(GenericBot &$o, $p, $d) {
		$party = &Party::getPartyByIdCreate($o, 0);
		if (!isset($o->party) || !($o->party instanceof Party)) $o->party = &$party;
		$d = parse_str_packet($d, 'a[name;players]z[24]x[rest][a[id;name;map_name;leader;online]lz[24]z[16]bb]');
		$party->name = $d['name'];
		foreach ($d['players'] as $zm) {
			$player = &Entity::getEntityByIdCreate($o, $zm['id']);
			$player->name         = $zm['name'];
			$player->map_name     = $zm['map_name'];
			$player->online       = !$zm['online'];
			$player->party        = $party;
			$player->party_leader = !$zm['leader'];
			$party->member_list[$player->id] = &$player;
		}

		//if (!isset($player_data['party'])) $player_data['party'] = array();

		//$player_data['party'] = array_merge($player_data['party'], );
	}

	// 0101 - Party Share
	function parse_recv_0101(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[share_exp;share_item]ww');
		$party = &Party::getPartyByIdCreate($o, 0);
		$party->share_exp  = $d['share_exp'];
		$party->share_item = $d['share_item'];
		//echo sizeof($party);
		//print_r($party->getMemberNameList()); $party->trace();
	}

	// 0152 - ??
	function parse_recv_0152(GenericBot &$o, $p, $d) {
		echo "Unparsed packet: 0x0152\n";
	}

	// 00fa - Party Create Failed
	function parse_recv_00fa(GenericBot &$o, $p, $d) {
		echo "Unparsed packet: 0x00fa\n";
	}

	// 00fd - Party Join Request (From You)
	function parse_recv_00fd(GenericBot &$o, $p, $d) {
		echo "Unparsed packet: 0x00fd\n";
	}

	// 00fe - Party Join Request (From Other)
	function parse_recv_00fe(GenericBot &$o, $p, $d) {
		echo "Unparsed packet: 0x00fe\n";
	}

	// 0104 - Party User Info
	function parse_recv_0104(GenericBot &$o, $p, $d) {
		echo "Unparsed packet: 0x0104\n";
	}

	// 0105 - Party User Left
	function parse_recv_0105(GenericBot &$o, $p, $d) {
		echo "Unparsed packet: 0x0105\n";
	}

	// 0106 - Party HP
	function parse_recv_0106(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;hp;hp_max]lww');

		$entity = &Entity::getEntityByIdCreate($o, $d['id']);
		$entity->hp = $d['hp'];
		$entity->hp_max = $d['hp_max'];

		$o->onUpdateHP($entity);
	}

	// 0107 - Party Move
	function parse_recv_0107(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;x;y]lww');

		$entity = &Entity::getEntityByIdCreate($o, $d['id']);

		$entity->setXYMap($d['x'], $d['y']);
		if (!$entity->visible) $entity->setXY($d['x'], $d['y']);

		$o->onMoving($entity);

		//print_r($d);
		//echo "Unparsed packet: 0x0107\n";
	}

	// 0109 - Party Message
	function parse_recv_0109(GenericBot &$o, $p, $d) {
		echo "Unparsed packet: 0x0109\n";
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////


	// 00e5 - Trade Request
	function parse_recv_00e5(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[name]z[24]');

		$o->onTradeRequest(
			$o->trade_entity      = Entity::getEntityByName($o, $d['name']),
			$o->trade_entity_name = $d['name']
		);

		//$entity->visible = true;
	}

	// 00e7 - Trade Response
	function parse_recv_00e7(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[mes]b');

		switch ($d['mes']) {
			case DealConst::SUCCESS:
				$o->onTradeStart($o->trade_entity, $o->trade_entity_name);
			break;
			default:
				$o->onTradeCancel($o->trade_entity, $o->trade_entity_name, $d['mes']);
			break;
		}

		//$entity->visible = true;
	}

	// 00ea - Add items
	function parse_recv_00ea(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[index;mes]wb');

		//echo $d['index'] . ': ' . $d['mes'] . "\n";
	}

	// 00ec - Trade OK From
	function parse_recv_00ec(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[from]b');

		$o->tradeOkFlags |= (1 << $d['from']);

		if ($d['from'] == DealConst::OK_OTHER) $o->onTradeOk($o->trade_entity, $o->trade_entity_name);

		//echo "Trade OK : " . $o->tradeOkFlags . ' - ' . $d['from'] . "\n";

		// Si ambos están disponibles
		//echo 'FLAGS: ' . $o->tradeOkFlags . ' - ' . DealConst::OK_SELF . ' | ' . DealConst::OK_OTHER . "\n";
		if (($o->tradeOkFlags & (1 << DealConst::OK_SELF)) && ($o->tradeOkFlags & (1 << DealConst::OK_OTHER))) {
			$o->onTradeFinish($o->trade_entity, $o->trade_entity_name);
		}
	}

	// 00ee - Trade Cancel (message)
	function parse_recv_00ee(GenericBot &$o, $p, $d) {
		$o->onTradeCancel($o->trade_entity, $o->trade_entity_name, DealConst::ERROR_CANCEL);
	}

	// 00f0 - Trade Success (message)
	function parse_recv_00f0(GenericBot &$o, $p, $d) {
		$o->onTradeSuccess($o->trade_entity, $o->trade_entity_name);
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	// 019b - Effect
	function parse_recv_019b(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[id;type]ll');

		$e = &Entity::GetEntityByIdCreate($o, $d['id']);

		switch ($d['type']) {
			case 0x00: $o->onEffect($e, $d['type']); break; // Base Level Up
			case 0x01: $o->onEffect($e, $d['type']); break; // Job Level Up
			case 0x03: $o->onEffect($e, $d['type']); break; // Refining
			default: throw(new Exception("019b type unknown (" . $d['type'] . ")")); break;
		}
	}

	// 008a - Attack/Sit/Stand
	function parse_recv_008a(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[src_id;dst_id;tick;src_speed;dst_speed;param1;param2;type;param3]lllllwwbw');

		if ($d['src_id'] != 0) $from = &Entity::GetEntityByIdCreate($o, $d['src_id']); else $from = NULL;
		if ($d['dst_id'] != 0) $to   = &Entity::GetEntityByIdCreate($o, $d['dst_id']); else $to = NULL;

		switch ($d['type']) {
			case 0x00: break; // MISS/Damage
			case 0x01: break; // Item pickup
			case 0x02: $o->onSit($from);   break; // Sit Down
			case 0x03: $o->onStand($from); break; // Stand Up
			case 0x08: break; // Multiple Attack
			case 0x0a: break; // Critical Attack
			case 0x0b: break; // Perfect Evade
			default:   throw(new Exception("008a type unknown (" . $d['type'] . ")")); break;
		}
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

	// 007f - Server Tick
	function parse_recv_007f(GenericBot &$o, $p, $d) {
		$d = parse_str_packet($d, 'a[tick]l');

		$o->onServerTick($d['tick']);
	}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

*/
?>