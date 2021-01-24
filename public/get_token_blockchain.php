<?php
if ( ($dbHash = get_token_blockchain()) <> FALSE ) {
	// Something in DB?
		if ($dbHash['count']<1) {
			// Get
				$curl = curl_init();
				curl_setopt_array($curl, [
					CURLOPT_URL 			=> $_ENV['CIVICPOWER_INBLOCKS_AUTH_URL'],
					CURLOPT_RETURNTRANSFER 	=> true,
					CURLOPT_ENCODING 		=> "",
					CURLOPT_MAXREDIRS 		=> 10,
					CURLOPT_TIMEOUT 		=> 30,
					CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST 	=> "POST",
					CURLOPT_POSTFIELDS 		=> "grant_type=client_credentials&client_id=J5ru8HCjijJxOWv6hSQj7m5PvO4Bu7Rr&client_secret=Ph62kYUtX-NZGew_I7eGWvQNWI3B5OkwhJTFlL3nWQkZkr6qeG7ngfPZMytLMMg1&audience=/v1/lum9gqkn",
					CURLOPT_HTTPHEADER 		=> [
				    	"content-type: application/x-www-form-urlencoded"
				    ],
				]);
				$response = curl_exec($curl);
				$err = curl_error($curl);
				if ($err) {
					// Mail
						debugMailer( array('variables' => get_defined_vars(),'subject' => __FUNCTION__." can't grab AUTH0 token") );
					// Return FALSE
						mapi_error("error", $err, "no_grant");
				} else {
					if ( ( $dbDo = sql(
						"INSERT INTO `".$_ENV['MYSQL_BASE_BLOCKCHAIN']."`.`auth0_token` (`token`, `date`) VALUES ('".
						json_decode($response, TRUE)['access_token']
						."', CURRENT_TIMESTAMP);"
			    	) ) == FALSE ) { mapi_error("error", "write_db"); }
					else { 
						mapi_success("200", "token", json_decode($response, TRUE)['access_token']);
					}
				}
		}
		else {
			mapi_success("200", "token", $dbHash['token']);
		}
}
else { mapi_error("error", "no_db"); }
?>