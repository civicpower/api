<?php
if (mapi_post_mandatory("token")) {
    $user = civicpower_check_token_user(gpost("token"));
    if ($user !== false) {
        mapi_success("user_steps", "User steps", civicpower_get_steps($user));
    }
}
?>