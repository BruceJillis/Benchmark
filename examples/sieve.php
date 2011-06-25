<pre>
<?php
	include '..\Benchmark.php';

	function eratosthenes($n) {
		$all=array();
		$prime=1;
		$i=3;
		while($i<=$n) {
			if(!in_array($i,$all)) {
				$prime+=1;
				$j=$i;
				while($j<=($n/$i)) {
					array_push($all,$i*$j);
					$j+=1;
				}
			}
			$i+=2;
		}
		return;
	}

	$bm = new Benchmark();

	$bm->start('SIEVE');
	eratosthenes(19000);
	$bm->stop('SIEVE');

	$bm->display();