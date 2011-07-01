<?php
	class Benchmark {
		private $sections = array();
		
		function __construct() {
			$this->sections['total'] = array();
			$this->sections['total'][] = array(
				'time_start' => microtime(true),
				'time_stop'  => null,
				'memory_start' => memory_get_usage(),
				'memory_stop' => null
			);
		}

		/**
		 * Returns a human readable time (ms, s, m)
		 */
		function units($type, $value) {
			switch($type) {
				case 'time':
					$value = floatval($value);
					if( $value <= 1 ) {
						$unit = 'ms';
						$amount = intval($value * 1000);
					} else if( $value <= 60 ) {
						$unit = 's';
						$amount = round($value, 2);
					} else if( $value <= 3600 ) {
						$unit = 'm';
						$amount = round($value / 60, 2);
					}
					return "$amount $unit";
				case 'bytes':
					$sizes = array(' B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
					for ($i = 0; abs($value) > 1024 && isset($sizes[$i+1]); ++$i)
					  $value /= 1024;
					return sprintf("%3.0f %2s", $value, $sizes[$i]);
			}
		}

		function start($section) {
			if( $section == 'total' )
				throw new Exception('a total measurement is already being made.');
			$this->calculated = false;
			if( !isset($this->sections[$section]) ) {
				$this->sections[$section] = array();
				$this->sections[$section][] = array(
					'time_start' => microtime(true),
					'time_stop'  => null,
					'memory_start' => memory_get_usage(),
					'memory_stop' => null
				);
			} else {
				$this->sections[$section][] = array(
					'time_start' => microtime(true),
					'time_stop'  => null,
					'memory_start' => memory_get_usage(),
					'memory_stop' => null
				);
			}
		}

		function stop($section) {
			$this->calculated = false;
			$this->sections[$section][count($this->sections[$section]) - 1]['time_stop'] = microtime(true);
			$this->sections[$section][count($this->sections[$section]) - 1]['memory_stop'] = memory_get_usage();
		}

		function calculate_median($arr) {
			sort($arr);
			$count = count($arr); //total numbers in array
			$middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
			if($count % 2) { // odd number, middle is the median
				$median = $arr[$middleval];
			} else { // even number, calculate avg of 2 medians
				$low = $arr[$middleval];
				$high = $arr[$middleval+1];
				$median = (($low+$high)/2);
			}
			return $median;
		}

		function avg_abs_deviation($arr, $center) {
			$sum = 0;
			$count = 0;
			foreach($arr as $value) {
				$count++;
				$sum += abs($value - $center);
			}
			return $sum / $count;
		}

		private function calculate($name) {
			$count = 0;
			$time = 0;
			$memory = 0;
			$time_deltas = array();
			$memory_deltas = array();
			foreach($this->sections[$name] as $index => $section) {
				$count += 1;
				$time += $section['time_stop'] - $section['time_start'];
				$time_deltas[] = $section['time_stop'] - $section['time_start'];
				$memory += $section['memory_stop'] - $section['memory_start'];
				$memory_deltas[] = $section['memory_stop'] - $section['memory_start'];
			}
			$median_time = $this->calculate_median($time_deltas);
			$median_time_deviation = $this->avg_abs_deviation($time_deltas, $median_time);
			$mean_time_deviation = $this->avg_abs_deviation($time_deltas, $time / $count);
			$median_memory = $this->calculate_median($memory_deltas);
			$median_memory_deviation = $this->avg_abs_deviation($memory_deltas, $median_memory);
			$this->sections[$name]['totals'] = array(
				'count' => $count,
				'mean_time' => $time / $count,
				'median_time' => $median_time,
				'median_time_deviation' => $median_time_deviation,
				'mean_time_deviation' => $mean_time_deviation,
				'mean_memory' => $memory / $count,
				'median_memory' => $median_memory,
				'median_memory_deviation' => $median_memory_deviation,
				'time' => $time,
				'memory' => $memory
			);
		}

		function display($section = null) {			
			if( !$this->calculated ) {
				$this->stop('total');
				foreach($this->sections as $name => $section) {
					$this->calculate($name);
				}
				function cmp($a, $b) {
					if($a['totals']['time'] == $b['totals']['time']) {
						return 0;
					}
					return ($a['totals']['time'] > $b['totals']['time']) ? -1 : 1;
				}
				uasort($this->sections, 'cmp');
				$this->calculated = true;
			}
			echo <<< EOT
<table cellpadding="2" cellspacing="2" style="font-family: courier;">
	<tr style="font-family: verdana;">
		<th>section</th>
		<th>#</th>
		<td>|</td>
		<th colspan="4">Time</th>
		<td>|</td>
		<th colspan="4">Memory</th>
	</tr>
	<tr style="font-family: verdana;">
		<th></th>
		<th></th>
		<td>|</td>
		<th>total</th>
		<th>mean</th>
		<th>median</th>
		<th>deviation</th>
		<td>|</td>
		<th>total</th>
		<th>mean</th>
		<th>median</th>
		<th>deviation</th>
	</tr>
EOT;
			if( count($this->sections) == 1 ) {
				echo "<tr><td colspan='2'></td><td>|</td><td colspan='8' align='center'><em>No Data</em></td></tr>";
			}
			foreach($this->sections as $name => $section) {
				if( $name == 'total' ) {
					continue;
				}
				echo <<< EOR
				<tr>
					<td>{$name}</td>
					<td>{$section['totals']['count']}</td>
					<td>|</td>
					<td align="center">{$this->units('time', $section['totals']['time'])}</td>
					<td align="right">{$this->units('time', $section['totals']['mean_time'])}</td>
					<td align="right">{$this->units('time', $section['totals']['median_time'])}</td>
					<td align="right">+/- {$this->units('time', $section['totals']['median_time_deviation'])}</td>
					<td>|</td>
					<td align="center">{$this->units('bytes', $section['totals']['memory'])}</td>
					<td align="right">{$this->units('bytes', $section['totals']['mean_memory'])}</td>
					<td align="right">{$this->units('bytes', $section['totals']['median_memory'])}</td>
					<td align="right">+/- {$this->units('bytes', $section['totals']['median_memory_deviation'])}</td>
				</tr>
EOR;
			}
			echo "<tr><td colspan='12'><hr/></td>";	
			echo "<tr><td colspan='2'>total</td>";
			echo "<td>|</td><td colspan='4'>{$this->units('time', $this->sections['total']['totals']['time'])}</td>";
			echo "<td>|</td><td colspan='4'>{$this->units('bytes', $this->sections['total']['totals']['memory'])}</td>";
			echo "</tr>";
			echo '</table>';
		}
	}