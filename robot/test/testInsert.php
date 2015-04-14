<?php
	/**
	* 
	*/
	class testInsert
	{
		private $connection;
		function __construct()
		{
			$this->connection = new mysqli('localhost','root','slfy2014',
				'snewflyhelper_2');
			$this->connection->query('set names utf8');
		}
		public function insertUsers()
		{
			for ($i=0; $i < 10000; $i++) { 
				$sql = "insert into users (`name`,`telephone`,`true_name`,`group_id`) values('test$i','ouHUtt-OpyLTpWIksy8u6d70WT40$i','test$i',4)";
				$this->connection->query($sql);			
			}
		}

		public function close()
		{
			$this->connection->close();
		}
		public function insertTestMessages()
		{
			for ($i=0; $i < 100; $i++) {
				$time = time();
				$users_id = 100 + $i;
				$sql = "insert into messages (`pic`,`audio`,`audio_text`,`state`,`users_id`,`upload_time`)
				values ('test','test','test',382,$users_id,$time)";
				try {
					$this->connection->query($sql);
				} catch (Exception $e) {
					echo 'insert wrong'."\n";
				}
				
			}
		}

	}
	$test = new testInsert();
	$test->insertUsers();
	$test->close();
?>