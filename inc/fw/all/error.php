<?php
function exit_error($msg,$type=null){
	if(is_null($type)){
		$type='default_error';
	}
	header("HTTP/1.1 500 ".$type);
	echo $msg;
	exit();
}
function exiterror($msg,$type=null){
	exit_error($msg,$type);
}
?>