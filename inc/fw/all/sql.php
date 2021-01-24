<?php
function defined_sql_config(){
	return $_ENV['MYSQL_HOST'] && $_ENV['MYSQL_LOGIN'] && $_ENV['MYSQL_PASS'] && $_ENV['MYSQL_BASE'];
}
function select_database(){
	mysqli_select_db($GLOBALS['db'],$_ENV['MYSQL_BASE'])
		or die(mysql_error() . "<br />Maintenance du site en cours! (".__LINE__.")");
}
function sql($requete,$select_database=true,$do_stripslashes=true){
	if(!isset($GLOBALS['db']) || empty($GLOBALS['db'])){
		$GLOBALS['db'] = mysqli_connect($_ENV['MYSQL_HOST'],$_ENV['MYSQL_LOGIN'],$_ENV['MYSQL_PASS']) or die('<hr />HOST='.$_ENV['MYSQL_HOST'].'<br />LOGIN='.$_ENV['MYSQL_LOGIN'].'<br />BASE='.$_ENV['MYSQL_BASE'].'<hr />'.mysql_error().'<hr />');
		if($select_database==true){
			select_database();
			mysqli_set_charset($GLOBALS['db'],'utf8');
			mysqli_query($GLOBALS['db'],"SET NAMES 'utf8'");
		}
	}
	$tab = array();
	$res = mysqli_query($GLOBALS['db'],$requete) or printr_sql_error($requete,'BASE:'.$_ENV['MYSQL_BASE'].'<br /><span style="background-color:yellow">'.mysqli_error($GLOBALS['db']).'</span>');
	if(is_resource($res) ||is_object($res)){
		while($tab[] = traite_sql(mysqli_fetch_assoc($res),$do_stripslashes)){}
		array_pop($tab);
	}else{
		$tab = mysqli_insert_id($GLOBALS['db']);
	}
	return $tab;
}
function traite_sql($tab,$do_stripslashes=true){
	if(is_array($tab)){
		foreach($tab as $k=>$v){
			if($do_stripslashes){
				$v = stripslashes($v);
			}
			$tab[$k] = $v;
		}
	}
	return $tab;
}
function printr_sql_error($requete,$error){
	$tab=array();
	$tab['requete']=$requete;
	$tab['erreur']=$error;
	echo '<pre>';
	print_r($tab);
	echo '</pre>';
	error_log("requete: ".$requete);
	error_log("requete: ".$error);
}
function sql_shift($requete,$do_stripslashes=true){
	$requete = sql($requete,true,$do_stripslashes);
	if(is_array($requete) && count($requete)>0){
		return array_shift($requete);
	}else{
		return array();
	}
}
function sql_pop($requete){
	return array_pop(sql($requete));
}
function sql_unique($requete){
	$res = sql($requete);
	if(is_array($res) && count($res)>0){
		$res = array_pop($res);
		if(is_array($res) && count($res)>0){
			$res = array_shift($res);
		}else{
			$res = '';
		}
	}else{
		$res='';
	}
	return $res;
}
function primary_key($table){
	$res='';
	$table = sql("SHOW index FROM " . $table);
	if(is_array($table) && count($table)>0){
		$table = array_shift($table);
		$res = $table['Column_name'];
	}
	return $res;
}
function arranger_tableau_par($tab,$colonne){
	$res = array();
	foreach($tab as $k => $v){
		$res[$v[$colonne]] = $v;
	}
	return $res;
}
?>