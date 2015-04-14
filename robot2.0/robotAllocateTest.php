<?php
	require '../allocation/lib/allocation.php';
	define('Allocation_Root_Dir', realpath(__DIR__."/")."/");
	chdir(Allocation_Root_Dir);
	$max = 1;
	$child = 0;
	$allocation = new allocation();
	$allocation->connect();
	$allocation->allocate();
	$allocation->send();
	$allocation->close();
	unset($allocation);
?>