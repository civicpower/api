<?php
if(mapi_post_mandatory("token","password")) {
    $user = civicpower_check_token_user(gpost("token"));
    if($user !== false) {
        $password = trim(gpost("password"));
        $check = civicpower_check_password($password);
        if ($check["result"] == false) {
            mapi_error("password_error", $check['error']);
        }else {
            $new_pass = hash('sha256',$_ENV['GLOBAL_SALT'] . $password);
            $old_pass = sql_unique("
                SELECT user_password
                FROM usr_user
                WHERE user_id = '" . for_db($user['user_id']) . "'
            ");
            if($new_pass==$old_pass){
                mapi_error("password_is_same", "Vous ne pouvez pas utiliser le même mot de passe que votre mot de passe actuel. Merci d'en choisir un différent");
            }else {
                sql("
                    UPDATE usr_user SET
                    user_password = '" . for_db($new_pass) . "'
                    WHERE user_id = '" . for_db($user['user_id']) . "'
                ");
                mapi_success("password_updated", "Password updated");
            }
        }
    }
}
?>