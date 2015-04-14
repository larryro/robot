<?php
class robotReply{	
	private $connection;
	
	/**
    * 初始化
    * @return void
    */
	function __construct()
	{	
		// Config::instance();
		// 获取数据库连接实例
		$this->connection = Db::instance(Config::$config['db']['host'],
			Config::$config['db']['usr'],Config::$config['db']['password'],Config::$config['db']['robotReplyDb'],
			'utf8');
	}

	function __destruct(){
		$this->connection->close();
	}
	/**
    * 通过机器人对条件合适的消息进行回复
    * @return void
    */
	public function reply()
	{	
		// 获取当前时间
		$timestamp = time();

		// 获取超时未人工处理的消息
		$oldTime = $timestamp - Config::$config['robotReply']['DuringTime'];

		// 格式化
		$date = date('Y-m-d H:i:s',$timestamp);

		// 格式化
		$oldDate = date('Y-m-d H:i:s',$oldTime);

		// 判断当前是否为工作时间
		$workTime = $this->judgeTime(time());
		if ($workTime) {
			echo 'workTime'."\n";
			$sql = 'select messages.id,messages.pic,messages.state from messages inner join device on messages.device_id = device.id where (device.ver = 3 || device.ver = 4) and messages.update_time < \''.$oldDate.'\' and messages.state = 382';
		}
		else{
			echo 'not workTime'."\n";
			$sql = 'select messages.id,messages.pic,messages.state from messages inner join device on messages.device_id = device.id where (device.ver = 3 || device.ver = 4) and messages.update_time < \''.$date.'\' and messages.state = 382';
		}
		//获取需要回复的消息 
		$res = $this->connection->query($sql);

		// 当有需要处理的消息
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_assoc()) {
				// $duringTime = time() - $this->startTime;
				$id = $row['id'];
				$newState = $row['state'];

				if ($newState != 382) {
					continue;
				}
				$state = $this->getState($id);
				if ($state != 382) {
					continue;
				}
				$this->connection->query("update messages set state = 9 where id =$id");
				$img_url = Config::$config['robotReply']['baseUrl'].'/'.$row['pic'];
				// 注释
				$translateRes = Pic2Text::getresult($img_url);
				
				//如果成功获取到自动回复的结果,更新数据 
				if ($translateRes != 'error') {
					$this->connection->query("set names utf8");
					echo "update messages set reply = '$translateRes',state = 41 where id = $id";
					$this->connection->query("update messages set reply = '$translateRes',state = 41 where id = $id");

				}
				// 如果获取自动回复结果失败。则更新数据到失败状态
				else{
					$this->connection->query("update messages set state = 42 where id = $id");
				}
			}
		}
	}
	
	/**
    * 通过消息Id获取消息状态
    * @return int
    */
	private function getState($id)
	{
		$src = $this->connection->query("select state from messages where id = $id ");
		$stateArr = $src->fetch_assoc();
		return $stateArr['state'];
	}

	/**
    * 判断是否为上班时间
    * @param $timestamp
    * @return true or false
    */
	private function judgeTime($timestamp)
	{
		// 默认返回false
		$tag = false;
		
		// 获取上午上班时间
		$amWork = strtok(Config::$config['robotReply']['workTimeAM'],'-');

		// 获取上午上班开始时间
		$amStart = intval(str_ireplace(':',	'',	$amWork[0]));

		// 获取上午上班结束时间
		$amEnd = intval(str_ireplace(':', '', $amWork[1]));

		// 获取下午上班时间
		$pmWork = strtok(Config::$config['robotReply']['workTimePM'], '-');

		// 获取下午上班开始时间
		$pmStart = intval(str_ireplace(':',	'',	$pmWork[0]));

		// 获取下午上班结束时间
		$pmEnd = intval(str_ireplace(':', '', $pmWork[1]));
		
		// 格式化时间戳
		$time = intval(date('Hi',$timestamp));

		// 判断是否上班时间
		switch ($time) {
			case ($time >= $amStart)&&($time <= $amEnd):
				$tag = true;
				break;
			case ($time >= $pmStart)&&($time <= $pmEnd):
				$tag = true;
				break;
			default:
				$tag = false;
				break;
		}
		return $tag;
	}

}

function sig_handler($sig){
	global $child;
	switch ($sig) {
		case SIGCHLD:
			echo 'SIGHLD received'."\n";
			$child--;
			break;
		default:
			break;
	}
}



