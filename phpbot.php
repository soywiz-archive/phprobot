<?php
	require_once(dirname(__FILE__) . '/src/class.GenericBot.php');

	class Bot extends GenericBot {
//-----------------------------------------------------------------------------
// CONSTRUCTOR
//-----------------------------------------------------------------------------

		function __construct() {
			parent::__construct();
		}

//-----------------------------------------------------------------------------
// CONNECTION
//-----------------------------------------------------------------------------

		function onDisconnect() {
			//echo "Ha sido desconectado del servidor...\nReconectando dentro de 10 segundos...\n"; sleep(10);
			$this->reconnect();
		}

//-----------------------------------------------------------------------------
// MASTER
//-----------------------------------------------------------------------------

		function onMasterLogin() {
			$this->masterConnect('localhost:6900', 'test2', 'test2');
		}

		function onMasterLoginError() {
			$this->disconnect();
		}

//-----------------------------------------------------------------------------
// CHARA
//-----------------------------------------------------------------------------

		function onCharaLogin() {
			// $this->serverList
			$this->serverCharaSelect('wiz_server');
		}

		function onMasterCharaError() {
			$this->disconnect();
		}

		function onCharaSelect() {
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

			echo "TYPE: $type, TEXT: $text";
			if (isset($from)) {
				echo ", FROM: (" . $from_name . ")";
			}

			echo "\n";

			if (strtolower(trim($from_name)) == 'thewiz') {
				$data = explode(' ', $text, 2);
				if (!isset($data[1])) $data[1] = ''; else $data[1] = ltrim($data[1]);

				$command = $data[0];
				switch (strtolower(trim($command))) {
					case 'exit': exit; break;
					case 'say': $this->say($data[1]); break;
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
			$this->moveNear($e);
		}

		function onMoveStart(Entity &$e) {
			echo "* onMoveStart();\n";
			//echo $e->id . ': Moving Start (' . $e->x . ', ' . $e->y . ')'. "\n";
		}

		function onMoveEnd(Entity &$e) {
			echo "* onMoveEnd();\n";
			echo $e->id . ': Moving Start End (' . $e->x . ', ' . $e->y . ')'. "\n";
			// Mira al personaje
			//$this->lookAt($e);
		}
	}

//-----------------------------------------------------------------------------
// MAIN
//-----------------------------------------------------------------------------

	$mybot = new Bot();
	while (true) {
		$mybot->process();
	}

	exit;
?>