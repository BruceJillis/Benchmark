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

	for($j = 0; $j < rand(1, 10); $j++) {
		for($i = 0; $i < rand(1, 150); $i++) {
			$bm->start('sieve' . $j);
			eratosthenes(1000 *  $j);
			$bm->stop('sieve' . $j);
		}
	}

	$bm->display();