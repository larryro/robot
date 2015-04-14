<?php
class robotAllocation{

	/**
     * 数据库连接
     * @var source
     */
	private $connection;

	/**
     * 数据库连接
     * @var source
     */
	private $pid;

	// memcache缓存
	private $mediaIdMem;

	// 标识用户在线
	const USER_ONLINE = 0;

	// Group 盲人用户组标识
	const MANG_REN_USER = 1;

	// Group 聋哑人用户组标识
	const LONG_YA_REN_USER = 2;

	// Group 家属用户组标识
	const FAMILY_USER = 3;

	// Group 义工用户组标识
	const YI_GONG_USER = 4;

	// Group app管理员用户组标识
	const APP_ADMIN_USER = 5;

	// Group app普通用户组标识
	const APP_NORMAL_USER = 6;

	// Messages消息已经被分配
	const MESSAGES_ALLOCATED = 90;

	// Reply消息被分配到微信用户
	const WEXIN_USER_ALLOCATED = 50;

	// Reply消息被分配到app用户
	const APP_ADMIN_ALLOCATED = 30;

	// Reply消息被分配到机器
	const ROBOT_USER_ALLOCATED = 40;

	// Reply消息被分配到WEB管理员用户
	const WEB_ADMIN_ALLOCATED = 60;

	// Reply APP用户消息已被推送
	const APP_ADMIN_PUSHED = 383;

	// Reply 微信用户消息已被推送
	const WEXIN_USER_PUSHED = 583;

	// Messages 微信图片消息上传成功
	const WEXIN_IMG_UPLOADED = 581;

	// Messages 微信录音消息上传成功
	const WEXIN_AUDIO_UPLOADED = 582;

	// Messages APP图片消息上传成功
	const APP_IMG_UPLOADED = 381;

	// Messages APP录音消息上传成功
	const APP_AUDIO_UPLOADED = 382;

	// 用户过期未回复被锁定
	const WEXIN_USER_LOCKED = 589;
	
	
	/**
    * 初始化
    * @return void
    */
	function __construct()
	{	
		// Config::instance();
		$this->pid = posix_getpid();

		// // 实例化Memcache对象
		// $this->mediaIdMem = new Memcache();
		// $this->mediaIdMem->connect('localhost',11211);
		
		// 获取数据库连接实例，初始化
		$this->connection = Db::instance(Config::get('db.host'),
			Config::get('db.usr'),Config::get('db.password'),
			Config::get('db.robotAllocationDb'),
			'utf8');

		// 初始化微信消息发送库
		WechatYG::ini(Config::get('WechatYG.appId'),Config::get('WechatYG.appSecret'),
			Config::get('WechatYG.textSendUrl'),Config::get('WechatYG.accessTokenUrl'),
			Config::get('WechatYG.fileUploadUrl'),Config::get('WechatYG.textSendUrl'),
			Config::get('WechatYG.mediaSendUrl'),Config::get('WechatYG.memHost'),
			Config::get('WechatYG.memPort'));

		// 初始化微信消息发送库
		WechatLY::ini(Config::get('WechatLY.appId'),Config::get('WechatLY.appSecret'),
			Config::get('WechatLY.textSendUrl'),Config::get('WechatLY.accessTokenUrl'),
			Config::get('WechatLY.fileUploadUrl'),Config::get('WechatLY.textSendUrl'),
			Config::get('WechatLY.mediaSendUrl'),Config::get('WechatLY.memHost'),
			Config::get('WechatLY.memPort'));

		// 初始化融云消息发送库
		Rongyun::ini(Config::get('RongYun.appKey'),Config::get('RongYun.appSecret'),
			Config::get('RongYun.format'),Config::get('RongYun.serverUrl'));
	}

	public function __destruct()
	{
		$this->connection->close();
		// $this->mediaIdMem->close();
	}


