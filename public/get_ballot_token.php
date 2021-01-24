<?php
if(mapi_post_mandatory("shortcode")) {
    $shortcode = gpost("shortcode");
    $ballot_token = sql_unique("
        SELECT " . civicpower_hash_db(true, "ballot_id", $_ENV['SALT_BALLOT']) . " AS ballot_token
        FROM bal_ballot
        WHERE ballot_shortcode LIKE '" . for_db($shortcode) . "'
    ");
    mapi_success("ballot_token", "Ballot Token", $ballot_token);
}
?>