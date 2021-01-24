<?php
if (mapi_post_mandatory("token")) {
    $token = gpost("token");
    $user = civicpower_check_token_user($token);
    $view_all = false;
    if ($user !== false) {
        $view_all = true;
        $finish = post_exists("finish") && gpost("finish");
        $not_finish = post_exists("not_finish") && gpost("not_finish");
        $voted = null;
        if (post_exists("voted")) {
            $tmp = gpost("voted");
            if (!$tmp || $tmp == "false" || $tmp === false) {
                $voted = false;
            } else {
                $voted = true;
            }
        }
        if($not_finish && !$voted) {
            $view_all = false;
        }
    }
    $ballot_list = civicpower_get_ballot_list($token, $finish, $not_finish, $voted, $view_all);
    mapi_success("ballot_list", "Ballot List ".($view_all?"1":"0"), $ballot_list);
}

?>