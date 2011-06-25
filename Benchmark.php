<?php
	class Benchmark {
		private $sections = array();

		/**
		 * Returns a human readable time (ms, s, m)
		 */
		function units($time) {
			$time = floatval($time);
			if( $time <= 1 ) {
				$unit = 'ms';
				$amount = intval($time * 1000);
			} else if( $time <= 60 ) {
				$unit = 's';
				$amount = round($time, 2);
			} else if( $time <= 3600 ) {
				$unit = 'm';
				$amount = round($time / 60, 2);
			}
			return array($amount, $unit);
		}
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
				list($amount, $unit) = $this->units($section['stop'] - $section['start']);
				printf("%s = %d %s\n", $name, $amount, $unit);
			}
		}
	}