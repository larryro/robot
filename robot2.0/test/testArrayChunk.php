<?php
/**
* 
*/
class testArrayChunk
{
	public static function chunk()
	{
		$arr = array(array(1),array(2),array(3));
		$arrList = array_chunk($arr, 1);
		var_dump($arrList);
	}
}
testArrayChunk::chunk();