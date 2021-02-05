<?php
if(mapi_post_mandatory("ballot_token")) {
    $ballot_token = gpost("ballot_token");
    $user = null;
    if (post_exists("token")) {
        $token = gpost("token");
        if (is_string($token) && strlen($token) > 0) {
            $user = civicpower_check_token_user($token,false);
        }
    }
    $ballot = civicpower_get_ballot_info($ballot_token, gpost("result"), $user);
    if(is_null($ballot)){
        mapi_error("unknown_ballot", "Consultation introuvable");
    }else {
        if (!is_null($user) && $user !== false) {
            $ballot_list = civicpower_get_ballot_list($token, false, true);
            $ids = [];
            foreach ($ballot_list as $k => $v) {
                $ids[] = $v['ballot_id'];
            }
            if (in_array($ballot['ballot_id'], $ids)) {
                $ballot['can_vote'] = 1;
            } else {
                $ballot['can_vote'] = 0;
            }
        }
        $ok = true;
        if(isset($ballot['can_vote']) && $ballot['can_vote']==0){
            $nb_bfilter_all = intval(sql_unique("
                SELECT COUNT(*) AS nb
                FROM bal_filter
                WHERE bfilter_ballot_id = '".for_db($ballot['ballot_id'])."'
                AND bfilter_all = '1'
                AND bfilter_active = '1'
            "));
            if($nb_bfilter_all <= 0){
                $ok = false;
                mapi_error("unknown_ballot", "Consultation introuvable");
            }
        }
        if($ok) {
            mapi_success("ballot", "Ballot Info", $ballot);
        }
    }
}
?>