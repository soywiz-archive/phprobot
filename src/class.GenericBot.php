<?php
	require_once(dirname(__FILE__) . '/system.php');

	//echo 'Waiting'; for ($n = 0; $n < 5; $n++) { echo '.'; sleep(1); } echo "\n";

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

	// Constantes utilizadas en el método "say" y en el evento "onSay"
	make_enum(
		'GB_SAY_TYPE_GLOBAL',
		'GB_SAY_TYPE_GUILD',
		'GB_SAY_TYPE_PARTY',
		'GB_SAY_TYPE_PUBLIC',
		'GB_SAY_TYPE_PRIVATE'
	);

	// Clase para bots genérica
	abstract class GenericBot {
		public $lists = array();
		public $player;

		public $trackPath;
		public $trackLast;

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

		protected $requestMoveTimeNext = -1;
// ----------------------------------------------------------------------------
// PROCESS
// ----------------------------------------------------------------------------

		final private function processMoving() {
			if (isset($this->lists['Entity']) && is_array($this->lists['Entity'])) {
				//echo sizeof($this->lists['Entity']) . "\n";

				foreach ($this->lists['Entity'] as $k => $v) {
					$e = &$this->lists['Entity'][$k];
					if ($e->moving) {
						$cp = floor(max(0, min(1, $e->from_time->dist() / $e->to_time_t)) * (sizeof($e->path) - 1));
						$_x = $e->x; $_y = $e->y;
						list($e->x, $e->y) = $e->path[$cp];

						// Si ha cambiado de posición "onMoving"
						//$e->visible = true;
						if ($_x != $e->x || $_y != $e->y) $this->onMoving($e);

						if ($cp >= sizeof($e->path) - 1) {
							$e->path = array();
							$e->setXY($e->x, $e->y);
							$e->moving = false;
							$this->onMoveEnd($e);
							$this->requestMoveTimeNext = -1;
						}
					} else {
						if (($e->map_x >= 0) || ($e->map_y >= 0)) {
							$e->x = $e->map_x;
							$e->y = $e->map_y;
						}

						if ($e === $this->player) {
							if (sizeof($this->trackPath) && (($this->requestMoveTimeNext < 0) || (time() >= $this->requestMoveTimeNext))) {
								if (($this->requestMoveTimeNext >= 0) && ((time() >= $this->requestMoveTimeNext) && isset($this->trackLast) && is_array($this->trackLast) && (sizeof($this->trackLast) == 2))) array_unshift($this->trackPath, $this->trackLast);

								$this->requestMoveTimeNext = time() + 6;
								list($x, $y) = $this->trackLast = array_shift($this->trackPath);
								if ($x && $y) {
									sendMove($this, $x, $y);
								}
							}
						}
					}
				}
			}
		}

// ----------------------------------------------------------------------------
// GENERAL
// ----------------------------------------------------------------------------

		function setBusy($flag) {
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

// ----------------------------------------------------------------------------
// PROCESS
// ----------------------------------------------------------------------------

		function process() {
			// Para dejar tiempo de proceso a la CPU y que el proceso no consuma
			// el 99% de los recursos del sistema.
			usleep(5000);

			switch ($this->step) {
				case GB_STEP_DISCONNECTED:        $this->onDisconnect();       break;
				case GB_STEP_MASTER_LOGIN:        $this->onMasterLogin();      break;
				case GB_STEP_MASTER_LOGIN_ERROR:  $this->onMasterLoginError(); break;
				case GB_STEP_CHARA_LOGIN:         $this->onCharaLogin();       break;
				case GB_STEP_CHARA_LOGIN_ERROR:   $this->onCharaLoginError();  break;
				case GB_STEP_CHARA_LOGIN_SUCCESS: $this->onCharaSelect();      break;

				case GB_STEP_MAP_PROCESS:
					// Proceso de movimiento
					$this->processMoving();

				case GB_STEP_MASTER_PROCESS: case GB_STEP_CHARA_PROCESS:
					while ($pk = $this->sock->extractPacket()) {
						list($p, $d) = $pk;
						$hex = str_pad(dechex($p), 4, '0', STR_PAD_LEFT);

						$f = "parse_recv_{$hex}";
						if (!function_exists($f)) {
							throw(new Exception("La función '{$f}' no está definida\n"));
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

		function sayTo($type, $text, &$to = NULL) {
			if (isset($to) && !($to instanceof Entity)) throw(new Exception(''));

			switch ($type) {
				case GB_SAY_TYPE_GLOBAL:  break;
				case GB_SAY_TYPE_GUILD:   break;
				case GB_SAY_TYPE_PARTY:   break;
				case GB_SAY_TYPE_PUBLIC:  break;
				case GB_SAY_TYPE_PRIVATE: break;
			}
		}

		function lookAt(Entity &$e) {
			$this->lookAtMap($e->x, $e->y);
		}

		function lookAtMap($x, $y) {
			$a = get_angle($this->player->x, $this->player->y, $x, $y);
			if (($z = (-90 - $a)) < 0) $z = 360 + $z;
			$pos = $z / 45;
			/* if (($m = $pos - (int)$pos) == 0) { $mz = 0;
			} else if ($m > 0.5) { $mz = 1;
			} else { $mz = 2; } */ $mz = 0;

			sendChangeView($this, $mz, round($pos));
		}

		function say($text, &$to = NULL) {
			if (isset($to) && !($to instanceof Entity)) throw(new Exception(''));

			sendSay($this, $text, $to);
		}

		function moveAt($x, $y, $add = false, $mov_dist = 12) {
			if (sizeof($path = $this->map->getPath($this->player->x, $this->player->y, $x, $y))) {
				if ($this->player->x != $x || $this->player->y != $y) {
					$t = floor(sizeof($path) / $mov_dist);
					$end = array();

					for ($n = 0; $n < $t; $n++) $end[] = $path[$n * $mov_dist];

					if ((sizeof($end) == 0) || ($end[sizeof($end) - 1] != $path[sizeof($path) - 1])) $end[] = $path[sizeof($path) - 1];

					while ($end[0][0] == $this->player->x && $end[0][1] == $this->player->y) array_shift($end);

					$this->trackPath = $end;
				} else {
					//echo "AT SAME POS\n";
				}
			} else {
				//echo "CANT MOVE\n";
			}
		}

		function moveNearTo($x, $y, $near = 2, $add = false) {
			$path = $this->map->getPath($x, $y, $this->player->x, $this->player->y) ;
			//$path = $this->map->getPath($this->player->x, $this->player->y, $x, $y);

			if (sizeof($path) > $near) {
				$pos = $path[$near];
			} else /*if (sizeof($path) > 0) {
				$pos = $path[0];
			} else */{
				return;
			}

			//echo ': (' . $x . ', ' . $y . ') - (' . $pos[0] . ', ' . $pos[1] . ")\n";

			$this->moveAt($pos[0], $pos[1], $add);
		}

		function moveNear(Entity &$entity, $near = 2, $add = false) {
			$this->moveNearTo($entity->x, $entity->y, $near, $add);
		}

// ----------------------------------------------------------------------------
// CALLBACKS
// ----------------------------------------------------------------------------

		// Login
		function onDisconnect()       { }
		function onMasterLogin()      { }
		function onMasterLoginError() { }
		function onCharaLogin()       { }
		function onMasterCharaError() { }
		function onCharaSelect()      { }
		function onMapStart()         { }

		// Interact
		function onSay($type, $text, &$from = NULL, $from_name = NULL) { }

		// Moving
		function onAppear(Entity &$e)       { }
		function onDisAppear(Entity &$e)    { }
		function onMoving(Entity &$e)       { }
		function onMoveStart(Entity &$e)    { }
		function onMoveEnd(Entity &$e)      { }

		// Update
		function onCharaInfoUpdate()        { }
		function onUpdateHP(Entity &$e)     { }
		function onUpdateSP(Entity &$e)     { }

		// Dealing
		function onDealRequest(Entity &$e)  { }
		function onDealStart(Entity &$e)    { }
		function onDealCancel(Entity &$e)   { }
		function onDealSuccess(Entity &$e)  { }
	}
?>