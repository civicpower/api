<?php
if(mapi_post_mandatory(
    "username",
    "password"
)) {
    $username = civicpower_no_space_word(gpost("username"));
    $password = trim(gpost("password"));
    $pass_hash = hash('sha256',$_ENV['GLOBAL_SALT'] . $password);

    if (!civicpower_phone_valid($username) && !civicpower_is_email($username)) {
        mapi_error("login_error", "Mauvaises informations de connexion.");
    } else {
        $user = sql_shift("
            SELECT *
            FROM usr_user
            WHERE (
                user_email LIKE '" . for_db($username) . "'
                OR user_phone_international = '" . for_db(civicpower_international_phone($username)) . "'
            )
            AND user_active = '1' AND user_ban = '0'
            LIMIT 1
        ");
        if (is_array($user) && count($user) > 0) {
            if (is_scalar($user['user_nb_fail']) && $user['user_nb_fail'] >= 10) {
                mapi_error("login_error", "Compte bloqué. Merci de contacter le support.");
            }else {
                if ($user['user_password'] != $pass_hash) {
                    sql("
                        UPDATE usr_user SET
                        user_nb_fail = user_nb_fail + 1
                        WHERE (
                            user_email LIKE '" . for_db($username) . "'
                            OR user_phone_international = '" . for_db(civicpower_international_phone($username)) . "'
                        )
                    ");
                    mapi_error("login_error", "Mauvaises informations de connexion");
                }else{
                    civicpower_invoke_login($user['user_id']);
                    mapi_success("login_success", "Succès de la connexion", civicpower_hash_db(false, $user['user_salt'], $_ENV['SALT_USER']));
                }
            }
        } else {
            mapi_error("login_error", "Utilisateur introuvable.");
        }
    }
}
