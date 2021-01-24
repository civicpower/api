<?php
$filter = false;
if (request_exists("shortcode")) {
    $filter = "ballot_shortcode = '" . for_db(grequest("shortcode")) . "'";
} else if (request_exists("ballot_token")) {
    $filter = civicpower_hash_db(true, "ballot_id", $_ENV['SALT_BALLOT']) . " = '" . for_db(grequest("ballot_token")) . "'";
}
if ($filter !== false) {
    $ballot = sql_shift("
        SELECT
            bal_ballot.*,
            ".civicpower_hash_db(true,"ballot_asker_id",$_ENV['SALT_ASKER'])." AS asker_token
        FROM bal_ballot
        INNER JOIN ask_asker ON asker_id = ballot_asker_id
        WHERE 1=1
        AND $filter
        AND ballot_active = '1'
        AND ballot_bstatus_id >= 10
    ");
    mapi_success("ballot_meta", "Ballot meta tags", $ballot);
} else {
    mapi_error("ballot_error", "General error.");
}
?>