<?php
function get_token_blockchain(){
    return sql_shift("SELECT count(*) as count, token FROM `".for_db($_ENV['MYSQL_BASE_BLOCKCHAIN'])."`.`auth0_token`"." WHERE `date` >= SUBDATE(NOW(),1)");
}
function civicpower_load_salt(){
    if ( isset($_ENV['EGREGORE']) && ($_ENV['EGREGORE']==TRUE) ) {
        if ( ( $f=fopen($_ENV['EGREGORE'],'r') ) <> FALSE ) {
            while (($line = fgets($f)) !== false) {
                $tmp = explode("=", $line);
                $val = $tmp[1];
                $val = trim($val);
                $val = preg_replace("~^\"~","",$val);
                $val = preg_replace("~\"$~","",$val);
                $_ENV[ $tmp[0] ] = $val;
            }
        }
    }
}
function civicpower_invoke_login($user_id="", $civicpower=""){
    if (isset($user_id)&&($user_id<>"")) {
        sql("
            UPDATE usr_user SET
            user_nb_login = user_nb_login + 1,
            user_nb_fail = 0
            WHERE user_id = '".for_db($user_id)."'
        ");
    }
}
function civicpower_no_space_word($string) {
    return preg_replace('/\s+/', '',trim($string));
}
function civicpower_phone_valid($string) {
    $res = true;
    if (preg_match_all("/[0-9]/", $string) <= 6) {
        $res = false;
    }
    return $res;
}
function cp_serveur_can_send_sms() {
    $array = explode(",",$_ENV['CP_SERVER_CAN_SEND_SMS']);
    return in_array($_SERVER["SERVER_NAME"], $array);
}
function civicpower_get_steps($user) {
    $res = ["done" => 0, "remaining" => 0, "list" => ["email" => false, "phone" => false, "password" => false, "city" => false, "name" => false,//        "address" => false,
        ]];
        $res["user"] = $user;
        if (isset($user["user_email"]) && is_string($user["user_email"]) && strlen($user["user_email"]) > 0) {
            $res["done"]++;
            $res["list"]["email"] = true;
        } else {
            $res["remaining"]++;
        }
        if (isset($user["user_phone_international"]) && is_string($user["user_phone_international"]) && strlen($user["user_phone_international"]) > 0) {
            $res["done"]++;
            $res["list"]["phone"] = true;
        } else {
            $res["remaining"]++;
        }

        if (isset($user["user_city_id"]) && is_numeric($user["user_city_id"]) && $user["user_city_id"] > 0) {
            $res["done"]++;
            $res["list"]["city"] = true;
        } else {
            $res["remaining"]++;
        }
        if (isset($user["user_password"]) && is_string($user["user_password"]) && strlen($user["user_password"]) > 0) {
            $res["done"]++;
            $res["list"]["password"] = true;
        } else {
            $res["remaining"]++;
        }
        if (isset($user["user_firstname"]) && is_string($user["user_firstname"]) && strlen($user["user_firstname"]) > 0 && isset($user["user_lastname"]) && is_string($user["user_lastname"]) && strlen($user["user_lastname"]) > 0
        ) {
            $res["done"]++;
            $res["list"]["name"] = true;
        } else {
            $res["remaining"]++;
        }
        $res["total"] = $res["done"] + $res["remaining"];
        return $res;
}
function civicpower_check_token_user($token,$errorize=true) {
    $res = false;
    $user = sql_shift("
        SELECT
            usr_user.*,
            fdt_member.*,
            code_postal,
            nom_commune
        FROM usr_user
        LEFT JOIN geo_fr_cities ON city_id = user_city_id
        LEFT JOIN fdt_member ON member_user_id = user_id
        WHERE " . civicpower_hash_db(true, "user_salt", $_ENV['SALT_USER']) . " = '" . for_db($token) . "'
        AND user_active = '1' AND user_ban = '0'
    ");
    if (is_array($user) && count($user) > 0) {
        $res = $user;
    }
    if ($res===false) {
        if($errorize) {
            mapi_error("wrong_user_token", "Wrong user token, Please login", ["token" => $token]);
        }
    }else {
        $res["steps"] = civicpower_get_steps($user);
    }
    return $res;
}
function civicpower_inner_filter($view_public=true) {
    $res = [];
    $res[] = "INNER JOIN bal_filter ON ballot_id = bfilter_ballot_id  AND bfilter_active = '1'";
    $res[] = "INNER JOIN usr_user ON";
    $res[] = "(
        LENGTH(bfilter_email)
        AND bfilter_email LIKE user_email
    )";
    $res[] = "OR (
        LENGTH(bfilter_email_domain)>0
        AND bfilter_email_domain LIKE SUBSTRING(user_email, LOCATE('@', user_email) + 1)
    )";
    $res[] = "OR (
        (
            LENGTH(user_phone_dial)>0
            AND LENGTH(bfilter_phone_national) >0
            AND bfilter_phone_dial = user_phone_dial
            AND bfilter_phone_national = user_phone_national
        ) OR (
            LENGTH(bfilter_phone_international)
            AND bfilter_phone_international = user_phone_international
        )
    )";
    $res[] = "OR (
        bfilter_city_id>0
        AND bfilter_city_id= user_city_id
    )";
    if($view_public) {
        $res[] = "OR (
            bfilter_all = '1'
        )";
    }else{
        $res[] = "OR (
            bfilter_all = '1'
            AND ballot_asker_id IN (
                SELECT asker_id
                FROM ask_asker
                INNER JOIN usr_user ON user_id = asker_user_id  AND user_email = 'admin@civicpower.io' AND user_active = '1' AND user_ban = '0'
                WHERE asker_name LIKE 'civicpower'
            )
        )";
    }
    $res = implode(" ", $res);
    return $res;
}
function civicpower_get_ballot_list($user_token,$finish,$not_finish,$voted=null,$view_public=true) {
    if(!is_null($voted) && is_bool($voted)) {
        $voted_sql = "
            AND (
                ballot_id ".($voted?"IN":"NOT IN")." (
                    SELECT question_ballot_id
                    FROM vot_vote
                    INNER JOIN bal_option ON vote_option_id = option_id AND option_active = '1'
                    INNER JOIN bal_question ON option_question_id = question_id AND question_active = '1'
                    INNER JOIN usr_user ON user_id = vote_user_id AND user_active='1' AND user_ban='0'
                    WHERE ".civicpower_hash_db(true,"user_salt",$_ENV['SALT_USER'])." = '" . for_db($user_token) . "'
                    AND vote_active = '1'
                )
            )
        ";
    }else{
        $voted_sql="";
    }
    $finish_sql = $finish ? "
        AND (
            ballot_bstatus_id = '".for_db($_ENV['STATUS_BALLOT_VALIDE_TERMINE'])."'
            /*ballot_start + INTERVAL ballot_duration_second SECOND < NOW()*/
        )
    " : "";
    $not_finish_sql = $not_finish ? "
          AND  ballot_bstatus_id IN ('".for_db($_ENV['STATUS_BALLOT_VALIDE_EN_COURS'])."','".for_db($_ENV['STATUS_BALLOT_VALIDE_EN_ATTENTE'])."')
        /*AND NOW() < ballot_start + INTERVAL ballot_duration_second SECOND*/
    " : "";
    $ballot_list = sql($sql ="
        SELECT
            ".civicpower_hash_db(true,"ballot_id",$_ENV['SALT_BALLOT'])." AS ballot_token,
            ".civicpower_hash_db(true,"asker_id",$_ENV['SALT_ASKER'])." AS asker_token,
            bal_ballot.*,
            ask_type.*,
            asker_name,
            NOW() > ballot_start AS ballot_started,
            NOW() > ballot_start + INTERVAL ballot_duration_second SECOND AS ballot_finished,
            NOW() > ballot_start AND NOW() < ballot_start + INTERVAL ballot_duration_second SECOND AS ballot_running,
            ballot_start + INTERVAL ballot_duration_second SECOND AS ballot_end,
            DATE_FORMAT(ballot_start + INTERVAL ballot_duration_second SECOND,'%d/%m/%Y') AS ballot_end_date_fr,
            DATE_FORMAT(ballot_start + INTERVAL ballot_duration_second SECOND,'%HH%i') AS ballot_end_hourmin_fr,
            COUNT(DISTINCT vote_user_id) AS nb_participation
        FROM bal_ballot
        INNER JOIN ask_asker ON asker_id = ballot_asker_id
        INNER JOIN ask_type ON asker_astyp_id = astyp_id
        ".civicpower_inner_filter($view_public)."
        LEFT JOIN bal_question  ON question_ballot_id = ballot_id AND question_active = '1'
        LEFT JOIN bal_option    ON option_question_id = question_id AND option_active = '1'
        LEFT JOIN vot_vote      ON vote_option_id = option_id AND vote_active = '1'
        WHERE 1=1
        $finish_sql
        $not_finish_sql
        $voted_sql
        AND " . civicpower_hash_db(true, "user_salt", $_ENV['SALT_USER']) . " = '" . for_db($user_token) . "'
        AND ballot_active = '1'
        AND ballot_bstatus_id >= 10
        AND asker_active = '1'
        AND user_active = '1' AND user_ban = '0'
        GROUP BY ballot_id
        ORDER BY ballot_start DESC
    ");
    return $ballot_list;
}
function civicpower_get_ballot_info($ballot_token, $see_result, $user) {
    $ballot = sql_shift($sql = "
        SELECT
            bal_ballot.*,
            ask_type.*,
            user_firstname,
            user_lastname,
           " . civicpower_hash_db(true, "ballot_id", $_ENV['SALT_BALLOT']) . " AS ballot_token,
           " . civicpower_hash_db(true,"asker_id",$_ENV['SALT_ASKER']) . " AS asker_token,
            asker_name,
            NOW() > ballot_start AS ballot_started,
            NOW() > ballot_start + INTERVAL ballot_duration_second SECOND AS ballot_finished,
            NOW() > ballot_start AND NOW() < ballot_start + INTERVAL ballot_duration_second SECOND AS ballot_running,
            ballot_start + INTERVAL ballot_duration_second SECOND AS ballot_end,
            DATE_FORMAT(ballot_start + INTERVAL ballot_duration_second SECOND,'%d/%m/%Y') AS ballot_end_date_fr,
            DATE_FORMAT(ballot_start + INTERVAL ballot_duration_second SECOND,'%HH%i') AS ballot_end_hourmin_fr,
            COUNT(DISTINCT vote_user_id) AS nb_participation
        FROM bal_ballot
        LEFT JOIN ask_asker ON asker_id = ballot_asker_id AND asker_active = '1'
        INNER JOIN ask_type ON asker_astyp_id = astyp_id
        LEFT JOIN bal_question  ON question_ballot_id = ballot_id AND question_active = '1'
        LEFT JOIN bal_option    ON option_question_id = question_id AND option_active = '1'
        LEFT JOIN vot_vote      ON vote_option_id = option_id AND vote_active = '1'
        LEFT JOIN usr_user      ON user_id = asker_user_id AND user_active = '1' AND user_ban = '0'
        WHERE " . civicpower_hash_db(true, "ballot_id", $_ENV['SALT_BALLOT']) . " = '" . for_db($ballot_token) . "'
        AND ballot_active = '1'
        AND ballot_bstatus_id >= 10
        GROUP BY ballot_id
    ");
    if(!is_array($ballot) || count($ballot)==0){
        return null;
    }
    $ballot_id = $ballot["ballot_id"];
    $can_see_results = $ballot["ballot_see_results_live"]==1 || $ballot["ballot_finished"] == 1;
    if ($see_result) {
        $question_list = sql("
            SELECT
                bal_question.*,
                COUNT(DISTINCT vote_user_id) AS question_nb_vote
            FROM bal_question
            LEFT JOIN bal_option ON option_question_id = question_id AND option_active = '1'
            LEFT JOIN vot_vote ON vote_option_id = option_id AND vote_active = '1'
            WHERE question_ballot_id = '" . for_db($ballot_id) . "'
            AND question_active = '1'
            AND LENGTH(question_title)>0
            GROUP BY question_id
        ");
    } else {
        $question_list = sql("
            SELECT *
            FROM bal_question
            WHERE question_ballot_id = '" . for_db($ballot_id) . "'
            AND question_active = '1'
            AND LENGTH(question_title)>0
        ");
    }
    $question_ids = [];
    $ballot_user_has_voted = 0;
    if (is_array($question_list) && count($question_list) > 0) {
        foreach ($question_list as $k => $v) {
            $question_ids[] = $v["question_id"];
            $option_list = sql("
                SELECT *
                FROM bal_option
                WHERE option_question_id = '" . for_db($v["question_id"]) . "'
                AND LENGTH(option_title)>0
                AND option_active = '1'
                ORDER BY option_rank ASC
            ");
            $info = civicpower_inject_result($option_list, $user, $see_result,$can_see_results);
            $question_list[$k]["option_list"] = $option_list;
            if ($info["user_has_voted"]) {
                $question_list[$k]["question_user_has_voted"] = 1;
                $ballot_user_has_voted = 1;
            } else {
                $question_list[$k]["question_user_has_voted"] = 0;
            }
        }
    }
    
    $ballot["question_list"] = $question_list;
    $ballot["ballot_user_has_voted"] = $ballot_user_has_voted;
    return $ballot;
}
function civicpower_user_can_vote_option($user_id,$option_id) {
    $user_salt = civicpower_user_id_to_user_salt($user_id);
    $token = civicpower_hash_db(false,$user_salt,$_ENV['SALT_USER']);
    $ballot_list = civicpower_get_ballot_list($token,false,true);
    $ballot_id = sql_unique("
        SELECT question_ballot_id
        FROM bal_question
        INNER JOIN bal_option ON option_question_id = question_id
        WHERE option_id = '".for_db($option_id)."'
    ");
    $ids = [];
    foreach($ballot_list as $k => $v){
        $ids[] = $v['ballot_id'];
    }
    return in_array($ballot_id,$ids);
}
function civicpower_inject_result(&$option_list, $user, $see_result,$can_see_results) {
    $ids = [];
    $user_has_voted = false;
    $user_vote = [];
    foreach ($option_list as $k => $v) {
        $ids[] = $v['option_id'];
    }
    if (!is_null($user) && isset($user['user_id'])) {
        $user_vote_tmp = sql("
            SELECT *
            FROM vot_vote
            WHERE vote_user_id = '" . for_db($user['user_id']) . "'
            AND vote_option_id IN ('" . implode("','", $ids) . "')
            AND vote_active = '1'
        ");
        foreach ($user_vote_tmp as $k => $v) {
            $user_vote[$v["vote_option_id"]] = $v;
        }
    }
    $option_result_tmp = sql("
        SELECT
            option_id,
           ".($can_see_results?"COUNT(vote_user_id)":"'?'")." AS option_nb_vote
        FROM bal_option
        INNER JOIN vot_vote ON option_id = vote_option_id AND vote_active = '1'
        WHERE option_id IN ('" . implode("','", $ids) . "')
        AND option_active = '1'
        GROUP BY option_id
    ");
    $option_result = [];
    foreach ($option_result_tmp as $k => $v) {
        $option_result[$v["option_id"]] = $v;
    }
    if ($see_result) {
        foreach ($option_list as $k => $v) {
            $option_id = $v["option_id"];
            if (isset($option_result[$option_id])) {
                $stat = $option_result[$option_id];
                $option_list[$k]["option_nb_vote"] = $stat["option_nb_vote"];
            } else {
                $option_list[$k]["option_nb_vote"] = 0;
            }
        }
    }
    foreach ($option_list as $k => $v) {
        $option_id = $v["option_id"];
        if (isset($user_vote) && isset($user_vote[$option_id])) {
            $user_has_voted = true;
            $option_list[$k]["option_user_has_voted"] = 1;
        } else {
            $option_list[$k]["option_user_has_voted"] = 0;
        }
    }
    return ["user_has_voted" => $user_has_voted,];
}

function civicpower_user_salt_to_user_id($salt) {
    $user_id = intval(sql_unique("
        SELECT user_id
        FROM usr_user
        WHERE user_salt = '".for_db($salt)."'
    "));
    return $user_id;
}
function civicpower_user_id_to_user_salt($user_id) {
    $user_salt = sql_unique("
        SELECT user_salt
        FROM usr_user
        WHERE user_id = '".for_db($user_id)."'
    ");
    return $user_salt;
}
function civicpower_hash_db($sql_language, $string, $salt = "") {
    if ($sql_language) {
        return "SHA1(CONCAT($string,'" . for_db($_ENV['GLOBAL_SALT']) . "','" . for_db($salt) . "'))";
    } else {
        return sha1("" . $string . $_ENV['GLOBAL_SALT'] . $salt);
    }
}
/* 
 * @access      -
 * @param
                sql_language    -> do we want SQL in return?
                string          -> string we want to protect
                salt            -> users's salt
 * @author      C2
 * Purpose      store in BC
 * @roadmap
 *      2020/12 : born
 * @todo
 *      test
 */
function civicpower_hash_user($sql_language, $string, $salt = "") {
    if ($sql_language) {
        return "SHA1(CONCAT($string,'".for_db($_ENV['SALT_USER'])."'))";
    } else {
        return sha1("" . $string . $_ENV['SALT_USER']);
    }
}

function civicpower_send_email($email="",$subject="", $text="",$user_id="") {

    // Just in case
        if ( ($email=="") || ($subject=="") || ($text=="") )  { return FALSE; }

    // Custom test titles
        if($subject<>"") {
            if (!strpos($_SERVER["SERVER_NAME"], "-staging.civicpower.io") ){
                $subject = 'STAGING - '.$subject;
            }
            elseif (!strpos($_SERVER["SERVER_NAME"], "-demo.civicpower.io") ){
                $subject = 'DEMO - '.$subject;
            }
            elseif (
                (!strpos($_SERVER["SERVER_NAME"], "-dev.civicpower.io") )
                || (!strpos($_SERVER["SERVER_NAME"], "-local.civicpower.io") )
                || (!strpos($_SERVER["SERVER_NAME"], "-ftp.civicpower.io") )
            ){
                $subject = 'DEV - '.$subject;
            }
        }

    // Call internal SMS GW
        $post = "email=".$email
            ."&subject=".$subject
            ."&text=".$text
            ."&user_id=".$user_id;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$_ENV["INTERNAL_GW_URL"]."/GW_1.0/mail/");
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_VERBOSE,true);
        $result = curl_exec ($ch);
        $curlresponse = json_decode($result, true);
    // Return
        switch (true) {
            case (isset($curlresponse['res'])):
                return $curlresponse['res'];
                break;
            case (isset($curlresponse['parameter_missing'])):
                return FALSE;
                break;
            case (isset($curlresponse['quota'])):
                return FALSE;
                break;
            default:
                return FALSE;
                break;
        }
    
}

function civicpower_free_user_salt() {
    $salt   = sha1(uniqid().mt_rand(0,9999).time());
    $nb     = intval(sql_unique("
        SELECT COUNT(*) AS nb
        FROM usr_user
        WHERE user_salt = '".for_db($salt)."'
    "));
    if($nb==0){
        return $salt;
    }else{
        return civicpower_free_user_salt();
    }
}

/**
 * @access          public
 * @param           mobile_phone_number = target
                    text = content
                    sender = from
 * @return
 * @author          Hakim
 * @purpose         Send SMS with our GW,
                    allowing to change our provider anytime without any impact on the app
*/
function civicpower_send_sms($mobile_phone_number, $text, $user_id="", $sender = "Civicpower") {
    // Call internal SMS GW
        $post = "mobile_phone_number=".$mobile_phone_number
            ."&text=".$text
            ."&user_id=".$user_id
            ."&sender=".$sender;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$_ENV["INTERNAL_GW_URL"]."/GW_1.0/sms/");
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_VERBOSE,true);
        $result = curl_exec ($ch);
        $curlresponse = json_decode($result, true);
    // Return
        switch (true) {
            case (isset($curlresponse['res'])):
                return $curlresponse['res'];
                break;
            case (isset($curlresponse['parameter_missing'])):
                return FALSE;
                break;
            case (isset($curlresponse['quota'])):
                return FALSE;
                break;
            default:
                return FALSE;
                break;
        }
}
function civicpower_is_email($str) {
    return filter_var($str, FILTER_VALIDATE_EMAIL);
}
function civicpower_check_password($str) {
    $uppercase = "~[A-Z]~";
    $lowercase = "~[a-z]~";
    $letter = "~[a-zA-Z]~";
    $number = "~[0-9]~";
    $special = "~[!|@|#|$|%|^|&|*|(|)|-|_]~";
    $obj = [];
    $obj["result"] = true;
    if (strlen($str) < $_ENV['CP_PASSWORD_MIN_LENGTH']) {
        $obj["result"] = false;
        $obj["error"] = "Mot de passe trop court. ".$_ENV['CP_PASSWORD_MIN_LENGTH']." caractères minimum.";
        return $obj;
    }
    $nb_uppercase = 0;
    $nb_lowercase = 0;
    $nb_letter = 0;
    $nb_number = 0;
    $nb_special = 0;
    $tab = str_split($str);
    foreach ($tab as $k => $v) {
        if (preg_match($letter, $v)) {
            $nb_letter=1;
        } else if (preg_match($number, $v)) {
            $nb_number=1;
        } else if (preg_match($special, $v)) {
            $nb_special=1;
        }
    }
    $sum = $nb_letter + $nb_special + $nb_number;
    if ($sum<2) {
        $obj["result"] = false;
        $obj["error"] = "Mot de passe incorrect. Utilisez au moins ".$_ENV['CP_PASSWORD_MIN_LENGTH']." caractères avec des lettres, des chiffres et des symboles.";
    }
    return $obj;
}
function civicpower_international_phone($str) {
    $res = $str;
    if (preg_match("~^00~", $str)) {
        $res = preg_replace("~^00~", "+", $str);
    } else if (preg_match("~^0~", $str)) {
        $res = preg_replace("~^0~", "+33", $str);
    }
    return $res;
}
?>