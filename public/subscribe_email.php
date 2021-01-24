<?php
if (mapi_post_mandatory("email")) {
    $email = trim(gpost("email"));
    if (!civicpower_is_email($email)) {
        mapi_error("wrong_email_format", "Wrong email format");
    }else{
        if(mapi_assert_user_not_exist($email)) {
            if (mapi_assert_email_domain_accepted($email)) {
                $sql = "
                    SELECT *
                    FROM usr_user
                    WHERE user_email = '" . for_db($email) . "'
                    AND user_active = '1' AND user_ban = '0'
                ";
                $user = sql_shift($sql);
                $code = rand(1000, 9999);
                if (!is_array($user) || count($user) <= 0) {
                    sql("
                        INSERT INTO usr_user SET
                        user_salt = '".for_db(civicpower_free_user_salt())."',
                        user_email_pending = '" . for_db($email) . "',
                        user_code_validation_email = '" . for_db($code) . "'
                    ");
                    $user = sql_shift($sql);
                }
                if (isset($user['user_email']) && strlen($user['user_email'])>3) {
                    mapi_error("user_already_exists", "Un utilisateur existe déjà avec cette adresse email.");
                }else {
                    if (isset($user['user_code_validation_email']) && is_numeric($user['user_code_validation_email']) && $user['user_code_validation_email'] > 0) {
                        $code = $user['user_code_validation_email'];
                    }
                    sql("
                        UPDATE usr_user SET
                            user_code_validation_email = '" . for_db($code) . "'
                        WHERE user_id = '" . for_db($user['user_id']) . "'
                    ");
                    if (cp_serveur_can_send_sms()) {
                        sql("
                            UPDATE usr_user SET
                            user_emailcode_send = '1'
                            WHERE user_id = '" . for_db($user['user_id']) . "'
                        ");
                        mapi_success("email_subscribed", "Your email is subscribed, Please validate it", "");
                    } else {
                        mapi_success("email_subscribed", "Your email is subscribed, Please validate it", "$code");
                    }
                }
            }
        }
    }
}
?>
