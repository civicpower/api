<?php
if(mapi_post_mandatory("token","street","city_id")) {
    $user = civicpower_check_token_user(gpost("token"));
    if($user !== false) {
        $streetnum = trim(gpost("streetnum"));
        $street = trim(gpost("street"));
        $city_id = trim(gpost("city_id"));
        $error = false;
        if (strlen($streetnum) > 50) {
            mapi_error("wrong_streetnum", "Le numéro de rue est incorrect");
            $error = true;
        }
        if (strlen($street) <= 0) {
            mapi_error("wrong_street", "Le nom de rue est incorrect");
            $error = true;
        }
        if (!is_numeric($city_id) || $city_id <= 0) {
            mapi_error("wrong_city_id", "Commune incorrecte");
            $error = true;
        }
        if(!$error) {
            sql("
                UPDATE usr_user SET
                    user_streetnum = '" . for_db($streetnum) . "',
                    user_street = '" . for_db($street) . "',
                    user_city_id = '" . for_db($city_id) . "'
                WHERE user_id = '" . for_db($user['user_id']) . "'
            ");
            mapi_success("user_updated", "User updated");
        }
    }
}
?>