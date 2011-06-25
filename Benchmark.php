<?php
	class Benchmark {
		private $sections = array();

		function start($section) {
			$this->section[$section] = array(
				'start' => microtime(true),
				'stop'  => null
			);
		}

		function stop($section) {
			$this->section[$section]['stop'] = microtime(true);
		}

		function display($section = null) {
			foreach($this->section as $name => $section) {
				printf("%s = %f seconds\n", $name, $section['stop'] - $section['start']);
			}
		}
	}