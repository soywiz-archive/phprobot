<?php
	require_once(dirname(__FILE__) . '/src/class.GenericBot.php');

	class Bot extends GenericBot {
		private static $look_player = 'Master';

		// Login
		function onDisconnect()       { $this->reconnect(); }
		function onMasterLogin()      { $this->masterConnect('localhost:6900', 'test2', 'test2'); }
		function onMasterLoginError() { $this->disconnect(); }
		function onCharaLogin()       { $this->serverCharaSelect('test_server'); }
		function onCharaSelectError() { $this->disconnect(); }
		function onCharaSelect()      { $this->charaSelect('CharaTest'); }

		// Process
		function onMoving(Entity &$e) {
			// Look At Entity while moving if his/her name is Bot::$look_player
			if (strcasecmp($e->name, Bot::$look_player)) $this->lookAt($e);
		}

		function onAppear(Entity &$e) {
			// Say Hello when Bot::$look_player appears.
			if (strcasecmp($e->name, Bot::$look_player)) $this->say('Hello ^^');
		}

	}

	$mybot = new Bot();
	while (true) {
		try {
			$mybot->process();
		} catch (Exception $e) {
			echo $e;
		}
	}
?>