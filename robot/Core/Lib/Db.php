<?php
/**
* 
*/
class Db
{
	/**
     * 数据库实例
     * @var source
     */	
	public static $instance = array();

	/**
     * 获取数据库实例
     * @param $module,$host,$usr,$password,$db,$charset
     * @throws errMessage
     * @return source or false
     */
	public static function instance($host,$usr,$password,$db,$charset)
	{
		$Connection = new mysqli($host,$usr,$password,$db);
		// array_push(self::instance, var)
		if ($Connection->connect_error) {
			die("Connection failed: " . $Connection->connect_error);
			exit(0);
			return false;
		}else{
			$Connection->query('set names '.$charset);
			return $Connection;
		}

	}
	/**
     * 关闭数据库连接
     * @param none
     * @return none
     */
	public static function close($module)
	{
		// self::$instance[$module]->close();
	}
}