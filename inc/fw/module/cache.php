<?php
if(isset($_GET[mode]) && isset($_GET[md5])){
	define(GZ_FILE,dirname(__FILE__).'/../../tmp/'.$_GET[md5]);
	if(file_exists(GZ_FILE) && is_file(GZ_FILE)){
		$etag=$_GET[md5];
		header("ETag: " . $etag, TRUE);
		$modified = gmdate("D, d M Y H:i:s \G\M\T");
		$expires = gmdate("D, d M Y H:i:s \G\M\T", time() + (24*3600*365*10));
		if (!stristr($_SERVER["HTTP_USER_AGENT"], "msie")) {
			header("Vary: Accept-Encoding", TRUE);
		}
		header("Cache-Control: public,must-revalidate", TRUE);
		if (isset ($_SERVER["HTTP_IF_NONE_MATCH"]) && $_SERVER["HTTP_IF_NONE_MATCH"] == $etag && !isset ($_SERVER["HTTP_CACHE_CONTROL"])) {
			header("HTTP/1.1 304 Not Modified", TRUE, 304);
		}
		elseif (isset ($_SERVER["HTTP_IF_MODIFIED_SINCE"]) && $_SERVER["HTTP_IF_MODIFIED_SINCE"] < $expires && !isset ($_SERVER["HTTP_CACHE_CONTROL"])) {
			header("HTTP/1.1 304 Not Modified", TRUE, 304);
		} else {
			header("Last-Modified: " . $modified, TRUE);
			header("Expires: " . $expires, TRUE);
			/*
			$length = ob_get_length();
			header("Content-Length: " . $length, TRUE);
			*/
		}

		if($_GET[mode]==css){
			header("Content-type: text/css; charset: UTF-8");
		}elseif($_GET[mode]==js){
			header("Content-type: text/javascript; charset: UTF-8");
		}
		if (@readgzfile(GZ_FILE)){
			exit; // Attempt to decompress and read gzip file
		}else{
			ini_set('zlib.output_compression', 'On');
			ob_start(write_gzip);
			@readfile(GZ_FILE);
			ob_end_flush();
		}
	}
}

// Callback function for ob_start
function write_gzip($buffer){
	// Take this opportunity to remove some padding
	//$buffer = preg_replace('#/\*[^*]*\*/#', '', $buffer); // Comments
	//$buffer = preg_replace('/[\t\r\n]+/', '', $buffer); // Tabs and line feeds
	//$buffer = preg_replace('/:\s+/', ':', $buffer); // Space before :
	//$buffer = preg_replace('/;\s*}/', '}', $buffer); // Last ;
	$gz = gzopen(GZ_FILE, 'w9'); // 9 for maximum compression
	gzwrite($gz, $buffer);
	gzclose($gz);
	return $buffer;
}
?>