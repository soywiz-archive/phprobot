<?php
	require_once(dirname(__FILE__) . '/src/class.GenericBot.php');

	class Bot extends GenericBot {


//-----------------------------------------------------------------------------
// CONSTRUCTOR
//-----------------------------------------------------------------------------

		function __construct() {
			echo "* __contruct();\n";
			parent::__construct();
		}

//-----------------------------------------------------------------------------
// CONNECTION
//-----------------------------------------------------------------------------

		function onDisconnect() {
			echo "* onDisconnect();\n";
			//echo "Ha sido desconectado del servidor...\nReconectando dentro de 10 segundos...\n"; sleep(10);
			$this->reconnect();
		}

//-----------------------------------------------------------------------------
// MASTER
//-----------------------------------------------------------------------------

		function onMasterLogin() {
			echo "* onMasterLogin();\n";
			$this->masterConnect('localhost:6900', 'test2', 'test2');
		}

		function onMasterLoginError() {
			echo "* onMasterLoginError();\n";
			$this->disconnect();
		}

//-----------------------------------------------------------------------------
// CHARA
//-----------------------------------------------------------------------------

		function onCharaLogin() {
			echo "* onCharaLogin();\n";
			// $this->serverList
			$this->serverCharaSelect('wiz_server');
		}

		function onMasterCharaError() {
			echo "* onMasterCharaError();\n";
			$this->disconnect();
		}

		function onCharaSelect() {
			echo "* onCharaSelect();\n";
			// $this->characterList
			//$this->charaSelect('Nena');
			$this->charaSelect('Nena');
		}

//-----------------------------------------------------------------------------
// MAP
//-----------------------------------------------------------------------------

		function onMapStart() {
			echo "* onMapStart();\n";
			//echo "Iniciar Mapa\n";
			//sendSkillUse($this, SKILL_AL_HEAL, 10, Entity::getEntityById)
		}

		function onCharaInfoUpdate() {
			echo "* onCharaInfoUpdate();\n";
			// Show updated info
			//$this->player->trace();
			//print_r($this->characterSelected);
		}

		function onAppear(Entity &$e) {
			echo "* onAppear();\n";
			$name = strtolower(trim($e->name));
			if ($name == 'thewiz') {
				/*
				//$this->useSkill(SKILL_AL_HEAL,     10, $e);
				$this->useSkill(SKILL_AL_INCAGI,   10, $e);
				$this->useSkill(SKILL_AL_INCAGI,   10, $e);
				$this->useSkill(SKILL_AL_INCAGI,   10, $e);
				//$this->useSkill(SKILL_AL_ANGELUS,  10, $e);
				$this->useSkill(SKILL_AL_BLESSING, 10, $e);
				$this->useSkill(SKILL_PR_IMPOSITIO, 5, $e);
				*/

				$this->lookAt($e);
			}
		}

		function onSay($type, $text, &$from = NULL, $from_name = NULL) {
			echo "* onSay();\n";
			if (isset($from) && !($from instanceof Entity)) return;
			if (!isset($from_name) && isset($from)) $from_name = $from->name;

			//echo "TYPE: $type, TEXT: $text"; if (isset($from)) echo ", FROM: (" . $from_name . ")"; echo "\n";

			if (strtolower(trim($from_name)) == 'thewiz') {
				$data = explode(' ', $text, 2);
				if (!isset($data[1])) $data[1] = ''; else $data[1] = ltrim($data[1]);

				$command = $data[0];
				switch (strtolower(trim($command))) {
					case 'exit': exit; break;
					case 'say':  $this->say($data[1]); break;
					case 'move': $this->moveAt($from->x, $from->y); break;
					case 'move_near': $this->moveNear($from, 4); break;
					case 'where':
						$to_say = $this->map->name . ' : ' . $this->player->x . ', ' . $this->player->y;
						echo $to_say . "\n";
						$this->say($to_say);
					break;
				}
			}
		}

//-----------------------------------------------------------------------------
// MAP
//-----------------------------------------------------------------------------

		function onMoving(Entity &$e) {
			echo "* onMoving();\n";
			//echo $e->id . ': ' . $e->x . ', ' . $e->y . ' - (' . $e->name . ')' . "\n";
			//$this->lookAt($e);
			//$this->moveNear($e);
			if ($e->visible) {
				if (strtolower($e->name) == 'thewiz') {
					$this->moveNear($e, 4);
				}
			} else {
				if (strtolower($e->name) == 'thewiz') {
					$this->moveNear($e, 4);
				}
				//$e->trace();
			}
		}

		function onUpdateHP(Entity &$e) {
			echo $e->name . ' - ' . $e->hp . "/" . $e->hp_max . "\n";
		}

		function onMoveStart(Entity &$e) {
			echo "* onMoveStart();\n";
			//$this->moveNearTo($e->to_x, $e->to_y);
			//$this->moveNearTo($e->to_x, $e->to_y, 1);
			//echo $e->id . ': Moving Start (' . $e->x . ', ' . $e->y . ')'. "\n";
		}

		function onMoveEnd(Entity &$e) {
			// Si ya ha acabado su propio movimiento
			if ($e === $this->player) {
				if ($e = &Entity::getEntityByName($this, 'TheWiz')) {
					if ($e->visible) $this->lookAt($e);
				}
			} else if (strtolower($e->name) == 'thewiz') {
				if ($e->visible) $this->lookAt($e);
			}

			echo "* onMoveEnd();\n";
			//echo $e->id . ': Moving Start End (' . $e->x . ', ' . $e->y . ')'. "\n";
			// Mira al personaje
			//$this->lookAt($e);
			//$this->moveNearTo($e->to_x, $e->to_y, 1);
		}

		function onDealRequest(Entity &$e) {
			$this->dealRequestAccept();
			//$this->dealCancel();

			//$this->dealRequest($e);
		}
	}

//-----------------------------------------------------------------------------
// MAIN
//-----------------------------------------------------------------------------

	$mybot = new Bot();
	while (true) {
		try {
			$mybot->process();
		} catch (Exception $e) {
			echo $e;
		}
	}

	exit;
?>