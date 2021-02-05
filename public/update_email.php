<?php
if(mapi_post_mandatory("email", "token")) {
    $user = civicpower_check_token_user(gpost("token"));
    if($user !== false) {
        $email = civicpower_no_space_word(gpost("email"));
        if (!civicpower_is_email($email)) {
            mapi_error("wrong_email_format", "Mauvais format d'adresse email");
        }else {
            if (mapi_assert_user_not_exist($email, $user['user_id'])) {
                if (mapi_assert_email_domain_accepted($email)) {
                    if (isset($user['user_code_validation_email']) && is_numeric($user['user_code_validation_email']) && $user['user_code_validation_email'] > 0) {
                        $code = $user['user_code_validation_email'];
                    } else {
                        $code = random_int(1000, 9999);
                    }
                    sql("
                        UPDATE usr_user SET
                            user_email_pending = '" . for_db($email) . "',
                            user_code_validation_email = '" . for_db($code) . "'
                        WHERE user_id = '" . for_db($user['user_id']) . "'
                    ");
                    if (cp_serveur_can_send_sms()) {
                        sql("
                            UPDATE usr_user SET
                            user_emailcode_send = '1'
                            WHERE user_id = '" . for_db($user['user_id']) . "'
                        ");
                        mapi_success("email_subscribed", "Votre adresse email a été enregistrée", "");
                    } else {
                        mapi_success("email_subscribed", "Votre adresse email a été enregistrée", "$code");
                    }
                }
            }
        }
    }
}
?>

