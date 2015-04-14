<?php
define('robot_Root_Dir', realpath(__DIR__."/../")."/"); 
require_once robot_Root_Dir . 'Core/Lib/Config.php';
require_once robot_Root_Dir . 'Core/Lib/Db.php';
require_once robot_Root_Dir . 'Core/Lib/Pic2Text.php';
require_once robot_Root_Dir . 'Core/Lib/WechatYG.php';
require_once robot_Root_Dir . 'Core/Lib/WechatLY.php';
require_once robot_Root_Dir . 'Core/Lib/RongYun.php';
require_once robot_Root_Dir . 'Core/robotAllocation.php';
require_once robot_Root_Dir . 'Core/robotReply.php';

Config::instance();
/**
* 主进程
*/
class Master
{
	// 总的消息数量
	private static $messagesNum;

	// 当前已经开的消息分发的进程数量
	private static $currentProcessNum = 0;

	// 还可以新开的进程数量
	private static $processToOpen = 0;

	// 最多可以开的消息发送进程数量
	private static $maxSendNum = 0;

	// 每个进程最多发送消息数量
	private static $maxMessagesNum = 0;



	// 共享消息队列ID
	private static $msgId = 0;
	public static function notice($msg)
	{
		echo $msg."\n";
	}

	public static function startRobotReply()
	{
		self::notice('robotReply is starting');
		// self::installSignal();
		$robotReply = new robotReply;
		$child = 0;
		$max = Config::$config['robotReply']['robotWorker'];
		while (true) {
			$child++;
			$pid = pcntl_fork();
			if ($pid) {
				if ($child >= $max) {
					pcntl_wait($status);
				}
			}
			else{
				$robotReply = new robotReply;
				while (true) {
					$robotReply->reply();
					if (posix_getppid() == 1) {
						posix_kill(posix_getpid(),SIGKILL);
					}
					sleep(2);
				}
				unset($robotReply);
			}
		}
	}

	public static function startRobotAllocation()
	{
		self::notice('robotAllocation is starting');
		self::installSignal();
		self::SignalIgnore();
		$child = 0;
		var_dump(Config::$config);
		$max = Config::$config['robotAllocation']['robotWorker'];
		// self::installSignal();
		

		
		// 子进程消息数组
		$arrList = '';

		while (true) {
			echo date('Y-m-d H:i:s',time()).'new loop'."\n";
			$robotAllocation = new robotAllocation;
			
			// 消息分配
			$robotAllocation->allocate();

			echo date('Y-m-d H:i:s', time()).' 1'."\n";

			// 消息重新分配
			$robotAllocation->reallocate();
			
			// 消息发送
			// $robotAllocation->send();
			$messages = $robotAllocation->messagesToSend();

			unset($robotAllocation);

			echo date('Y-m-d H:i:s', time()).'currentProcessNum'."\n";
			echo date('Y-m-d H:i:s', time()).': '.self::$currentProcessNum."\n";
			if (!$messages) {
				sleep(1);
				// continue;
			}
			else{
				echo date('Y-m-d H:i:s', time()).' 8'."\n";
				// 总的消息数量
				$messagesNum = count($messages);

				echo date('Y-m-d H:i:s', time()).' 9'."\n";


				// 最多可以开的消息发送进程数量
				self::$maxSendNum = (int)Config::get('robot.maxSendNum');

				echo date('Y-m-d H:i:s', time()).' 10'."\n";


				self::$maxSendNum = 50;

				echo date('Y-m-d H:i:s', time()).' 11'."\n";

				// 每个进程最多发送消息数量
				self::$maxMessagesNum = (int)Config::get('robot.maxMessagesNum');
				
				echo date('Y-m-d H:i:s', time()).' 12'."\n";

				self::$maxMessagesNum = 10;

				echo date('Y-m-d H:i:s', time()).' 13'."\n";

				// 消息分配需要的进程数量
				$needProcessNum = intval($messagesNum / self::$maxMessagesNum) + 1;

				echo date('Y-m-d H:i:s', time()).' 14'."\n";


				// 还可以开的进程数目
				$processToOpen = self::$maxSendNum - self::$currentProcessNum;
				echo date('Y-m-d H:i:s', time()).' 15'."\n";


				// 如果还可以开的线程数为0，将程序挂起
				if ($processToOpen == 0) {

					echo date('Y-m-d H:i:s', time()).' 16'."\n";
					pcntl_wait($status);
					pcntl_signal_dispatch();
					echo date('Y-m-d H:i:s', time()).' 17'."\n";
					// continue;
				}

				// 需要的进程数多于还可以打开的进程数
				if ($needProcessNum > $processToOpen) {
					echo date('Y-m-d H:i:s', time()).' 18'."\n";
					
					// 将消息按照单进程最大接收消息数目分配到各个系统上
					$arrList = array_chunk($messages, self::$maxMessagesNum);
					
					echo date('Y-m-d H:i:s', time()).' 19'."\n";
					for ($i= 0; $i < $processToOpen; $i++) { 
						
						$pid = pcntl_fork();
						
						if ($pid > 0) {
							echo date('Y-m-d H:i:s', time()).' 20'."\n";
							self::$currentProcessNum++;
							// sleep(1);
							// continue;
						}elseif ($pid == -1) {
							echo date('Y-m-d H:i:s', time()).' 21'."\n";
							return false;
						}else{
							echo date('Y-m-d H:i:s', time()).' 22'."\n";
							$robotSend = new robotAllocation();
							// 发送消息
							// $robotSend->send($value);
							$robotSend->send($arrList[$i]);
							unset($robotSend);
							//结束进程 
							exit(0);
						}
					}
				}
				// 需要的进程数比还可以打开的进程数目少
				else{
					echo date('Y-m-d H:i:s', time()).' 23'."\n";
					// 将消息按照每个进程能够处理最大的消息数目进行分块
					$arrList = array_chunk($messages, self::$maxMessagesNum);

					echo date('Y-m-d H:i:s', time()).' 24'."\n";

					// 遍历每个消息块，开子进程对消息进行发送
					foreach ($arrList as $value) {
						$pid = pcntl_fork();
						if ($pid > 0) {
							echo date('Y-m-d H:i:s', time()).' 25'."\n";
							self::$currentProcessNum++;
							// sleep(1);
							// continue;
						}elseif ($pid == -1) {
							echo date('Y-m-d H:i:s', time()).' 26'."\n";
							return false;
						}else{
							echo date('Y-m-d H:i:s', time()).' 27'."\n";
							$robotSend = new robotAllocation();
							// 发送消息
							$robotSend->send($value);
							unset($robotSend);
							//结束进程 
							exit(0);
						}
					}
				}
			}
			echo date('Y-m-d H:i:s', time()).' 28'."\n";
			pcntl_signal_dispatch();
			echo date('Y-m-d H:i:s', time()).' 29'."\n";
			echo 'sleep 1 second'."\n";
			sleep(1);
		}
		echo date('Y-m-d H:i:s', time()).' 30'."\n";
		unset($robotReply);
	}

