<?php

class Grf { }
// PHP_FFI 0.3 no permite obtener el puntero de una estructura para comprobar si enlaza
// a 0. Y creo que tampoco lee los enteros por puntero?

/*
	extension_loaded('php_ffi') or dl('php_ffi.dll') or die('FFI extension required');

if (!function_exists('make_enum')) { function make_enum() { $c = 0; foreach (func_get_args() as $v) if (!defined($v)) define($v, $c++); } }
make_enum('GE_BADARGS', 'GE_CANTOPEN', 'GE_INVALID', 'GE_CORRUPTED', 'GE_NOMEM', 'GE_NSUP', 'GE_NOTFOUND', 'GE_INDEX', 'GE_WRITE');

class grf {
	private $ffi;
	public  $error;
	private $handle;
	private $opened = false;

	function open($name) {
		if ($this->opened) $this->close();

		$this->error = false;
		if (file_exists($name) && is_readable($name)) {
			if (($this->handle = $this->ffi->grf_open($name, $this->error))) {
				$this->opened = true;
			} else {
				$this->opened = false;
			}
		} else {
			$this->opened = false;
		}
	}

	function find($find) {
		if ($this->opened) {
			$index = -1;
			$d = $this->ffi->grf_find($this->handle, $find, $index);
			echo 'Indice:' . $index . "\n";
			echo $d->real_len;
		}
	}

	function __construct($name) {
		$this->opened = false;

		$this->ffi = new ffi("
			struct GrfFile {
				char *name;
				uint32 type;
				uint32 compressed_len;
				uint32 compressed_len_aligned;
				uint32 real_len;
				uint32 pos;
				uint32 cycle;
			};

			struct Grf {
				char *filename;
				uint32 version;
				uint32 nfiles;
				struct GrfFile *files;
				int *f;
			};

			[lib='grf.dll']

			struct Grf *grf_open(char *fname, int *error);
			struct GrfFile *grf_find(struct Grf *grf, char *fname, uint32 *index);
			void *grf_get(struct Grf *grf, char *fname, uint32 *size, uint32 *error);
			void *grf_index_get(struct Grf *grf, uint32 index, uint32 *size, uint32 *error);
			int grf_extract(struct Grf *grf, char *fname, char *writeToFile, uint32 *error);
			int grf_index_extract(struct Grf *grf, uint32 index, char *writeToFile, uint32 *error);
			void grf_free(struct Grf *grf);
			char *grf_strerror(uint32 error);
		");
		// uint32 grf_open(char *fname, int *error);
		// struct Grf *grf_open(char *fname, int *error);

		$this->open($name);
	}

	function __destruct() {
		$this->close();
	}

	private function close() {
		if ($this->opened) {
			$this->opened = false;
			$this->ffi->grf_free($this->handle);
		}
	}

}

$grf = new grf('e:\\juegos\\ragnarok\\sprodata.grf');
//$grf->find('xml');
$grf->find('data\\mp3nametable.txt');
//$grf = new grf('e:\\juegos\\ragnarok\\sprodataa.grf');


//$error = 0;
//$g = $grf->grf_open('e:\\juegos\\ragnarok\\sprodata.grf', $error);
//$grf->free();
//echo $error;
*/
?>