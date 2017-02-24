<?php
require_once(__DIR__.'/class/task.php');
$objTask = new Task();

if(isset($_POST['childIds'])){
	$depenciesTable = $objTask->getDependancies($_POST['childIds']);
	echo json_encode(array('dependancies'=>$depenciesTable));
}

if(isset($_POST['action'])){
	$action = $_POST['action'];
	$taskId = $_POST['taskId'];
	
	if($action == 'markDoneOrComplete'){
		$objTask->markDoneOrComplete($taskId);
	}elseif($action == 'markInProgress'){
		$objTask->markInProgress($taskId);
	}
	echo json_encode(array('success'=>true));
}