<?php
if(mapi_post_mandatory(
    "dial_code",
    "phone_number_national",
    "phone_number_international"
)) {
    $dial = civicpower_no_space_word(gpost("dial_code"));
    $phone_number_national = civicpower_no_space_word(gpost("phone_number_national"));
    $phone_number_international = civicpower_no_space_word(gpost("phone_number_international"));
    if (!civicpower_phone_valid($phone_number_international)) {
        mapi_error("wrong_phone_format", "Wrong phone format");
    }else{
        if(mapi_assert_user_not_exist($phone_number_international)) {
            if (mapi_assert_french_mobile($phone_number_international)) {
                $sql = "
                    SELECT *
                    FROM usr_user
                    WHERE user_phone_international = '" . for_db($phone_number_international) . "'
                    AND user_active = '1' AND user_ban = '0'
                ";
                $user = sql_shift($sql);
                $code = random_int(1000, 9999);
                if (!is_array($user) || count($user) <= 0) {
                    sql("
                        INSERT INTO usr_user SET
                        user_salt = '".for_db(civicpower_free_user_salt())."',
                        user_phone_international_pending = '" . for_db($phone_number_international) . "',
                        user_phone_national_pending = '" . for_db($phone_number_national) . "',
                        user_phone_dial_pending = '" . for_db($dial) . "',
                        user_code_validation_phone = '" . for_db($code) . "'
                    ");
                    $user = sql_shift($sql);
                }
                if (isset($user['user_phone_international']) && strlen($user['user_phone_international'])>0) {
                    mapi_error("user_already_exists", "Un utilisateur existe déjà avec ce numéro de téléphone.");
                }else {
                    if (isset($user['user_code_validation_phone']) && is_numeric($user['user_code_validation_phone']) && $user['user_code_validation_phone'] > 0) {
                        $code = $user['user_code_validation_phone'];
                    }
                    sql("
                        UPDATE usr_user SET
                            user_phone_international_pending = '" . for_db($phone_number_international) . "',
                            user_phone_national_pending = '" . for_db($phone_number_national) . "',
                            user_phone_dial_pending = '" . for_db($dial) . "',
                            user_code_validation_phone = '" . for_db($code) . "'
                        WHERE user_id = '" . for_db($user['user_id']) . "'
                    ");
                    if (cp_serveur_can_send_sms()) {
                        civicpower_send_sms(
                            $phone_number_international,
                            "[" . $code . "] est votre code de confirmation Civicpower",
                            $user["user_id"]
                        );
                        mapi_success("phone_subscribed", "Your phone is subscribed, Please validate it", "");
                    } else {
                        mapi_success("phone_subscribed", "Your phone is subscribed, Please validate it", $code);
                    }
                }
            }
        }
    }
}
?>

