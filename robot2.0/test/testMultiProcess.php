<?php
/**
* 
*/
class testMultiProcess
{
	private static $my = 2;
	const test = 1;
	private static $mypid = array();
	public static function newProcess()
	{
		self::installSignal();
		$pid = pcntl_fork();
		if ($pid > 0) {
			while (1) {
				// pcntl_waitpid($childpid,$status,0);
				// $status = 0;
				echo 'parent';
				sleep(2);
				// pcntl_wait($status, 0);
				// sleep(1);
				// echo 'parent'."\n";
				pcntl_signal_dispatch();
				// posix_kill($childpid, SIGKILL);
			}
		}else{
			while (1) {
				echo self::$my."\n";
				echo 'childpid'.getmypid()."\n";
				sleep(2);
				exit();
				// array_push(self::$mypid, getmypid());
				// posix_kill(getmypid(), SIGTERM);
				// exit();
				// posix_kill(getmypid(), SIGKILL)
				// echo self::test."\n";
				// exit();
				// sleep(2);
			}	
		}
	}

	public static function installSignal()
	{
		pcntl_signal(SIGCHLD, array('testMultiProcess','kill'));
	}

	// 当子进程退出的时候回收子进程
	public static function kill($pid)
	{
		// echo 'signal come'."\n";
		// var_dump(self::$mypid);
		pcntl_wait($status);
		// foreach (self::$mypid as $value) {
		// 	posix_kill($value, SIGKILL);
		// }
	}
}
testMultiProcess::newProcess();
// testMultiProcess::installSignal();
// $b = new testMultiProcess;
// $b->newProcess();