	/**
	 *  对消息进行分配
	 *	@param $group
	 * 	@return array or false
	 */		
	public function allocate($type = 'allocate')
	{
		// 获取要分配的消息
		$arrayToAllocate = $this->getMessagesAllocation($type);
		
		// 如果返回的待分配的西欧阿西为空，则提交事务，返回False
		if (!$arrayToAllocate) {
			// 提交事务
			// $this->connection->query('commit');
			return false;
		}

		// 遍历消息数组分配消息
		foreach ($arrayToAllocate as $key => $value) {
			foreach ($value as $sekey => $sevalue) {
				$groupId = $this->getGroup($sevalue);
				if ($groupId == self::YI_GONG_USER || $groupId == self::LONG_YA_REN_USER){
					$state = self::WEXIN_USER_ALLOCATED;
				}
				elseif($groupId == self::APP_ADMIN_USER){
					$state = self::APP_ADMIN_ALLOCATED;
				}
				else{
					$state = self::ROBOT_USER_ALLOCATED;
				}
				$sql = "INSERT INTO reply(`messages_id`,`users_id`,`state`) 
				VALUES ($key,$sevalue,$state)";
				$sqlUpdate = 'update messages set state = '.self::MESSAGES_ALLOCATED.',
				 allocate_times = 1 where id = '.$key;
				echo date('Y-m-d H:i:s',time()).'  allocate finish'."\n";
				$this->connection->query($sql);
				$this->connection->query($sqlUpdate);
				
			}
		}
		// 提交事务
		// $this->connection->query('commit');
	}

	/**
	 *  对消息进行重新分配
	 *	@param $group
	 * 	@return array or false
	 */		
	public function reallocate()
	{
		$this->allocate('reallocate');
	}

	public function insertMessages()
	{
		for ($i=0; $i < 100; $i++) { 
			$sql = "insert into messages ";
		}
	}

