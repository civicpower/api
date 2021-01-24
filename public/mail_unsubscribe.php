<?php
if(mapi_post_mandatory("to")) {
    $to = gpost("to");
    sql("
        UPDATE mail_unsub SET
        unsub_active ='0'
        WHERE unsub_to = '".for_db($to)."'
    ");
    $list = gpost("list");
    if(is_array($list) && count($list)>0){
        foreach($list as $k => $v){
            if($v=="all"){
                sql("
                    INSERT INTO mail_unsub SET
                    unsub_to = '".for_db($to)."',
                    unsub_value = '%'
                ");
            }else if($v=="ballot"){
                sql("
                    INSERT INTO mail_unsub SET
                    unsub_to = '".for_db($to)."',
                    unsub_value = 'asker_%'
                ");
            }else{
                $ballot = sql_shift("
                    SELECT *
                    FROM bal_ballot
                    WHERE " . civicpower_hash_db(true, "ballot_id", $_ENV['SALT_BALLOT']) . " = '" . for_db($v) . "'
                ");
                if(is_array($ballot) && count($ballot)>0){
                    $asker_id = $ballot['ballot_asker_id'];
                    sql("
                        INSERT INTO mail_unsub SET
                        unsub_to = '".for_db($to)."',
                        unsub_value = '".for_db("asker_".$asker_id)."'
                    ");
                }
            }
        }
    }
    mapi_success("unsubscribed", "Unsubscribed");
}
?>