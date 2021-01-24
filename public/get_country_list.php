<?php
$country_list = sql("
    SELECT
        id,
        libelle
    FROM geo_countries
    WHERE LENGTH(libelle)>0
    ORDER BY libelle ASC
");
mapi_success("country_list","Country list",$country_list);
?>