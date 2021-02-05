<?php
if(mapi_post_mandatory("username")) {
    $username = civicpower_no_space_word(gpost("username"));
    if (!civicpower_phone_valid($username) && !civicpower_is_email($username)) {
        mapi_error("login_error", "Mauvaises informations de connexion.");
    }else {
        if (civicpower_phone_valid($username)) {
            $mode = "phone";
            $where = "user_phone_international = '" . for_db(civicpower_international_phone($username)) . "'";
        } else {
            $mode = "email";
            $where = "user_email LIKE '" . for_db($username) . "'";
        }
        $user = sql_shift("
            SELECT *
            FROM usr_user
            WHERE $where
            AND user_active = '1' AND user_ban = '0'
        ");
        if (count($user) <= 2) {
            mapi_error("account_not_found", "Ce compte est introuvable");
        }else {
            $pass_hash = sql_unique("
                SELECT user_password
                FROM usr_user
                WHERE (
                    user_email LIKE '" . for_db($username) . "'
                    OR user_phone_international = '" . for_db(civicpower_international_phone($username)) . "'
                )
            ");
            $code = null;
            $has_pass = strlen($pass_hash) > 0 ? "yes" : "no";
            if ($has_pass == "no") {
                $code = random_int(1000, 9999);
                if ($mode == "email") {
                    if (isset($user['user_code_validation_email']) && strlen($user['user_code_validation_email']) > 0) {
                        $code = $user['user_code_validation_email'];
                    }
                } else {
                    if (isset($user['user_code_validation_phone']) && strlen($user['user_code_validation_phone']) > 0) {
                        $code = $user['user_code_validation_phone'];
                    }
                }
                if($mode == "email"){
                    sql("
                        UPDATE usr_user SET
                        user_code_validation_email = '".for_db($code)."'
                        WHERE user_id = '" . for_db($user['user_id']) . "'
                    ");
                }else{
                    sql("
                        UPDATE usr_user SET
                        user_code_validation_phone = '".for_db($code)."'
                        WHERE user_id = '" . for_db($user['user_id']) . "'
                    ");
                }
                if (cp_serveur_can_send_sms()) {
                    if ($mode == "email") {
                        sql("
                            UPDATE usr_user SET
                            user_emailcode_send = '1'
                            WHERE user_id = '" . for_db($user['user_id']) . "'
                        ");
                    } else {
                        civicpower_send_sms(
                            civicpower_international_phone($username),
                            "[" . $code . "] est votre code de confirmation Civicpower",
                            $user["user_id"]
                        );
                    }
                    $code = null;
                }
            }
            mapi_success("account_pass", $has_pass, $code);
        }
    }
}
?>