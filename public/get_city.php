<?php
if(mapi_post_mandatory("zipcode")) {
    $zipcode = gpost("zipcode");
    $zipcode = substr($zipcode, 0, 50);
    $zipcode = preg_replace("~[^0-9]~","",$zipcode);
    $zipcode = trim($zipcode);
    $city_list = sql("
        SELECT city_id,nom_commune
        FROM (SELECT DISTINCT * FROM geo_fr_cities WHERE Code_postal = '" . for_db($zipcode) . "' ORDER BY city_id) t
        GROUP BY nom_commune
        ORDER BY nom_commune ASC
    ");
    mapi_success("city_list", "City List", $city_list);
}
?>
