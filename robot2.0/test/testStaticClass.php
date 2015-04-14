<?php
/**
* 
*/
class testStaticClass
{
	public static $instance;

	public static function show()
	{
		echo 2;
	}

	public static function instance()
	{
		self::$instance = new self();
		self::$instance->show();
		return self::$instance;
	}
}
testStaticClass::instance();