<?php
/**
* 
*/
class testWhile
{
	public static function testLoop()
	{
		$i = 0;
		while (1) {
			$i++;
			if ($i == 3) {
				continue;
			}
			echo $i."\n";
			sleep(1);
		}
	}
}
testWhile::testLoop();