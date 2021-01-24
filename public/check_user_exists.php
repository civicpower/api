<?php
if (mapi_post_mandatory("token")) {
    $user = civicpower_check_token_user(gpost("token"),true);

    if ($user !== false) {
        mapi_success("user_exists", "Success, User exists");
    }
}
?>