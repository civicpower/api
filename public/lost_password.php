<?php
if (mapi_post_mandatory("username")) {
    $username = trim(gpost("username"));
    if (!civicpower_is_email($username) && !civicpower_phone_valid($username)) {
        mapi_error("wrong_format", "Mauvais format. Merci de renseigner un email ou un téléphone mobile.");
    } else {
        $user = sql_shift($sql = "
            SELECT
                *,
                user_email LIKE '" . for_db($username) . "' AS is_email,
                user_phone_international = '" . for_db(civicpower_international_phone($username)) . "' AS is_phone
            FROM usr_user
            WHERE (
                user_email LIKE '" . for_db($username) . "'
                OR user_phone_international = '" . for_db(civicpower_international_phone($username)) . "'
            )
            AND user_active = '1' AND user_ban = '0'
            LIMIT 1
        ");
        $mode = $user["is_email"] ? "email" : "phone";
        $code = "#";
        if (!is_array($user) || count($user) <= 0) {
            mapi_error("user_not_exists", "Cet utilisateur n'existe pas");
        } else {
            if ($mode == "email") {
                $code = $user["user_code_validation_email"];
            } else if ($mode == "phone") {
                $code = $user["user_code_validation_phone"];
            }
            $code_set = true;
            if (strlen($code) <= 0) {
                $code = random_int(1000, 9999);
                $code_set = local_set_confirmation_code($user['user_id'], $mode, $code);
            }
            if ($code_set) {
                if (cp_serveur_can_send_sms()) {
                    if ($user["is_email"]) {
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
                    mapi_success("lost_password", "Your $mode is subscribed, Please validate it", "");
                } else {
                    mapi_success("lost_password", "Your $mode is subscribed, Please validate it", $code);
                }
            }
        }

    }
}
function local_set_confirmation_code($user_id, $mode, $code) {
    if (!in_array($mode, ['phone', 'email'])) {
        mapi_error("error", "Error " . __LINE__);
        return false;
    } else {
        sql("
            UPDATE usr_user SET
            user_code_validation_" . $mode . " = '" . for_db($code) . "'
            WHERE user_id = '" . for_db($user_id) . "'
        ");
    }
    return true;
}
function local_unconfirm($user_id, $mode) {
    if (!in_array($mode, ['phone', 'email'])) {
        mapi_error("error", "Error " . __LINE__);
        return false;
    }
    sql("
        UPDATE usr_user SET
        user_" . $mode . "_confirmed = '0'
        WHERE user_id = '" . for_db($user_id) . "'
    ");
    return true;
}
?>