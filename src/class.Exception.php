<?php
	if (!class_exists('Exception')) {
		class Exception {
			function __construct(string $message = NULL, int $code = 0) {
				if (func_num_args()) $this->message = $message;
				$this->code = $code;
				$this->file = __FILE__; // of throw clause
				$this->line = __LINE__; // of throw clause
				$this->trace = debug_backtrace();
				$this->string = StringFormat($this);
			}

			protected $message = 'Unknown exception';  // exception message
			protected $code = 0; // user defined exception code
			protected $file;    // source filename of exception
			protected $line;    // source line of exception

			private $trace;      // backtrace of exception
			private $string;    // internal only!!

			final function getMessage() {
				return $this->message;
			}

			final function getCode() {
				return $this->code;
			}

			final function getFile() {
				return $this->file;
			}

			final function getTrace() {
				return $this->trace;
			}

			final function getTraceAsString() {
				return self::TraceFormat($this);
			}

			function _toString() {
				return $this->string;
			}

			static private function StringFormat(Exception $exception) {
				// ... a function not available in PHP scripts
				// that returns all relevant information as a string
			}

			static private function TraceFormat(Exception $exception) {
				// ... a function not available in PHP scripts
				// that returns the backtrace as a string
			}
		}
	}
?>