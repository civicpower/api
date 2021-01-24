<?php
function for_db($str){
	if(!isset($GLOBALS['db'])){
		sql("SELECT 0");
	}
	return mysqli_real_escape_string($GLOBALS['db'],stripslashes($str));
}

function prenom($prenom){
	return ucwords(strtolower($prenom));
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
function html2txt($chaine){
	$chaine = eregi_replace("&eacute;", "é", $chaine);
	$chaine = eregi_replace("&egrave;", "è", $chaine);
	$chaine = eregi_replace("&ecirc;", "ê", $chaine);
	$chaine = eregi_replace("&euml;", "ë", $chaine);
	$chaine = eregi_replace("&agrave;", "à", $chaine);
	$chaine = eregi_replace("&acirc;", "â", $chaine);
	$chaine = eregi_replace("&iuml;", "ï", $chaine);
	$chaine = eregi_replace("&icirc;", "î", $chaine);
	$chaine = eregi_replace("&ocir;", "ô", $chaine);
	$chaine = eregi_replace("&ugrave;", "ù", $chaine);
	$chaine = eregi_replace("&ucirc;", "û", $chaine);
	$chaine = eregi_replace("&ccedil;", "ç", $chaine);
	return $chaine;
}
function from_spaw($txt){
	$txt = mysql_real_escape_string($txt);
	return $txt;
}
function to_spaw($txt){
	$txt = stripslashes($txt);
	return $txt;
}

function soit(){
	$res=null;
	$tab = func_get_args();
	foreach($tab as $v){
		if(isset($v) && !is_null($v)){
			if(is_array($v) || is_object($v)){
				if(count($v)>0){
					$res=$v;
					break;
				}
			}else{
				if(strlen($v)>0){
					$res=$v;
					break;
				}
			}
		}
	}
	return $res;
}
function is_vide($var){
	return empty($var) && strlen($var)==0;
}
function isvide($var){
	return is_vide($var);
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
        if(false){
            $str = str_replace(array("<",">"),array("&lt;","&gt;"),$str);
        }
        return $str;
	}
}
function in_guill($str){
	return str_replace('"','&#34;',$str);
}
function for_input($txt){
	$res=$txt;
	$res = str_replace(
		array("\\'","'","\"","\\\""),
		array("&#39;","&#39;","&#34;","&#34;"),
		$res
	);
	return $res;
}
function from_textarea($str){
	$str=str_replace(BN,BR,$str);
	return $str;
}
function chaine_hasard(){
	return md5(uniqid(rand(), true));
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
function a2t($arr,$compress=0,$fill='',$tab=1){
	return array2txt($arr,$compress,$fill,$tab);
}
function array2txt($arr,$compress=0,$fill='',$tab=1) {
    $t="";
	$txt_return='';
	if($tab==1){
		$txt_return.='<pre>' . BN;
	}
	if (!$fill) {
        $txt_return.='array(';
    }
    $n=rand();
    $run[$n]=0;
    for($i=0;$i<$tab;$i++) {
        $t.="\t";
    }
    if(is_array($arr)){
	    foreach ($arr as $key => $value) {
	        if (!$run[$n]) {
	        	$c='';
	        } else {
	        	$c=', ';
	        }
	        $run[$n]++;
	        if (is_array($value)) {
	        	$txt_return.=$c."\n".$t.$key.' => array('.array2txt($value,$compress,1,$tab+1);
	        	continue 1;
	        }
	        $txt_return.=$c."\n".$t.$key.' => '.$value.'';
	    }
	}
    $t='';
    for($i=0;$i<$tab-1;$i++) {
        $t.="\t";
    }
    if (!$fill) {
        $txt_return.="\n".$t.');'."\n";
    } else {
        $txt_return.="\n".$t.')';
    }
	if($tab==1){
    	$txt_return  .=   '</pre>';
    }
    if ($compress) {
        return gzcompress($txt_return, 9);
    } else {
        return $txt_return;
    }
}
function datetime2fr($str){
	if(eregi('([0-9]{2})-([0-9]{2})-([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})',$str,$tab)){
		printr($tab);
	}else{
		return $str;
	}
}
function datefr($date) {
  $split1 = explode(" ",$date);
  $split1 = $split1[0];
  $split = explode("-",$split1);
  if(count($split)>=3){
    $annee = $split[0];
    $mois = $split[1];
    $jour = $split[2];
    return "$jour"."/"."$mois"."/"."$annee";
  }else{
    return "";
  }
}
function mixer($tab1,$tab2){
	if(!is_array($tab1)){
		$tab1=array($tab1);
	}
	if(!is_array($tab2)){
		$tab2=array($tab2);
	}
	$res = array();
	foreach($tab1 as $v){
		foreach($tab2 as $vv){
			$res[] = $v . ' ' . $vv;
		}
	}
	unset($tab1);
	unset($tab2);
	return $res;
}

function rewriting( $texte,  $sep_mots, $max_caracteres=900 ){
	//$texte = strip_tags($texte);
	//$texte = ereg_replace('^<[^>]*>','',$texte);
   // Définition des caractères accentués
   //$texte = html2txt($texte);
   $car_speciaux = array( 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'Ù', 'Ú', 'Û', 'Ü', 'ù', 'ú', 'û', 'ü', 'ß', 'Ç', 'ç', 'Ð', 'ð', 'Ñ', 'ñ', 'Þ', 'þ', 'Ý', 'ÿ', 'Ÿ' );
   // ... et de leurs "équivalents" non-accentués
   $car_normaux  = array( 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'E', 'E', 'E', 'E', 'e', 'e', 'e', 'e', 'I', 'I', 'I', 'I', 'i', 'i', 'i', 'i', 'O', 'O', 'O', 'O', 'O', 'O', 'o', 'o', 'o', 'o', 'o', 'o', 'U', 'U', 'U', 'U', 'u', 'u', 'u', 'u', 'B', 'C', 'c', 'D', 'd', 'N', 'n', 'P', 'p', 'Y', 'Y', 'Y' );

   // On commence par supprimer les accents
   $texte = str_replace($car_speciaux, $car_normaux, $texte);

   // On remplace les caractères non-alphanumériques par le séparateur $sep_mots
   $texte = preg_replace( "/[^A-Za-z0-9]+/", $sep_mots, $texte );

   // On supprime le séparateur s'il se trouve en début ou fin de chaîne
   $texte = trim( $texte, $sep_mots );

   // On limite la chaine à $max_caracteres caractères (ici 50 caractères)
   $texte = substr( $texte, 0, $max_caracteres );
   $texte = trim( $texte, $sep_mots );

   // On convertit le tout en minuscules
   $texte = strtolower( $texte );

   // On ajoute l'id à la fin pour avoir une url unique et on ajoute l'extension (ici .html)
   $texte = $texte;

   // On retourne le résultat
   return ( $texte );
}
function simplize($txt){
	$txt=rewriting($txt, "-", 150);
	return $txt;
}
function if_num_else_zero($v){
	if(is_numeric($v)){
		return $v;
	}else{
		return 0;
	}
}
function flouter($nom,$color=null){
	//$nom = strtolower($nom);
	//$nom = ucwords($nom);
	$nom = ereg_replace('(.)','\1_',$nom);
	$nom = ereg_replace('_$','',$nom);
	$nom = explode('_',$nom);
	foreach($nom as $k=>$v){
		if(!ereg("[ \r\n]",$v)){
			if(is_null($color)){
				$nom[$k] = '#';
			}else{
				$nom[$k] = '<span style="color:'.for_html($color).'">#</span>';
			}
			//$nom[$k] = '<img class="flou" src="/html/images/autres/rand' . rand(0,9) .'.gif" />';
		}
	}
	$nom = implode('',$nom);
	return $nom;
}
function first_letter($string){
	return substr($string,0,1);
}
function str2color($str) {
  $code = dechex(crc32($str));
  $code = substr($code, 0, 6);
  return "#".$code;
}
function unescapehtml($unsafe) {
	/*
	$unsafe = str_replace("&amp;lt;", "<",$unsafe);
	$unsafe = str_replace("&amp;gt;", ">",$unsafe);
	   */
	$unsafe = str_replace("&amp;cent;", "¢",$unsafe);
	$unsafe = str_replace("&amp;pound;", "£",$unsafe);
	$unsafe = str_replace("&amp;euro;", "€",$unsafe);
	$unsafe = str_replace("&amp;yen;", "¥",$unsafe);
	$unsafe = str_replace("&amp;deg;", "°",$unsafe);
	$unsafe = str_replace("&amp;frac14;", "¼",$unsafe);
	$unsafe = str_replace("&amp;OElig;", "Œ",$unsafe);
	$unsafe = str_replace("&amp;frac12;", "½",$unsafe);
	$unsafe = str_replace("&amp;oelig;", "œ",$unsafe);
	$unsafe = str_replace("&amp;frac34;", "¾",$unsafe);
	$unsafe = str_replace("&amp;Yuml;", "Ÿ",$unsafe);
	$unsafe = str_replace("&amp;iexcl;", "¡",$unsafe);
	$unsafe = str_replace("&amp;laquo;", "«",$unsafe);
	$unsafe = str_replace("&amp;raquo;", "»",$unsafe);
	$unsafe = str_replace("&amp;iquest;", "¿",$unsafe);
	$unsafe = str_replace("&amp;Agrave;", "À",$unsafe);
	$unsafe = str_replace("&amp;Aacute;", "Á",$unsafe);
	$unsafe = str_replace("&amp;Acirc;", "Â",$unsafe);
	$unsafe = str_replace("&amp;Atilde;", "Ã",$unsafe);
	$unsafe = str_replace("&amp;Auml;", "Ä",$unsafe);
	$unsafe = str_replace("&amp;Aring;", "Å",$unsafe);
	$unsafe = str_replace("&amp;AElig;", "Æ",$unsafe);
	$unsafe = str_replace("&amp;Ccedil;", "Ç",$unsafe);
	$unsafe = str_replace("&amp;Egrave;", "È",$unsafe);
	$unsafe = str_replace("&amp;Eacute;", "É",$unsafe);
	$unsafe = str_replace("&amp;Ecirc;", "Ê",$unsafe);
	$unsafe = str_replace("&amp;Euml;", "Ë",$unsafe);
	$unsafe = str_replace("&amp;Igrave;", "Ì",$unsafe);
	$unsafe = str_replace("&amp;Iacute;", "Í",$unsafe);
	$unsafe = str_replace("&amp;Icirc;", "Î",$unsafe);
	$unsafe = str_replace("&amp;Iuml;", "Ï",$unsafe);
	$unsafe = str_replace("&amp;ETH;", "Ð",$unsafe);
	$unsafe = str_replace("&amp;Ntilde;", "Ñ",$unsafe);
	$unsafe = str_replace("&amp;Ograve;", "Ò",$unsafe);
	$unsafe = str_replace("&amp;Oacute;", "Ó",$unsafe);
	$unsafe = str_replace("&amp;Ocirc;", "Ô",$unsafe);
	$unsafe = str_replace("&amp;Otilde;", "Õ",$unsafe);
	$unsafe = str_replace("&amp;Ouml;", "Ö",$unsafe);
	$unsafe = str_replace("&amp;Oslash;", "Ø",$unsafe);
	$unsafe = str_replace("&amp;Ugrave;", "Ù",$unsafe);
	$unsafe = str_replace("&amp;Uacute;", "Ú",$unsafe);
	$unsafe = str_replace("&amp;Ucirc;", "Û",$unsafe);
	$unsafe = str_replace("&amp;Uuml;", "Ü",$unsafe);
	$unsafe = str_replace("&amp;Yacute;", "Ý",$unsafe);
	$unsafe = str_replace("&amp;THORN;", "Þ",$unsafe);
	$unsafe = str_replace("&amp;szlig;", "ß",$unsafe);
	$unsafe = str_replace("&amp;agrave;", "à",$unsafe);
	$unsafe = str_replace("&amp;aacute;", "á",$unsafe);
	$unsafe = str_replace("&amp;acirc;", "â",$unsafe);
	$unsafe = str_replace("&amp;atilde;", "ã",$unsafe);
	$unsafe = str_replace("&amp;auml;", "ä",$unsafe);
	$unsafe = str_replace("&amp;aring;", "å",$unsafe);
	$unsafe = str_replace("&amp;aelig;", "æ",$unsafe);
	$unsafe = str_replace("&amp;ccedil;", "ç",$unsafe);
	$unsafe = str_replace("&amp;egrave;", "è",$unsafe);
	$unsafe = str_replace("&amp;eacute;", "é",$unsafe);
	$unsafe = str_replace("&amp;ecirc;", "ê",$unsafe);
	$unsafe = str_replace("&amp;euml;", "ë",$unsafe);
	$unsafe = str_replace("&amp;igrave;", "ì",$unsafe);
	$unsafe = str_replace("&amp;iacute;", "í",$unsafe);
	$unsafe = str_replace("&amp;icirc;", "î",$unsafe);
	$unsafe = str_replace("&amp;iuml;", "ï",$unsafe);
	$unsafe = str_replace("&amp;eth;", "ð",$unsafe);
	$unsafe = str_replace("&amp;ntilde;", "ñ",$unsafe);
	$unsafe = str_replace("&amp;ograve;", "ò",$unsafe);
	$unsafe = str_replace("&amp;oacute;", "ó",$unsafe);
	$unsafe = str_replace("&amp;ocirc;", "ô",$unsafe);
	$unsafe = str_replace("&amp;otilde;", "õ",$unsafe);
	$unsafe = str_replace("&amp;ouml;", "ö",$unsafe);
	$unsafe = str_replace("&amp;oslash;", "ø",$unsafe);
	$unsafe = str_replace("&amp;ugrave;", "ù",$unsafe);
	$unsafe = str_replace("&amp;uacute;", "ú",$unsafe);
	$unsafe = str_replace("&amp;ucirc;", "û",$unsafe);
	$unsafe = str_replace("&amp;uuml;", "ü",$unsafe);
	$unsafe = str_replace("&amp;yacute;", "ý",$unsafe);
	$unsafe = str_replace("&amp;thorn;", "þ",$unsafe);
	$unsafe = str_replace("&amp;quot;", "\"",$unsafe);
	$unsafe = str_replace("'", "'",$unsafe);
	return $unsafe;
}

function utf8_ansi($valor) {

    $utf8_ansi2 = array(
    "\u00c0" =>"À",
    "\u00c1" =>"Á",
    "\u00c2" =>"Â",
    "\u00c3" =>"Ã",
    "\u00c4" =>"Ä",
    "\u00c5" =>"Å",
    "\u00c6" =>"Æ",
    "\u00c7" =>"Ç",
    "\u00c8" =>"È",
    "\u00c9" =>"É",
    "\u00ca" =>"Ê",
    "\u00cb" =>"Ë",
    "\u00cc" =>"Ì",
    "\u00cd" =>"Í",
    "\u00ce" =>"Î",
    "\u00cf" =>"Ï",
    "\u00d1" =>"Ñ",
    "\u00d2" =>"Ò",
    "\u00d3" =>"Ó",
    "\u00d4" =>"Ô",
    "\u00d5" =>"Õ",
    "\u00d6" =>"Ö",
    "\u00d8" =>"Ø",
    "\u00d9" =>"Ù",
    "\u00da" =>"Ú",
    "\u00db" =>"Û",
    "\u00dc" =>"Ü",
    "\u00dd" =>"Ý",
    "\u00df" =>"ß",
    "\u00e0" =>"à",
    "\u00e1" =>"á",
    "\u00e2" =>"â",
    "\u00e3" =>"ã",
    "\u00e4" =>"ä",
    "\u00e5" =>"å",
    "\u00e6" =>"æ",
    "\u00e7" =>"ç",
    "\u00e8" =>"è",
    "\u00e9" =>"é",
    "\u00ea" =>"ê",
    "\u00eb" =>"ë",
    "\u00ec" =>"ì",
    "\u00ed" =>"í",
    "\u00ee" =>"î",
    "\u00ef" =>"ï",
    "\u00f0" =>"ð",
    "\u00f1" =>"ñ",
    "\u00f2" =>"ò",
    "\u00f3" =>"ó",
    "\u00f4" =>"ô",
    "\u00f5" =>"õ",
    "\u00f6" =>"ö",
    "\u00f8" =>"ø",
    "\u00f9" =>"ù",
    "\u00fa" =>"ú",
    "\u00fb" =>"û",
    "\u00fc" =>"ü",
    "\u00fd" =>"ý",
    "\u00ff" =>"ÿ");

    return strtr($valor, $utf8_ansi2);

}
?>