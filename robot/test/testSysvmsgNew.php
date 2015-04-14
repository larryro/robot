<?php
/*
 * class msg
 * Use for communication between php and php;
 * Create at: 12:08 2012/10/31
 * Author: leixun(lein_urg@163.com)
 * version 1 - 14:01 2012/10/31
 */

class testSysvmsg{
	private static $id;
	private static $msg_id;
	private static $_serialize = true;

	/**
	 * @param $_id ID
	 */
	public static function msg($_id, $_serialize = true){
		self::$id = $_id;
		self::$msg_id = msg_get_queue ( $_id );
		self::$_serialize = $_serialize;
		if (self::$msg_id === false) {
			die(basename(__FILE__).'->'.__LINE__.': Unable to create message quee');
		}
	}

	/**
	 * @data data to send
	 * @type message type
	 */
	public static function send( $data, $type = 1, $blocking = false )
	{
		if (!msg_send (self::$msg_id, $type, $data, self::$_serialize, $blocking, $msg_err))
		{
			return "Msg not sent because $msg_err\n";
		}
		return true;
	}

	/**
	 * @param $type message type
	 * @param $maxsize The maximum size of message to be accepted,
	 */
	public static function receive($type = 1 , $maxsize = 1024 )
	{
		$rs = msg_receive ( self::$msg_id , $type ,	$type , $maxsize , $message , self::$_serialize, MSG_IPC_NOWAIT , $errorcode);
		if($rs)
			return $message;
		else
			return false;
	}

	public static function remove()
	{
		msg_remove_queue($this->msg_id);
	}

	public static function loop()
	{
		while (1) {
			sleep(1);
			echo self::receive();
			echo "\n";
		}
	}
}
testSysvmsg::msg(1);
testSysvmsg::send('my');
// testSysvmsg::loop();