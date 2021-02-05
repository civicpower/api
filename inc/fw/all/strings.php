<?php
function for_db($str){
	if(!isset($GLOBALS['db'])){
		sql("SELECT 0");
	}
	return mysqli_real_escape_string($GLOBALS['db'],stripslashes($str));
}

function sans_accents($string){
	return str_replace(
		array('à','á','â','ã','ä', 'ç', 'è','é','ê','ë', 'ì','í','î','ï', 'ñ', 'ò','ó','ô','õ','ö', 'ù','ú','û','ü', 'ý','ÿ', 'À','Á','Â','Ã','Ä', 'Ç', 'È','É','Ê','Ë', 'Ì','Í','Î','Ï', 'Ñ', 'Ò','Ó','Ô','Õ','Ö', 'Ù','Ú','Û','Ü', 'Ý'),
		array('a','a','a','a','a', 'c', 'e','e','e','e', 'i','i','i','i', 'n', 'o','o','o','o','o', 'u','u','u','u', 'y','y', 'A','A','A','A','A', 'C', 'E','E','E','E', 'I','I','I','I', 'N', 'O','O','O','O','O', 'U','U','U','U', 'Y'),
		$string);
}
function dt2fr($datetime,$days="Dimanche|Lundi|Mardi|Mercredi|Jeudi|Vendredi|Samedi"){
    $days = explode("|",$days);
    $datetime = strtotime($datetime);
    $jour = $days[date("w",$datetime)];
    return $jour . ' ' . date("d/m/Y H:i:s",$datetime);
}
function to_html($str){
	$str = str_replace("&lt;","<",$str);
	$str = str_replace("&gt;",">",$str);
	return $str;
}
function for_html($str){
	if(is_resource($str)){
		return '***RESOURCE***';
	}elseif(is_object($str)){
		return '***OBJECT***';
	}else{
        $str = htmlentities(my_concat($str), ENT_QUOTES, 'UTF-8');
        return $str;
	}
}
function in_guill($str){
	return str_replace('"','&#34;',$str);
}
function my_concat($arr,$separator='',$format='%s',$ifnotnull=false){
	$txt_return='';
    if(is_array($arr) || is_object($arr)){
		foreach ($arr as $key => $value) {
	        if (is_array($value) || is_object($value)){
	        	$txt_return.=my_concat($value,$separator,$format,$ifnotnull);
	        }else{
	        	if(($ifnotnull && strlen($value)>0) || !$ifnotnull){
			        $txt_return.=sprintf($format,$value).$separator;
			    }
		    }
	    }
	}else{
    	if(($ifnotnull && strlen($arr)>0) || !$ifnotnull){
			$txt_return.=sprintf($format,$arr).$separator;
		}
	}
    return $txt_return;
}
?>