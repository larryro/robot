<?php
/**
* 
*/
class testShm
{
	public static function getNew()
	{
		$id = shm_attach(2,300);
		echo $id;
	}
}
testShm::getNew();