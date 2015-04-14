<?php
/**
* 
*/
class testString
{
	public static function testTrim()
	{
		// echo trim("sdwe-se","-");
		echo str_ireplace('-', '', 'sdwe-se');
	}
}
testString::testTrim();