	public static function run($type=1,$maxsize = 1024,$_serialize=true)
	{
		self::$msgId = msg_get_queue((int)Config::$config['robotReply']['IpcKey']);
		// 告诉系统内核父进程不关心子进程，结束后自动回收子进程
		pcntl_signal(SIGCHLD, SIG_IGN);
		pcntl_signal_dispatch();
		while (true) {
			$msgId = self::$msgId;
			$rs = msg_receive ( $msgId , $type ,$type ,$maxsize,
			 $message , $_serialize, MSG_IPC_NOWAIT , $errorcode);
			if ($rs) {
				$pid = pcntl_fork();
				if ($pid) {
					// exit();
				}else{
					$robotReply = new robotReply;
					$robotReply->reply();
					unset($robotReply);
					posix_kill(getmypid(), SIGTERM);
					exit(0);
				}		
			}
		}
	}

	protected static function sigHandle($signal)	
	{
		switch ($signal) {
			case SIGCHLD:
				echo 'SIGCHLD received'."\n";
				self::$currentProcessNum--;
				pcntl_wait($status);
				break;
			
			default:
				# code...
				break;
		}
		// self::$currentProcessNum--;
	}

	private static function installSignal()
	{
		pcntl_signal(SIGCHLD, array('Master', 'sigHandle'));
	}

	private static function SignalIgnore()
	{
		pcntl_signal(SIGPIPE, SIG_IGN);
		pcntl_signal(SIGTTIN, SIG_IGN);
		pcntl_signal(SIGTTOU, SIG_IGN);
		pcntl_signal(SIGQUIT, SIG_IGN);
		pcntl_signal(SIGALRM, SIG_IGN);
		pcntl_signal(SIGINT, SIG_IGN);
		pcntl_signal(SIGUSR1, SIG_IGN);
		pcntl_signal(SIGUSR2, SIG_IGN);
		pcntl_signal(SIGHUP, SIG_IGN);
		pcntl_signal_dispatch();
	}
}