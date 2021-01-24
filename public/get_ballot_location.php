<?php
if(mapi_post_mandatory("ballot_token")) {
    $ballot_token = gpost("ballot_token");
    $location = sql_shift($sql = "
        SELECT
            city_id,
            code_postal
        FROM bal_filter
        INNER JOIN geo_fr_cities ON city_id = bfilter_city_id
        WHERE " . civicpower_hash_db(true, "bfilter_ballot_id", $_ENV['SALT_BALLOT']) . " = '" . for_db($ballot_token) . "'
        ORDER BY nom_commune ASC
        LIMIT 1
    ");
    mapi_success("ballot_location", "Ballot location", $location);
}
?>