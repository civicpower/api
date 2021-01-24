<?php
if(mapi_post_mandatory("shortcode")) {
    $shortcode = gpost("shortcode");
    $route = "/ballot-list";
    $to = "";
    $ballot_token = "";
    if (is_string($shortcode) && strlen($shortcode) > 0) {
        $link = sql_shift("
            SELECT *
            FROM mail_link
            WHERE mlink_shortcode = '" . for_db($shortcode) . "'
        ");
        if (is_array($link) && count($link) > 0) {
            $destination = $link["mlink_destination"];
            $to_tmp = $link["mlink_to"];
            if (is_string($destination) && strlen($destination) > 0) {
                if (is_string($to_tmp) && strlen($to_tmp) > 0) {
                    $to = $to_tmp;
                    $route = $destination;
                }
                $ballot_id_tmp = $link["mlink_ballot_id"];
                if(is_numeric($ballot_id_tmp) && $ballot_id_tmp>0){
                    $ballot_token = sql_unique("
                        SELECT " . civicpower_hash_db(true, "ballot_id", $_ENV['SALT_BALLOT']) . " AS ballot_token
                        FROM bal_ballot
                        WHERE ballot_id LIKE '" . for_db($ballot_id_tmp) . "'
                    ");
                }
            }
            sql("
                UPDATE mail_link SET
                mlink_clicked = '1'
                WHERE mlink_shortcode = '" . for_db($shortcode) . "'
            ");
        }
    }
    mapi_success("ml_link", "Mail link Route", [
        'shortcode'=>$shortcode,
        'route'=>$route,
        'ballot_token'=>$ballot_token,
        'to'=>$to
    ]);
}
?>