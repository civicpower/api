<?php
if (mapi_post_mandatory("token", "option_list")) {
    $user = civicpower_check_token_user(gpost("token"));
    if ($user !== false) {
        $option_list = gpost("option_list");
        if ($option_list) {
            $option_list = preg_replace("~\D~", " ", $option_list);
            $option_list = trim($option_list);
            $option_list = explode(" ", $option_list);
            $option_list = array_unique($option_list);
            $all_numeric = true;
            foreach($option_list as $k => $v){
                if(!is_numeric($v) || $v<=0){
                    $all_numeric = false;
                    break;
                }
            }
            if(!$all_numeric){
                mapi_error("non_numeric", "Une erreur est survenue ! (non numeric option id)");
            }else{
                $test_can_vote = true;
                foreach ($option_list as $k => $v) {
                    $test_can_vote = civicpower_user_can_vote_option($user['user_id'], $v);
                    if ($test_can_vote === false) {
                        break;
                    }
                }
                if (!$test_can_vote) {
                    mapi_error("user_cannot_vote", "Vous ne pouvez pas voter pour cette consultation");
                } else {
                    $ballots = [];
                    $qtab = [];
                    $option_question = sql("
                        SELECT *
                        FROM bal_option
                        INNER JOIN bal_question ON question_id = option_question_id AND question_active='1'
                        WHERE option_id IN ('" . implode("','", $option_list) . "')
                        AND option_active = '1'
                    ");
                    foreach ($option_question as $k => $v) {
                        $ballots[$v["question_ballot_id"]][$v["question_id"]][] = $v;
                        $qtab[$v["question_id"]] = $v;
                    }
                    if (count($ballots) > 1) {
                        mapi_error("multiple_ballot", "Vous ne pouvez pas voter qu'à une consultation à la fois");
                    } else {
                        $error_list = [];
                        $bid = null;
                        foreach ($ballots as $ballot_id => $tab_question) {
                            $bid = $ballot_id;
                            foreach ($tab_question as $question_id => $tab_option) {
                                if(count($tab_option)==1 && $tab_option[0]['option_can_be_deleted']==0){

                                }else {
                                    $nb_fixe = 0;
                                    foreach($tab_option as $kk => $vv){
                                        if($vv['option_can_be_deleted']==0){
                                            $nb_fixe++;
                                        }
                                    }
                                    if($nb_fixe>0 && count($tab_option)>1){
                                        $error_list[] = 'Vos options de vote comportent des anomalies<br>ERROR '.__LINE__;
                                    }else {
                                        $nb_voted = count($tab_option);
                                        $qitem = $qtab[$question_id];
                                        $nb_min = $qitem["question_nb_vote_min"];
                                        $nb_max = $qitem["question_nb_vote_max"];
                                        if ($nb_voted < $nb_min || $nb_voted > $nb_max) {
                                            $nb_allowed = "";
                                            if ($nb_min == $nb_max) {
                                                $nb_allowed = $nb_min . " choix";
                                            } else {
                                                $nb_allowed = "entre " . $nb_min . " et " . $nb_max . " choix";
                                            }
                                            $error_list[] = 'La question "' . $qitem["question_title"] . '" autorise ' . $nb_allowed . '.<br>Vous avez sélectionné ' . $nb_voted . ' choix de vote';
                                        }
                                    }
                                }
                            }
                        }
                        if (count($error_list) > 0) {
                            mapi_error("nb_choice_incorrect", implode("<br><br>", $error_list));
                        } else {
                            $ballot = sql_shift("
                                SELECT *
                                FROM bal_ballot
                                WHERE ballot_id = '".for_db($bid)."'
                            ");
                            $can_change = $ballot["ballot_can_change_vote"] == 1;
                            $nb_already_voted = intval(sql_unique("
                                SELECT COUNT(*) AS nb
                                FROM vot_vote
                                INNER JOIN bal_option ON option_id = vote_option_id AND option_active = '1'
                                INNER JOIN bal_question ON question_id = option_question_id AND question_active = '1'
                                WHERE vote_user_id =  '".for_db($user['user_id'])."'
                                AND question_ballot_id = '".for_db($bid)."'
                                AND vote_active = '1'
                            "));
                            if($nb_already_voted>0 && !$can_change){
                                mapi_error("cant_change_vote", "Cette consultation n'autorise pas la modification de votre vote");
                            }else {
                                sql("
                                        UPDATE vot_vote
                                        SET vote_active = '0'
                                        WHERE vote_user_id = '" . for_db($user['user_id']) . "'
                                        AND vote_option_id IN (
                                            SELECT option_id
                                            FROM bal_option
                                            WHERE option_question_id IN (
                                                SELECT option_question_id
                                                FROM bal_option
                                                WHERE option_id IN ('" . implode("','", $option_list) . "')
                                            )
                                        )
                                    ");
                                foreach ($option_list as $k => $v) {
                                    sql("
                                            INSERT INTO vot_vote SET
                                            vote_user_id = '" . for_db($user["user_id"]) . "',
                                            vote_option_id = '" . for_db($v) . "',
                                            vote_datetime = NOW(),
                                            vote_active = '1'
                                        ");
                                }
                                mapi_success("vote_recorded", "Your vote has been recorded");
                            }
                        }
                    }
                }
            }

        }else{
            mapi_error("vote_error", "General error.");
        }
    }
}
?>