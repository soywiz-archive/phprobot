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
			echo "Iniciar Mapa\n";
			//sendSkillUse($this, SKILL_AL_HEAL, 10, Entity::getEntityById)
		}

		function onCharaInfoUpdate() {
			// Show updated info
			//$this->player->trace();
			//print_r($this->characterSelected);
		}

		function onAppear(Entity &$e) {
			$name = strtolower(trim($e->name));
			if ($name == 'thewiz') {
				//$this->useSkill(SKILL_AL_HEAL,     10, $e);
				$this->useSkill(SKILL_AL_INCAGI,   10, $e);
				$this->useSkill(SKILL_AL_INCAGI,   10, $e);
				$this->useSkill(SKILL_AL_INCAGI,   10, $e);
				//$this->useSkill(SKILL_AL_ANGELUS,  10, $e);
				$this->useSkill(SKILL_AL_BLESSING, 10, $e);
				$this->useSkill(SKILL_PR_IMPOSITIO, 5, $e);
			}
		}

		function onSay($type, $text, &$from = NULL) {
			if (isset($from) && !($from instanceof Entity)) return;
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