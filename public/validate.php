<?php
if(mapi_post_mandatory(
    "value",
    "mode",
    "code"
)) {
    $value = civicpower_no_space_word(gpost("value"));
    $mode = trim(gpost("mode"));
    $error = false;
    if ($mode == "phone" && !civicpower_phone_valid($value)) {
        mapi_error("wrong_phone_format", "Wrong phone format");
        $error = true;
    } else if ($mode == "email" && !civicpower_is_email($value)) {
        mapi_error("wrong_email_format", "Wrong email format");
        $error = true;
    }
    if(!$error) {
        $code = gpost("code");

        $field_to_verif = "user_phone_international";
        $field_code = "user_code_validation_phone";
        $update_sql = "
            user_phone_international = IF(LENGTH(user_phone_international_pending)>0,user_phone_international_pending,user_phone_international),
            user_phone_dial = IF(LENGTH(user_phone_dial_pending)>0,user_phone_dial_pending,user_phone_dial),
            user_phone_national = IF(LENGTH(user_phone_national_pending)>0,user_phone_national_pending,user_phone_national)
        ";
        $value_to_verif = civicpower_international_phone($value);

        if ($mode == "email") {
            $field_to_verif = "user_email";
            $field_code = "user_code_validation_email";
            $update_sql = "
                user_email= IF(LENGTH(user_email_pending)>0,user_email_pending,user_email)
            ";
            $value_to_verif = $value;
        }

        $user = sql_shift($sql = "
            SELECT *
            FROM usr_user
            WHERE
                (
                    $field_to_verif" . "_pending = '" . for_db($value_to_verif) . "'
                    OR $field_to_verif" . " = '" . for_db($value_to_verif) . "'
                )
            AND user_active = '1' AND user_ban = '0'
            ORDER BY $field_code = '" . for_db($code) . "' DESC
        ");
        if (is_array($user) && count($user) > 0) {
            if ($user[$field_code] == $code) {
                sql("
                    UPDATE usr_user SET
                        $update_sql
                    WHERE user_id = '" . for_db($user['user_id']) . "'
                ");
                sql("
                    UPDATE usr_user SET
                        user_email_pending = '',
                        user_phone_dial_pending = '',
                        user_phone_national_pending = '',
                        user_phone_international_pending = '',
                        user_code_validation_email = '".for_db(rand(1000,9999))."',
                        user_code_validation_phone = '".for_db(rand(1000,9999))."'
                    WHERE user_id = '" . for_db($user['user_id']) . "'
                ");
                civicpower_invoke_login($user['user_id']);
                mapi_success("confirmed", "The provided code is correct", civicpower_hash_db(false, $user['user_salt'], $_ENV['SALT_USER']));
            } else {
                mapi_error("wrong_code", "The provided code is wrong");
            }
        } else {
            mapi_error("user_not_found", "No user found");
        }
    }
}
?>