<?php
function get_absolute_path($path) {
    $path = str_replace(array('/', '\\'), '/', $path);
    $parts = array_filter(explode('/', $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
        if ('.' == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    return implode('/', $absolutes);
}
function create_rep_if_not_exists($rep,$chmod=0777){
	if(! file_exists($rep) || ! is_dir($rep)){
		if(! @mkdir($rep,$chmod)){
			logg('Création impossible du repertoire ' . $rep . ' avec le chmod('.$chmod.')',__FILE__,__LINE__);
		}
	}
}
function my_mkdir($rep,$chmod="0777"){
	exec("mkdir -m ".$chmod." -p ".$rep);
	exec("chmod -R ".$chmod." ".$rep);
}

function my_chmod($rep,$chmod="0777"){
	exec("chmod -R ".$chmod." ".$rep);
}

function self(){
	return $_SERVER['PHP_SELF'];
}
function curdir(){
	if(isset($GLOBALS['override_curdir']) && strlen($GLOBALS['override_curdir']) > 0){
		return $GLOBALS['override_curdir'];
	}
	return dirname($_SERVER['SCRIPT_FILENAME']);
}
function fileroot($f){
	return str_replace($_ENV['REP_ROOT'],'',$f);
}
function webdir($f=''){
	$f = fileroot($f);
	//$f = get_absolute_path($f);
	if(is_file($_SERVER['SCRIPT_NAME'])){
		$file=dirname($_SERVER['SCRIPT_NAME']);
	}else{
		$file=$_SERVER['SCRIPT_NAME'];
	}
	$uu = dirname(str_replace($_ENV['REP_ROOT'],'' ,$file));
	$uu=$_ENV['HTTP_MODE'].'://' . $_SERVER['SERVER_NAME'] . $uu;
	return file_match($uu,$f);
}
function file_match($debut,$fin){
	$tab1=explode('/',$debut);
	$tab2=explode('/',$fin);
	if(array_pop($tab1) == $tab2[1]){
		$res = dirname($debut).$fin;
	}else{
		$res = $debut.(preg_match('~/$~',$debut)?preg_replace('~^/~','',$fin):$fin);
	}
	return $res;
}
function rediriger_301($file){
	header('Status: 301 Moved Permanently');
	header('LOCATION:'.$file);
	header('Connection: close');
	#exit();
}
function rediriger_vers($file,$method=null){
	if( (is_null($method) && headers_sent($file_num,$line_num)) || $method==1 ){
		global $fw;
		$fw->add_onload('window.location="'.str_replace('"','',$file).'";');
		//flush();
	}elseif(is_null($method) || $method==2){
		header("Location: " . $file);
	}
}
function icone($name,$options=''){
	$alt='';
	if(!eregi('alt=',$options)){
		$alt='alt="'.in_guill($name).'"';
	}
	return '<img '.$alt.' class="bo_icon" src="'.in_guill(webdir('/html/images/icones/'.$name.'.gif')).'" '.$options.' />';
}
function photo_exists($table,$champ,$num){

}

function mkpath($path) {
	if (is_dir($path) || file_exists($path)) return;
	mkdir($path, 0777, true);
}
?>