<?php
	require_once(dirname(__FILE__) . '/system.php');

	//echo 'Waiting'; for ($n = 0; $n < 5; $n++) { echo '.'; sleep(1); } echo "\n";

	require_class('Exception');
	require_class('Tasks');
	require_class('TasksTime');
	require_class('Timer');
	require_class('Map');
	require_class('PacketSocket');

	define('GB_SYSTEM_LODADED', true);

	require_once(PATH_SYSTEM . '/types.php');

	require_once(PATH_SYSTEM . '/send.master.php');
	require_once(PATH_SYSTEM . '/send.chara.php');
	require_once(PATH_SYSTEM . '/send.map.php');

	require_once(PATH_SYSTEM . '/recv.master.php');
	require_once(PATH_SYSTEM . '/recv.chara.php');
	require_once(PATH_SYSTEM . '/recv.map.php');

	// Carga los paquetes del ragnarok en el formato de mod-kore
	$ragnarokPacketList = new PacketList(array(
		PATH_SYSTEM . '/packets.recv.txt',
		PATH_SYSTEM . '/packets.send.txt'
	));

	// Crea constantes de STEPS de conexión
	make_enum(
		'GB_STEP_MASTER_LOGIN',
		'GB_STEP_MASTER_LOGIN_ERROR',
		'GB_STEP_MASTER_PROCESS',
		'GB_STEP_CHARA_LOGIN',
		'GB_STEP_CHARA_LOGIN_ERROR',
		'GB_STEP_CHARA_LOGIN_SUCCESS',
		'GB_STEP_CHARA_PROCESS',
		'GB_STEP_MAP_LOGIN',
		'GB_STEP_MAP_PROCESS',
		'GB_STEP_DISCONNECTED'
	);

	// Clase para bots genérica
	abstract class GenericBot {
		public $lists = array();
		public $player;

		public $tasks;
		public $sock;
		public $step;
		public $map;

		public $versionCode = 0x14;
		public $versionNum  = 0x02;

		public $connectionData;
		public $sex;
		public $serverList;
		public $characterList;
		public $characterSelected;

		public $overwriteIp = false;

		public $masterServer;
		public $charaServer;
		public $mapServer;

		public $busy;
		public $busy_skill;
		public $busy_time;

		public $lastRun;

		// Error fields
		protected $errorId = false;
		protected $errorText;

// ----------------------------------------------------------------------------
// GENERAL
// ----------------------------------------------------------------------------

		function setBusy($flag) {
			echo "[" . ($flag ? 'O' : 'X') . "]";
			if ($flag) {
				$this->busy       = true;
				$this->busy_skill = true;
			} else {
				$this->busy       = false;
				$this->busy_skill = false;
			}
		}

		function __construct() {
			$this->tasks = new Tasks();
			$this->sock  = new PacketSocket($GLOBALS['ragnarokPacketList']);
			$this->disconnect();
			$this->setBusy(false);
			$this->busy_time  = new Timer();
			Skill::load($this, PATH_SYSTEM . '/skills.txt');
		}

		function disconnect() {
			$this->step = GB_STEP_DISCONNECTED;
		}

		function reconnect() {
			$this->step = GB_STEP_MASTER_LOGIN;
		}

		function process() {
			usleep(1000);

			switch ($this->step) {
				case GB_STEP_DISCONNECTED:        $this->onDisconnect();       break;
				case GB_STEP_MASTER_LOGIN:        $this->onMasterLogin();      break;
				case GB_STEP_MASTER_LOGIN_ERROR:  $this->onMasterLoginError(); break;
				case GB_STEP_CHARA_LOGIN:         $this->onCharaLogin();       break;
				case GB_STEP_CHARA_LOGIN_ERROR:   $this->onCharaLoginError();  break;
				case GB_STEP_CHARA_LOGIN_SUCCESS: $this->onCharaSelect();      break;

				case GB_STEP_MAP_PROCESS:

					echo $this->busy ? 'O' : 'X';
					if (!$this->busy) {
						if (($z = $this->busy_time->dist()) > 0) {
							$this->lastRun = time();
							$this->tasks->run($this);
						}
						//echo $z . "\n";
					} else {
						//echo ".";
					}

					//echo '.';
				case GB_STEP_MASTER_PROCESS: case GB_STEP_CHARA_PROCESS:
					while ($pk = $this->sock->extractPacket()) {
						list($p, $d) = $pk;
						$hex = str_pad(dechex($p), 4, '0', STR_PAD_LEFT);

						// Call functions
						$f = "parse_recv_{$hex}";
						//echo "- {$f}\n";
						if (!function_exists($f)) {
							echo "La función '{$f}' no está definida\n";
						} else {
							$f($this, $p, $d);
						}
					}
				break;
			}
		}

// ----------------------------------------------------------------------------
// ERROR
// ----------------------------------------------------------------------------

		final function setError($errorId, $errorText) {
			list($this->errorId, $this->errorText) = array($errorId, $errorText);
		}

		final function setErrorSuccess() {
			$this->errorId = false;
		}

		function getError() {
			if ($this->errorId === false) return false;
			return get_class($this) .  ': Error ID: ' . $this->errorId . '. ' . $this->errorText;
		}

		function getErrorText() {
			if ($this->errorId === false) return false;
			return $this->errorText;
		}

		function getErrorId() {
			return $this->errorId;
		}

// ----------------------------------------------------------------------------
// MASTER
// ----------------------------------------------------------------------------

		function masterConnect($host, $user, $pass) {
			$this->sock->connect($this->masterServer = $host, 6900);
			sendMasterLogin($this, $user, $pass, $this->versionCode, $this->versionNum);

			$this->step = GB_STEP_MASTER_PROCESS;
		}

// ----------------------------------------------------------------------------
// CHARA
// ----------------------------------------------------------------------------

		function serverCharaSelect($server) {
			$similar = array();
			foreach ($this->serverList as $id => $_server) $similar[$id] = str_replace(' ', '_', strtolower(trim($_server['name'])));

			$similar = getSimilarArray($similar, str_replace(' ', '_', strtolower(trim($server))));
			$ms = &$this->serverList[$id = array_shift(array_keys($similar))];

			if ($this->overwriteIp) list($ms['ip']) = getIpAndPort($this->masterServer, 6900);
			$this->charaServer = $ms['ip'] . ':' . $ms['port'];

			$this->sock->connect($ms['ip'], $ms['port']);
			sendCharaServerSelect($this, $id);

			$this->connectionData['id_character2'] = getr32($this->sock->extract(4));

			$this->step = GB_STEP_CHARA_PROCESS;
		}

		function charaSelect($character) {
			$similar = array();
			foreach ($this->characterList as $id => $_character) $similar[$id] = str_replace(' ', '_', strtolower(trim($_character->name)));

			$similar = getSimilarArray($similar, str_replace(' ', '_', strtolower(trim($character))));

			$ms = &$this->characterList[$id = array_shift(array_keys($similar))];
			$this->characterSelected = &$ms;

			$this->connectionData['id_character']  = $ms->id;

			sendCharaSelect($this, $id);

			$this->step = GB_STEP_CHARA_PROCESS;
		}

// ----------------------------------------------------------------------------
// MAP
// ----------------------------------------------------------------------------

		function useSkill($skill, $level, Entity &$e) {
			if (!($skill instanceof Skill)) $skill = Skill::getSkillById($this, $skill);

			$this->tasks->add(array('finalUseSkill', $skill, $level, $e));
		}

		function useSkillLast($skill, $level, Entity &$e) {
			if (!($skill instanceof Skill)) $skill = Skill::getSkillById($this, $skill);

			$this->tasks->addLast(array('finalUseSkill', $skill, $level, $e));
		}

		function finalUseSkill($skill, $level, Entity &$e) {
			$this->setBusy(true);

			$this->busy_time = new Timer(NULL, $skill->delay + 200);

			if (!($skill instanceof Skill)) $skill = Skill::getSkillById($this, $skill);

			$skill->trace();

			sendSkillUse($this, $skill->id, min($level, $skill->level_max), $e->id);
		}

	}
?>