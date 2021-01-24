<?php
if (mapi_post_mandatory("token", "firstname", "lastname")) {
    $user = civicpower_check_token_user(gpost("token"));
    if ($user !== false) {
        $firstname = trim(gpost("firstname"));
        $lastname = trim(gpost("lastname"));

        $error = false;
        if (preg_match("~\d~", $firstname)) {
            mapi_error("firstname_contains_numeric", "Votre prénom ne doit pas contenir de chiffre");
            $error = true;
        } else if (local_count_alpha($firstname) <= 0) {
            mapi_error("firstname_contains_no_alpha", "Votre prénom doit contenir une lettre");
            $error = true;
        } else if (local_contains_bad_chars($firstname)) {
            mapi_error("firstname_bad_chars", "Votre prénom comporte des caractères spéciaux interdits");
            $error = true;
        } else if (local_last_char_forbidden($firstname)) {
            mapi_error("firstname_last_char", "Votre prénom comporte un caractère spécial interdit à la fin");
            $error = true;
        } else if (local_first_char_forbidden($firstname)) {
            mapi_error("firstname_first_char", "Votre prénom comporte un caractère spécial interdit au début");
            $error = true;
        } else if (substr_count($firstname,"'")>1) {
            mapi_error("firstname_too_many_quote", "Votre prénom ne peut comporter qu'une seule apostrophe");
            $error = true;
        } else if (substr_count($firstname,"-")>1) {
            mapi_error("firstname_too_many_dash", "Votre prénom ne peut comporter qu'un seule tiret");
            $error = true;
        } else {
            if (preg_match("~\d~", $lastname)) {
                mapi_error("lastname_contains_numeric", "Votre nom ne doit pas contenir de chiffre");
                $error = true;
            } else if (local_count_alpha($lastname) <= 0) {
                mapi_error("lastname_contains_no_alpha", "Votre nom doit contenir une lettre");
                $error = true;
            } else if (local_contains_bad_chars($lastname)) {
                mapi_error("lastname_bad_chars", "Votre nom comporte des caractères spéciaux interdits");
                $error = true;
            } else if (local_last_char_forbidden($lastname)) {
                mapi_error("lastname_last_char", "Votre nom comporte un caractère spécial interdit à la fin");
                $error = true;
            } else if (local_first_char_forbidden($lastname)) {
                mapi_error("lastname_first_char", "Votre nom comporte un caractère spécial interdit au début");
                $error = true;
            } else if (substr_count($lastname,"'")>1) {
                mapi_error("lastname_too_many_quote", "Votre nom ne peut comporter qu'une seule apostrophe");
                $error = true;
            } else if (substr_count($lastname,"-")>1) {
                mapi_error("lastname_too_many_dash", "Votre nom ne peut comporter qu'un seul tiret");
                $error = true;
            }
        }
        if ($error === false) {
            sql("
                    UPDATE usr_user SET
                        user_firstname = '" . for_db(trim($firstname)) . "',
                        user_lastname = '" . for_db(trim($lastname)) . "'
                    WHERE user_id = '" . for_db($user['user_id']) . "'
                ");

            mapi_success("user_updated", "User updated");
        }
    }
}
function local_last_char_forbidden($str) {
    $res = false;
    $str = trim($str);
    $last = substr($str, -1);
    if (in_array($last, ['-'])) {
        $res = true;
    }
    return $res;
}
function local_first_char_forbidden($str) {
    $res = false;
    $str = trim($str);
    $first = substr($str, 0, 1);
    if (in_array($first, ['-',"'"])) {
        $res = true;
    }
    return $res;
}
function local_contains_bad_chars($str) {
    $str = sans_accents($str);
    $str = preg_replace("~[a-zA-Z\-' ]~", "", $str);
    $str = trim($str);
    $len = strlen($str);
    return $len > 0;
}
function local_count_alpha($str) {
    $str = trim($str);
    $str = preg_replace("~[^a-zA-Z]~i", "", $str);
    $str = trim($str);
    return strlen($str);
}

?>