<?php
	define('LUNEA_ROOT', dirname(dirname(__FILE__)));

	define('LUNEA_DATA',     LUNEA_ROOT . '/data/');
	define('LUNEA_CONF',     LUNEA_ROOT . '/conf/');
	define('LUNEA_MAPS',     LUNEA_DATA . '/maps/');
	define('LUNEA_MAPS_IMG', LUNEA_DATA . '/maps_img/');
	define('LUNEA_SRC',      LUNEA_ROOT . '/src/');
	define('LUNEA_LIB',      LUNEA_ROOT . '/lib/');
	define('LUNEA_DOCS',     LUNEA_ROOT . '/docs/');

	function Import($import) {
		$import_path = explode('.', $import);
		if ($import_path[sizeof($import_path) - 1] == '*') {
			array_pop($import_path);
			foreach (scandir(LUNEA_LIB . implode('/', $import_path)) as $file) {
				if (strcasecmp(substr($file, -4, 4), '.php') == 0) {
					Import(implode('.', $import_path) . '.' . substr($file, 0, -4));
				}
			}

			return true;
		}

		$import_path = implode('/', $import_path);

		if (file_exists($file = LUNEA_LIB . $import_path . '.php')) {
			require_once($file);

			return true;
		} else {
			throw(new Exception('No se pudo acceder a la clase \'' . $import . '\' => \'' . $file . '\''));

			return false;
		}
	}

	$__declared_classes = get_declared_classes();

	function GetNewClasses() {
		$return = array();

		$classes = &$GLOBALS['__declared_classes'];

		foreach (get_declared_classes() as $class) {
			if (!array_search($class, $classes)) {
				if ($class != 'stdClass') $return[] = $class;
			}
		}

		return $return;
	}

	$__defined_functions = get_defined_functions();

	function GetNewFunctions() {
		$return = array();

		$functions = &$GLOBALS['__defined_functions'];

		foreach (get_defined_functions() as $function) {
			if (!array_search($function, $functions)) $return[] = $function;
		}

		return $return;
	}

	chdir(dirname($_SERVER['PHP_SELF']));
?>