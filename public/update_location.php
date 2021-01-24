<?php
if(mapi_post_mandatory("token","city_id")) {
    $user = civicpower_check_token_user(gpost("token"));
    if($user !== false) {
        $city_id = gpost("city_id");
        if (!is_numeric($city_id)) {
            mapi_error("city_id_not_numeric", "Le code commune doit être numérique");
        }else {
            $nb = intval(sql_unique("
                SELECT COUNT(*) AS nb
                FROM geo_fr_cities
                WHERE city_id = '" . for_db($city_id) . "'
            "));
            if ($nb == 0) {
                mapi_error("city_not_found", "Cette commune est inconnue");
            }else {
                sql("
                    UPDATE usr_user SET
                    user_city_id = '" . for_db($city_id) . "'
                    WHERE user_id = '" . for_db($user['user_id']) . "'
                ");
                mapi_success("location_updated", "Location updated");
            }
        }
    }
}
?>