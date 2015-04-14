<?php
/**
* 
*/
class testSignal
{
	
	public static function testAlarm()
	{
		// declare(ticks = 10);
		pcntl_signal(SIGALRM, array('testSignal', 'signalHandle'), false);
		pcntl_signal(SIGALRM, array('testSignal', 'signalHandle2'), false);
		// pcntl_alarm(1);
		while (1) {
			// echo 'do'."\n";
		pcntl_signal_dispatch();
		sleep(2);
		}
	}

	 /**
     * 捕捉alarm信号
     * @return void
     */
    public static function signalHandle()
    {
        self::tick();
        // pcntl_alarm(1);
    }

    public static function signalHandle2()
    {
        // self::tick();
        echo "2\n";
        // pcntl_alarm(1);
    }

    public static function tick()
    {
    	echo "1\n";
    }

    public static function loop()
    {
    	while (1) {

    		sleep(2);
    	}
    }

}
testSignal::testAlarm();
// testSignal::loop();