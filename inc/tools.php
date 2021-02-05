<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$no_fw = true;
$disable_https_redirect = true;
$no_session = true;
require_once dirname(__FILE__) . '/fw/tools.php';
require_once dirname(__FILE__) . '/tools_civicpower.php';
function mapi_get_mandatory() {
    return mapi_something_mandatory($_GET, func_get_args(), "_GET");
}
function mapi_post_mandatory() {
    return mapi_something_mandatory($_POST, func_get_args(), "_POST");
}
function mapi_request_mandatory() {
    return mapi_something_mandatory($_REQUEST, func_get_args(), "_REQUEST");
}
function mapi_something_mandatory($tab="", $params="", $tabname="") {
    if (is_array($params) && count($params) > 0) {
        foreach ($params as $k => $v) {
            if (isset($tab[$v])) {
                unset($params[$k]);
            }
        }
        if (!empty($params)) {
            mapi_error("mandatory_params", "mandatory params not found in " . $tabname . ": " . implode(',', $params));
            return false;
        }
    }
    return true;
}
function mapi_error($code, $message, $data = null) {
    $res = mapi_standard_response();
    $res['status'] = 'error';
    $res['code'] = $code;
    $res['message'] = $message;
    if (!is_null($data)) {
        civicpower_clear_data($data);
        $res['data'] = $data;
    }
    mapi_show_json($res);
}
function mapi_success($code, $message, $data = null) {
    $res = mapi_standard_response();
    $res['code'] = $code;
    $res['message'] = $message;
    if (!is_null($data)) {
        civicpower_clear_data($data);
        $res['data'] = $data;
    }
    mapi_show_json($res);
}
function mapi_standard_response() {
    $res = ['status' => 'success', 'code' => '', 'message' => '', 'data' => '',];
    return $res;
}
function mapi_show_json($data) {
    $json = json_encode($data, JSON_PRETTY_PRINT);
    echo $json;
}
function mapi_assert_email_domain_accepted($email) {
    $parts = explode("@", $email);
    $domain = array_pop($parts);
    $nb = intval(sql_unique("
        SELECT COUNT(*) AS nb
        FROM sys_mail_exclude
        WHERE mex_domain LIKE '" . for_db($domain) . "'
    "));
    if ($nb > 0) {
        mapi_error("email_domain_banned", "Le domaine d'email $domain n'est pas accepté");
        return false;
    }
    return true;
}
function mapi_assert_french_mobile($phone_number_international) {
    if (preg_match("~^\+33~", $phone_number_international)) {
        if (!preg_match("~\+33[67]~", $phone_number_international)) {
            mapi_error("not_mobile", "Ce numéro n'est pas un numéro de mobile");
            return false;
        }
    }
    return true;
}
function mapi_assert_user_not_exist($email_or_phone, $exclude_id = null) {
    $exclude_sql = "";
    if (!is_null($exclude_id)) {
        $exclude_sql = "
            AND user_id != '" . for_db($exclude_id) . "'
        ";
    }
    $nb = intval(sql_unique("
        SELECT COUNT(*) AS nb
        FROM usr_user
        WHERE (
            (user_email = '" . for_db($email_or_phone) . "')
            OR (user_phone_international = '" . for_db($email_or_phone) . "')
        )
        $exclude_sql
        AND user_active = '1' AND user_ban = '0'
    "));
    if ($nb > 0) {
        mapi_error("user_already_exists", "Un utilisateur existe déja avec cet identifiant");
        return false;
    }
    return true;
}

/* 
 * @access      -
 * @param       -
 * @author      C2
 * Purpose      time
 * @roadmap     2020/12 : born
 */
function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

/* 
 * @access      -
 * @param       content & logger file
 * @author      C2
 * Purpose      debug
 * @roadmap     2020/12 : born
 */
function vardump($message="",$log=""){
    if ($message<>"") {
        // Default
            if (!$log) { 
                if ( isset($_ENV['LOG_ERROR']) && ($_ENV['LOG_ERROR']==TRUE) ) {
                    $log = $_ENV['LOG_ERROR'];
                }
                else { $log = '/var/log/php-errors.log'; }
            }
        // Echo DUMP
            error_log(strftime('%Y-%m-%d %I:%M %P', strtotime('now')));
            error_log("********* data *********\n", 3, $log);
            ob_start();                    // start buffer capture
            var_dump( $message );           // dump the values
            $contents = ob_get_contents(); // put the buffer into a variable
            ob_end_clean();                // end capture
            error_log( $contents, 3, $log); 
            error_log("********* /data *********\n", 3, $log);
    }
}


/* 
 * @param       various mail config
 * @author      C2
 * Purpose      verify the data format we want to store
 * @roadmap     2020/12 : born
 */
function debugMailer ( $arguments = array('') ) {
    if ( isset($_ENV['MAIL_DEBUG']) && ($_ENV['MAIL_DEBUG']==TRUE) ) {
        // Just in case
            if (!isset($arguments['message'])||($arguments['message'])=="") {
                $arguments['message'] = 
                 "*** \ ***"
                ."function : ".(explode('?', explode('/', trim($_SERVER['REQUEST_URI'],".php"))[1]))[0]
                ."<BR>"
                ."script : ".$_SERVER['PHP_SELF']
                ."arguments : ".$_SERVER['PHP_SELF']
                ."<BR>"
                ."time : ".strftime('%Y-%m-%d %I:%M %P', strtotime('now'))
                ."<BR>"
                ."*** / ***";
                foreach ($_SERVER as $arg) {
                    $arguments['message'] .= $arg.' :'.$_SERVER[$arg]."<BR>";
                }
                $arguments['message'] .= "*** \ ***";
            }
            if (!isset($arguments['subject'])||($arguments['subject'])=="") {
                $arguments['subject'] = "Civicpower Issue";
            }
        // Create email
            $mail               =   new PHPMailer;
            // C2 2018/11
                $mail->CharSet = 'UTF-8';
                $mail->ContentType = 'text/html';
            $mail->IsSMTP();
            // Debug level (2 full info)
                $mail->SMTPDebug    =   0;
            // SMTP auth
                $mail->SMTPAuth     =   true;
                $mail->SMTPSecure   =   'tls';
                $mail->Host         =   'mail.gandi.net';
                $mail->Port         =   587;
                $mail->Username     =   ( ( isset($_ENV['MAIL_FROM_SMTPACCOUNT_DEBUG'])&&($_ENV['MAIL_FROM_SMTPACCOUNT_DEBUG']<>"") ) ? $_ENV['MAIL_FROM_SMTPACCOUNT_DEBUG'] : "");
                $mail->Password     =   ( ( isset($_ENV['MAIL_TO_DEBUG_PASSWORD'])&&($_ENV['MAIL_TO_DEBUG_PASSWORD']<>"") ) ? $_ENV['MAIL_TO_DEBUG_PASSWORD'] : "");
            // From
                $mail->setFrom( ( ( isset($_ENV['MAIL_FROM_DEBUG'])&&($_ENV['MAIL_FROM_DEBUG']<>"") ) ? $_ENV['MAIL_FROM_DEBUG'] : "") );
            // To
                $mail->addAddress( ( ( isset($_ENV['MAIL_TO_DEBUG'])&&($_ENV['MAIL_TO_DEBUG']<>"") ) ? $_ENV['MAIL_TO_DEBUG'] : "") );
            // Subject
                $mail->Subject      =   utf8_decode($arguments['subject']);
            // Body
                $mail->AltBody      =   strip_tags( str_replace(array('<br>','<br/>','<br />'), "\r\n", $arguments['message'] ) );
            // Message
                $mail->MsgHTML($arguments['message']);
        // Do we send?
            $result = $mail->send();
            if ($mail->ErrorInfo) { return FALSE; }
            else { return TRUE; }                   
    }
}
?>
