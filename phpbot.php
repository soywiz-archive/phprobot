<?php
	require_once(dirname(__FILE__) . '/src/class.GenericBot.php');

	class Bot extends GenericBot {
		function onDisconnect() {
			$this->reconnect();
		}

		function onMasterLogin() {
			$this->masterConnect('localhost:6900', 'test2', 'test2');
		}

		function onMasterLoginError() {
			$this->disconnect();
		}

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
	}

	$mybot = new Bot();
	while (true) {
		$mybot->process();
	}

	exit;
//-----------------------------------------------------------------------------
/*
	require_once(dirname(__FILE__) . '/core/src/main.php');

	class Bot extends GenericBot {
// ----------------------------------------------------------------------------
// GLOBAL
// ----------------------------------------------------------------------------
		function onDisconnect($error_id = NULL, $error_msg = NULL) {
			// Overwrite ip?
			$this->connect('localhost:6900', false);
		}

// ----------------------------------------------------------------------------
// MASTER
// ----------------------------------------------------------------------------

		function onLoginMasterFailed($error_id, $error_msg) {
			// Disconnect
			$this->disconnect();
		}

		function onLoginMaster($server_list, $sex) {
			$this->selectMasterServer('wiz_server');
		}

// ----------------------------------------------------------------------------
// CHARACTER
// ----------------------------------------------------------------------------

		function onLoginCharaFailed($error_id, $error_msg) {
			// Disconnect
			$this->disconnect();
		}

		function onLoginChara($chara_list) {
			$this->selectCharacter('-wiz-');
		}

// ----------------------------------------------------------------------------
// MAP
// ----------------------------------------------------------------------------

		function onLoginMapFailed($error_id, $error_msg) {
			// Disconnect
			$this->disconnect();
		}

		function onLoginMap($map_name) {
		}

		function onTick() {
		}

// ----------------------------------------------------------------------------
// /
// ----------------------------------------------------------------------------

	}

	$mybot = new Bot(); $mybot->run();
*/
?>