	/**
	 * 获取在线可推送消息用户
	 *	@param $group
	 * 	@return array or false
	 */		
	private function getUsrOnline()
	{
		// 活跃时间段初始化
		// $oldTime = date('Y-m-d H:i:s',time() - intval(Config::$config[
		// 	'robotAllocation']['activeTime']));
		$oldTime = date('Y-m-d',time()).' 00:00:00';
		
		//根据活跃时间和用户组获取用户Id
		$sql = "SELECT id FROM users WHERE last_login >'$oldTime' AND (group_id = ";

		// // 获取可以被分配消息的用户组列表
		$group=array(self::APP_ADMIN_USER,self::LONG_YA_REN_USER,self::YI_GONG_USER,
			self::WEB_ADMIN_ALLOCATED);

		// 获取可以被分配消息的用户组列表
		// $group=array(self::APP_ADMIN_USER);

		// 能回复信息的用户Id列表
		$UsersId = array();

		//查询可以回复的用户集,上限为2000个
		foreach ($group as $key => $value) {
			if ($key == 0) {
				$sql .= $value;
			}else{
				$sql .= ' ||group_id='.$value;
			}	
		}
		$sql.=') AND state = '.self::USER_ONLINE.' limit 0,2000';
		
		// // // 新的sql查询语句
		// $sql = 'SELECT id FROM users WHERE (group_id ='.self::YI_GONG_USER.' AND state=
		// 	'.self::USER_ONLINE.')||group_id = '.self::APP_ADMIN_USER.' limit 0,2000';
		// 查询
		$res = $this->connection->query($sql);

		echo $sql."\n";
		
		// 如果查询出错，返回
		if (!$res) {
			echo 'connection error'."\n";
			return $res;
		}

		// 获取用户ID数组
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_assoc()) {
				array_push($UsersId, $row['id']);
			}
			echo date('Y-m-d H:i:s',time()).' usersOnline'. "\n";
			// var_dump($UsersId);
			return $UsersId;
		}
		// 返回FALSE
		else{
			echo date('Y-m-d H:i:s',time()).'  no users Online return false'. "\n";
			// echo 'false';
			return false;
		}
	}

	
	/**
	 *  获取需要被回复的消息
	 *	@param $group
	 * 	@return array or false
	 */		
	private function getMessagesToReply($type='allocate')
	{
		$arr = array();
		if ($type == 'allocate') {
			$oldTime = time() - intval(Config::get('robotAllocation.finishTime'));
			$reallocateTime = time() - 120;
			
			// 查询合适的消息
			// 图片不能为空
			// 消息的被转发次数必须小于20次
			$sql = 'SELECT id FROM messages WHERE (state = '.self::WEXIN_AUDIO_UPLOADED.' || 
			state = '.self::APP_AUDIO_UPLOADED.' || (state = '.self::APP_IMG_UPLOADED.' and  
			upload_time < '.$oldTime.') || (state = '.self::WEXIN_IMG_UPLOADED.' and upload_time <' 
			.$oldTime.') ||(state = '.self::MESSAGES_ALLOCATED.' AND upload_time < '.$reallocateTime.'
			)) AND pic != \'\' AND allocate_times < 20 order by upload_time asc limit 0,500';
			
			// echo 'allocate'."\n";
			// echo 
			echo 'getMessagesToReply'. "\n";
			echo $sql."\n";
			
			// $this->connection->query('start transaction');
		    
		    $res = $this->connection->query($sql);
		    
		    if (!$res) {
			return false;
			}
			
			if ($res->num_rows > 0) {
				while ($row = $res->fetch_assoc()) {
					array_push($arr, $row['id']);
				}
				// echo date('Y-m-d H:i:s',time()). 'getMessagesToReply'."\n";
				// var_dump($arr);
				return $arr;
			}	
		}
		else
		{
			$oldTime = time() - 10*(int)Config::get('robotAllocation.outtime');

			$oldDate = date('Y-m-d H:i:s',time() - (int)Config::get('robotAllocation.outtime')); 
			// echo date('Y-m-d H:i:s',time()).' robotAllocation Outtime: '.Config::get('robotAllocation.outtime')."\n";
			// 查询已经推送但是没有回答的消息
			// $sql = "SELECT id FROM messages WHERE (state = 50 || state = 60)
			//  AND upload_time < $oldTime for UPDATE";
		    $sql = 'SELECT reply.id,reply.state,reply.users_id FROM reply INNER JOIN messages  
		    on reply.messages_id = messages.id WHERE (reply.state = '.self::WEXIN_USER_PUSHED 
		    	.' ||reply.state = '.self::WEXIN_USER_ALLOCATED.') AND reply.update_time < '."'$oldDate' limit 0,100";
			
			$sqlUpdate = 'UPDATE messages set state = 52 where state = 582 and upload_time <'.$oldTime;
		    $this->connection->query($sqlUpdate);
		    echo date('Y-m-d H:i:s',time()).' get messages to reallocate'."\n";
		    echo $sql."\n";
		    // //查询过期的分配了但是没有推送消息的用户 
		    // $sqlUpdate =  'SELECT id FROM messages WHERE (state = '.self::MESSAGES_ALLOCATED 
		    // 	' AND upload_time < $oldTime ';

		    // $this->connection->query('start transaction');
		   
		    $res = $this->connection->query($sql);
		    
		    if (!$res) {
			return false;
			}
			// 告诉没有回复消息的用户消息已过期
			if ($res->num_rows > 0) {
				while ($row = $res->fetch_assoc()) {
					// array_push($arr, $row['id']);
					
					// 用户过期后发送消息提醒
					$this->usersPunish($row['id'], $row['state'],$row['users_id']);
				}
				return false;
			}
		}
	}

	/**
	 *  获取消息的分配关系
	 *	@param $group
	 * 	@return array or false
	 */		
	public function getMessagesAllocation($type='allocate')
	{
		// 获取可分配的用户数组，上限为2000条
		$replyerToAllocate = $this->getUsrOnline();

		// 获取可以回复的消息数组,上限为500条
		$messagesToAllocate = $this->getMessagesToReply($type);

		
		// 如果没有可回复的用户，返回False
		if (empty($replyerToAllocate)) {
			echo 'no replyer return false'."\n";
			sleep(1);
			return 0;
		}

		if (empty($messagesToAllocate)) {
			echo 'no messages return false'."\n";
			sleep(1);
			return 0;
		}
		
		// 计算回复者的人数
		$replyerCount = count($replyerToAllocate);

		// 计算消息数量
		$messagesCount = count($messagesToAllocate);

		// 计算消息数和用户数的差值
		$count = $messagesCount - $replyerCount;

		echo "replyerToAllocate\n";
		// var_dump($replyerToAllocate);
		switch ($count) {
			// 消息数目比用户数目少，每个用户分配多个消息
			case $count < 0:
				// 取绝对值
				$range = abs($count);

				// 每个消息能够分配到的用户基数
				$n = floor($replyerCount / $messagesCount);

				if ($n >= 10) {
					$n = 10;
				}

				echo "num: $n\n";

				// 反转消息数组
				$arrayToReturn = array_flip($messagesToAllocate);

				// 对每条消息进行用户分配
				foreach ($arrayToReturn as $key => &$value) {
					// 初始化用户数组
					$value = array();

					// 计算处理开始时间
					$startTime = time();

					// 每条消息随机分配n个用户
					for ($i=0; $i < $n; $i++) { 
						do {
							// 如果处理的时间超过1秒，跳过
							if ((time() - $startTime) > 1) {
								continue;
							}
							$key = rand(0,$replyerCount -1);
						} while (!array_key_exists($key, $replyerToAllocate));
						
						array_push($value,$replyerToAllocate[$key]);
						$this->connection->query('update users set state = 1
						 where id ='.$replyerToAllocate[$key]);
						
						unset($replyerToAllocate[$key]);
					}

					// 获取每条消息绑定的亲属
					$relative = $this->getRelative($value);
					
					// 向每条消息对应的用户中插入相应亲属的用户Id
					if ($relative) {
						foreach ($relative as $relativeUser) {
							array_push($value, $relativeUser);
						}
					}
				}	
				break;
			case $count == 0:
				$arrayToReturn = array_flip($messagesToAllocate);
				
				// 将消息和用户进行一一对应
				foreach ($arrayToReturn as $key => &$value) {
					$user = array_pop($replyerToAllocate);
					$value = array($user);
					$this->connection->query('update users set state = 1
						 where id ='.$user);

					// 获取每条消息绑定的亲属
					$relative = $this->getRelative($value);
					
					// 向每条消息对应的用户中插入相应亲属的用户Id
					if ($relative) {
						foreach ($relative as $relativeUser) {
							array_push($value, $relativeUser);
						}
					}
				}
				break;
			case $count > 0:
				//取数组中排在前面的消息发送
				$messages = array_chunk($messagesToAllocate, $replyerCount);
				
				// 将消息和用户进行一一对应
				$arrayToReturn = array_flip($messages[0]);

				foreach ($arrayToReturn as $key => &$value) {
					$user = array_pop($replyerToAllocate);
					$value = array($user);
					$this->connection->query('update users set state = 1
						 where id ='.$user);
					
					// 获取每条消息绑定的亲属
					$relative = $this->getRelative($value);
				
					// 向每条消息对应的用户中插入相应亲属的用户Id
					if ($relative) {
						foreach ($relative as $relativeUser) {
							array_push($value, $relativeUser);
						}
					}
				}
				break;	
			default:
				break;
		}
		echo date('Y-m-d H:i:s',time()).' getMessagesAllocation: '. "\n";
		// var_dump($arrayToReturn);
		return $arrayToReturn;
	}

	/**
	 *  惩罚没有及时回复消息的用户
	 *	@param $messagesId
	 * 	@return true or false
	 */		
	private function usersPunish($replyId, $state,$users_id)
	{	
		if ($state == self::WEXIN_USER_ALLOCATED) {
			$sqlMessages = 'UPDATE reply SET state = 52 WHERE id
			='.$replyId;
			echo 'clear old allocated'."\n";
			echo $sqlMessages;
			$this->connection->query($sqlMessages);
			return true;
		}
		elseif ($state == self::WEXIN_USER_PUSHED) {
			$openId = $this->getOpenId($users_id);
			$value = array('pic' => '', 'audio' => '', 
				'audio_text' => '给您推送的问题已过期');
			echo 'openId: '.$openId."\n";
			WechatYG::sendMsg($openId, '您应该在忙吧，为了不打扰您，您再次点/:heart后再给您发问题哦。');

			// 标识用户回复失败
			$sqlMessages = 'UPDATE reply SET state = '.self::WEXIN_USER_LOCKED.' WHERE id 
			='.$replyId;
			$this->connection->query($sqlMessages);
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 *  获取分组信息
	 *	@param $id
	 * 	@return true or false
	 */	
	private function getGroup($id)
	{
		$sql = 'SELECT group_id FROM users WHERE id = '.$id;
		$res = $this->connection->query($sql);
		if (!$res) {
			return false;
		}
		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			return $row['group_id'];
		}
		return false;
	}

		
	/**
	 *  向接受消息的盲人或者义工推送消息
	 *	@param $messages  用户消息数组
	 * 	@return true || false;
	 */
	public function send($messages)
	{
		// $access_token = weChat::getAccessToken();
		echo date('Y-m-d H:i:s',time())." messages send"."\n";
		// var_dump($messages);
		$this->updatePid($messages);

		// 依次发送每条消息
		foreach ($messages as $key => $value) {
			$openId = $this->getOpenId($value['users_id']);
			if (!$openId) {
				return false;
			}

			//获取用户组别 
			$groupId = $this->getGroup($value['users_id']);

			// 根据消息ID获取消息发送者的用户ID
			$userId = $this->getUserId($value['messages_id']);
			// $openId = 'oDAkQs8C6NUiJNx3jErNUIdRfxmM';
			
			// 通过用户组别调用不同的接口发送消息
			switch ($groupId) {
				// 用户为APP管理员，调用APP消息发送接口发送
				case self::APP_ADMIN_USER:
					$content = array('imgURL' => '/'.$value['pic'],
						'voiURL' => '/'.$value['audio'], 'replyId' => $value['id']);
					$arr = array('content' => $content, 'extra' => 'slfy2014.4');
					
					// 调用融云接口向相关用户发送消息
					$ret = Rongyun::messagePublish($userId, array($value['users_id']),
					 'RC:TxtMsg',json_encode($arr));
					
					// 如果发送不成功跳过此次发送
					if (!$ret) {
						continue;
					}else{
						echo 'RongYun sended to'.$value['users_id'].' '.$value['id'].' '.$ret."\n";
					}
					break;
				// 用户为微信管理用户，调用微信用户发送接口发送
				case self::YI_GONG_USER:
					if (!$this->contentSend($openId, $value, 'WechatYG',$value['messages_id'])) {
						continue;
					}

					// 告诉义工这是需要回复的问题
					WechatYG::sendMsg($openId, '这是盲人朋友的问题，请您尽快帮助他哦。');
					break;
				case self::LONG_YA_REN_USER:
					if (!$this->contentSend($openId, $value, 'WechatLY',$value['messages_id'] )) {
						continue;
					}
					break;
				default:
					continue;
					break;
			}

			if ($groupId == self::APP_ADMIN_USER) {
				// 标识消息状态，用户正在回复中
				$sqlUpdateReply = 'UPDATE reply SET state = '.self::APP_ADMIN_PUSHED.' where id 
				='.$value['id'];
			}
			elseif ($groupId == self::YI_GONG_USER) {
				// 标识消息状态，用户正在回复中
				$sqlUpdateReply = 'UPDATE reply SET state = '.self::WEXIN_USER_PUSHED.' where id 
				='.$value['id'];
			}
			else{
				// 标识消息状态，用户正在回复中
				$sqlUpdateReply = 'UPDATE reply SET state = '.self::WEXIN_USER_PUSHED.' where id 
				='.$value['id'];
			}
			
			$sqlUpdateMessages = 'UPDATE messages SET allocate_times = allocate_times + 1 WHERE
			 id = '.$value['messages_id'];
			echo date('Y-m-d H:i:s', time())."\n messages pushed";
			echo $sqlUpdateMessages."\n";
			$res = $this->connection->query($sqlUpdateReply);
			$this->connection->query($sqlUpdateMessages);
		}
		return true;
	}

	/**
	 *  获取亲属
	 *	@param $messages_id
	 * 	@return array;
	 */
	private function getRelative($messages_id)
	{
		// 初始化需要发送家属数组
		$familyToSend = array();

		// //根据消息Id查询相应用户的Id 
		$users_id_Obj = $this->connection->query('select users_id from messages
		 where id ='.(int)$messages_id);

		if ($users_id_Obj->num_rows > 0) {
			$row = $users_id_Obj->fetch_assoc();
			$users_id = $row['users_id'];
		}
		// $user_id = $this->getOne($id,$table)

		$sql = 'SELECT users_relate.users_id,users.name FROM users_relate 
		INNER JOIN users ON users.id = users_relate.users_id WHERE 
		users_relate.users_binded_id  = '.$users_id;
		
		echo 'select relative'. "\n";
		echo $sql;

		// 根据用户Id查询相关消息的Id
		$family = $this->connection->query($sql);
		
		if ($family->num_rows > 0) {
			while ($row = $family->fetch_assoc()) {
				array_push($familyToSend, $row);
			}
			return $familyToSend;
		}else{
			return false;
		}
	}
	/**
	 *  获取需要推送的消息列表
	 *	@param none
	 * 	@return empty false | array
	 */	
	public function messagesToSend()
	{
		echo date('Y-m-d H:i:s', time()).' 2'."\n";
		// 初始化信息发送数组
		$arr = array();
		echo date('Y-m-d H:i:s', time()).' 3'."\n";
		$sql = "SELECT reply.id,reply.messages_id,messages.pic,messages.audio_text, 
		messages.audio, reply.users_id FROM  `messages` 
		INNER JOIN `reply` ON messages.id = reply.messages_id
		WHERE (reply.state =50 || reply.state =30) AND reply.pid = 0 limit 0,100";
		echo date('Y-m-d H:i:s',time())."  $sql \n";
		$res = $this->connection->query($sql);
		echo date('Y-m-d H:i:s', time()).' 4'."\n";
		if (!$res) {
			echo 'get messagesTosend err connection error'."\n";
			return $res;
		}
		if ($res->num_rows > 0) {
			echo date('Y-m-d H:i:s', time()).' 5'."\n";
			while ($row = $res->fetch_assoc()) {
				array_push($arr, $row);
			}
			echo date('Y-m-d H:i:s', time()).' 6'."\n";

			echo date('Y-m-d H:i:s', time()).' messages to send';
			// var_dump($arr);
			return $arr;
		}
		else{
			echo date('Y-m-d H:i:s', time()).' 7'."\n";
			echo 'no messages to send'."\n";
			return false;
		}

	}
		
	/**
	 *  获取需要推送消息的用户的微信OpenId 
	 *	@param $users_id
	 * 	@return string
	 */	
	private function getOpenId($users_id)
	{
		$sql = 'select name from users where id ='.$users_id;
		$res = $this->connection->query($sql);
		if (!$res) {
			return false;
		}
		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			$name = $row['name'];
			return substr($name, 3, strlen($name) - 3);
		}
	}
		
	/**
     * 解析配置文件
     * @param string $config_file
     * @throws Exception
     */
    protected function parseFile($config_file)
    {
        $config = parse_ini_file($config_file, true);
        if (!is_array($config) || empty($config))
        {
            echo('Invalid configuration format'."\n");
            exit();
        }
        return $config;
    }

	/**
     * 消息发送接口
     * @param string $id, $value, $type
     * 说明 图片为必达项目。没到达则返回失败
     * @throws Exception
     */
    private function contentSend($id, $value, $type, $messageId)
    {
    	$baseUrl = Config::get('robotAllocation.srcBaseUrl');
    	
    	// 必须要有图片消息
    	if ($value['pic'] != '') {
    			//确保图片必达 
				if(!$type::sendMsg($id, $baseUrl.$value['pic'], 'image', $messageId))
					return false;
				echo date('Y-m-d H:i:s').' image messages sended to '.$id."\n";
			}
		else{
			return false;
		}
		// 音频如果第一次发送失败，则重新发送一次。第二次如果继续发送失败，则继续
		if ($value['audio'] != '') {
			if (!$type::sendMsg($id, $baseUrl.$value['audio'], 'voice', $messageId)) {
				$type::sendMsg($id, $baseUrl.$value['audio'], 'voice',$messageId);
				echo date('Y-m-d H:i:s').' audio messages again sended to '.$id."\n";
			}
			echo date('Y-m-d H:i:s').' audio messages sended to '.$id."\n";
		}
		if ($value['audio_text'] != '') {
			$type::sendMsg($id, $value['audio_text']);
			echo date('Y-m-d H:i:s').' audio_text messages sended to '.$id."\n";
		}
		return true;
    }


	/**
     * 消息pid更新
     * @param $messages 消息数组
     * 说明 图片为必达项目。没到达则返回失败
     * @throws Exception
     */
    private function updatePid($messages)
    {
    	// 获取当前进程Id
    	$pid = getmypid();

    	//更新数据库状态 
    	foreach ($messages as $value) {
    		$sql = 'update reply set pid ='.$pid.' where id = '.$value['id'];
    		echo 'updatePid'."\n";
    		echo $sql."\n";
    		$this->connection->query($sql);
    	}
    }

	/**
     * 根据消息Id获取消息发送者的用户Id
     * @param $messagesId 消息Id
     * @return ID
     */
    private function getUserId($messagesId)
    {
    	$sql = 'select users_id from messages where id ='.$messagesId;
    	echo 'getUserId '."\n";
    	echo $sql."\n";
    	$res = $this->connection->query($sql);
    	if (!$res) {
    		return false;
    	}
    	if ($res->num_rows > 0) {
    		$row = $res->fetch_assoc();
    		return $row['users_id'];
    	}else{
    		return false;
    	}
    }
}