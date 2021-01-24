<?php
function printr($var,$title='PRINTR',$n=0,$opened=false){
	if($n==0){
		global $fw;
		if(isset($fw)){
			$fw->add_css('printr');
			$fw->add_script('printr');
		}
	}
	$res = array();
	$divid = md5(uniqid(rand(), true));
	if($n<10){
		$res[] = '<!--{literal}--><fieldset class="printr_'.($opened?'op':'cl').'">';
		$res[] = '<legend onclick="printr_switch(this)">'.for_html($title).'</legend>';
		$res[] = '<div>';
		if(is_array($var) || is_object($var)){
			foreach($var as $k => $v){
				$res[] = printr($v,$k,$n+1,$opened);
			}
		}elseif(is_resource($var)){
			$res[] = '#RESSOURCE';
		}else{
			$res[] = for_html($var);
		}
		$res[] = '</div>';
		$res[] = '</fieldset><!--{/literal}-->';
	}else{
		$res[] = for_html("** TROP PROFOND **") . "<br/>";
	}
	$res = implode(BN,$res);
	if($n==0){
		echo $res;
	}else{
		return $res;
	}
}
function printr_true($tab,$title='PRINTR'){
	return printr($tab,$title,1);
}
function pt($tab,$title='PRINTR'){
	return printr_true($tab,$title);
}
function printr2($elem){
    echo '<pre>';
    print_r($elem);
    echo '</pre>';
}
function printr3($tab,$return=false){
    $res = print_r($tab,true);
    if($return){
        return $res;
    }else{
        echo $res;
    }
}
?>