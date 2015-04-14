<?php
/**
* 
*/
class testChar
{
	public static function testRN()
	{
		// echo strlen("\r\n");
		// echo strlen("中文");
		$str = '中文';
		echo $str;
		$log = fopen('test.log', 'w');
		$char = substr($str,0, 3);
		if ($char == '中') {
			echo 'yes';
		}else{
			echo 'no';
		}
		fwrite($log, $char);
		fclose($log);

	}
}
testChar::testRN();