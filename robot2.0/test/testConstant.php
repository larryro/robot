<?php
/**
* 
*/
class testConstant
{
	const APP = 1;
	public static function testShow()
	{
		
		echo self::APP;
		echo constant('self::'.'APP');
	}	
}
testConstant::testShow();