<?php

class Git {
	private $cwd = null;
	private $log;

	public function __construct($cwd) {
		$this->cwd = $cwd;
		$this->log = Logger::getLogger("Git");
	}

	private function execute($args, $pipe_stdout, $cwd_override = null) {
		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);
		$cwd = $cwd_override != NULL ? $cwd_override : $this->cwd;
		$cmd = join(' ', array_map('escapeshellarg', $args));
		$proc = proc_open($cmd, $descriptorspec, $pipes, $cwd,
		                  array('LANG' => 'en_US.UTF-8'));

		if (!is_resource($proc)) {
			$errors = error_get_last();
			$this->log->error(
				"$cmd failed: {$errors['type']} {$errors['message']}");
			throw new Exception($errors['message']);
		}

		fclose($pipes[0]);
		if ($pipe_stdout) {
			$output = stream_get_contents($pipes[1]);
		} else {
			fpassthru($pipes[1]);
			$output = null;
		}
		$err = stream_get_contents($pipes[2]);

		$retval = proc_close($proc);
		if ($retval != 0) {
			$this->log->error("$cmd failed: $retval $output $err");
			throw new Exception($err);
		}

		return $output;
	}

	public function get($args, $cwd_override = null) {
		$args = array_merge(array("/usr/bin/git"), $args);
		return $this->execute($args, true, $cwd_override);
	}

	public function exec($args, $cwd_override = null) {
		$args = array_merge(array("/usr/bin/git"), $args);
		$this->execute($args, false, $cwd_override);
	}
}
