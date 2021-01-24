<?php
error_log("AAA");
error_log(vardump($_POST["token"]));
if(mapi_post_mandatory("token","id")) {
// Call for IB.io if auth0 token is okay
	if (gpost("token")==get_token_blockchain()['token']) {
		$curl = curl_init();
		curl_setopt_array($curl, [
		  CURLOPT_URL 			=> $_ENV['CIVICPOWER_INBLOCKS_URL'].str_replace("//", "/", $_ENV['CIVICPOWER_INBLOCKS_PROJECT']."/blockchains/".$_ENV['CIVICPOWER_INBLOCKS_BLOCKCHAIN']."/precedence/records/".gpost("id")),
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING 		=> "",
		  CURLOPT_MAXREDIRS 	=> 10,
		  CURLOPT_TIMEOUT 		=> 30,
		  CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_POSTFIELDS 	=> "",
		  CURLOPT_HTTPHEADER 	=> [
		     "Authorization: Bearer ".get_token_blockchain()['token']
		  ],
		]);
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
			// Mail
				debugMailer( array('variables' => get_defined_vars(),'subject' => __FUNCTION__." can't read blockchain") );
			// Return FALSE
				mapi_error("error", $err, "no_entry");
		} else {
			echo $response;
		}
	}
	else { mapi_error("error", "try /get_token_blockchain", "no_grant"); }
}
?>