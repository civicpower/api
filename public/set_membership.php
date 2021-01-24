<?php
if(mapi_post_mandatory("token","value")) {
    $user = civicpower_check_token_user(gpost("token"));
    if($user !== false) {
        $value = gpost("value");
        $value_sql = ($value == 1 ? $_ENV['CP_MEMBER_STATUS_ADHESION_DEMANDEE'] : $_ENV['CP_MEMBER_STATUS_ADHESION_ANNULEE_PAR_USER']);
        sql("
            INSERT INTO fdt_member SET
                member_user_id = '" . for_db($user['user_id']) . "',
                member_status_id = '" . for_db($value_sql) . "',
                member_update = NOW()
            ON DUPLICATE KEY UPDATE
                member_status_id = '" . for_db($value_sql) . "',
                member_update = NOW()
        ");
        mapi_success("membership_updated", "Votre demande a été prise en compte");
    }
}
?>