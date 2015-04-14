<?php
/**
* 
*/
class testDate
{
	public static function testFormat()
	{
		$time = time();
		var_dump((intval(date('Hi',$time))));
	}
}

testDate::testFormat();