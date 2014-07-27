<?php

/* Returns the small hash of a string, using RFC 4648 base64url format
eg. smallHash('20111006_131924') --> yZH23w
Small hashes:
- are unique (well, as unique as crc32, at last)
- are always 6 characters long.
- only use the following characters: a-z A-Z 0-9 - _ @
- are NOT cryptographically secure (they CAN be forged)
In Shaarli, they are used as a tinyurl-like link to individual entries.
 */
function smallHash($text) {
	$t = rtrim(base64_encode(hash('crc32', $text, true)), '=');
	return strtr($t, '+/', '-_');
}

// In a string, converts urls to clickable links.
// Function inspired from http://www.php.net/manual/en/function.preg-replace.php#85722
function text2clickable($url) {
	$redir = empty($GLOBALS['redirector'])?'':$GLOBALS['redirector'];
	return preg_replace('!(((?:https?|ftp|file)://|apt:|magnet:)\S+[[:alnum:]]/?)!si', '<a href="'.$redir.'$1" rel="nofollow">$1</a>', $url);
}

// This function inserts &nbsp; where relevant so that multiple spaces are properly displayed in HTML
// even in the absence of <pre>  (This is used in description to keep text formatting)
function keepMultipleSpaces($text) {
	return str_replace('  ', ' &nbsp;', $text);

}
// ------------------------------------------------------------------------------------------
// Sniff browser language to display dates in the right format automatically.
// (Note that is may not work on your server if the corresponding local is not installed.)
function autoLocale() {
	$loc = 'en_US';// Default if browser does not send HTTP_ACCEPT_LANGUAGE
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))// eg. "fr,fr-fr;q=0.8,en;q=0.5,en-us;q=0.3"
	{// (It's a bit crude, but it works very well. Prefered language is always presented first.)
		if (preg_match('/([a-z]{2}(-[a-z]{2})?)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {$loc = $matches[1];
		}
	}

	setlocale(LC_TIME, $loc);// LC_TIME = Set local for date/time format only.
}

// ------------------------------------------------------------------------------------------
// Misc utility functions:

// Returns the server URL (including port and http/https), without path.
// eg. "http://myserver.com:8080"
// You can append $_SERVER['SCRIPT_NAME'] to get the current script URL.
function serverUrl() {
	$https      = (!empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on')) || $_SERVER["SERVER_PORT"] == '443';// HTTPS detection.
	$serverport = ($_SERVER["SERVER_PORT"] == '80' || ($https && $_SERVER["SERVER_PORT"] == '443')?'':':'.$_SERVER["SERVER_PORT"]);
	return 'http'.($https?'s':'').'://'.$_SERVER['HTTP_HOST'].$serverport;
}

// Returns the absolute URL of current script, without the query.
// (eg. http://sebsauvage.net/links/)
function indexUrl() {
	$scriptname = $_SERVER["SCRIPT_NAME"];
	// If the script is named 'index.php', we remove it (for better looking URLs,
	// eg. http://mysite.com/shaarli/?abcde instead of http://mysite.com/shaarli/index.php?abcde)
	if (endswith($scriptname, 'index.php')) {$scriptname = substr($scriptname, 0, strlen($scriptname)-9);
	}

	return serverUrl().$scriptname;
}

// Returns the absolute URL of current script, WITH the query.
// (eg. http://sebsauvage.net/links/?toto=titi&spamspamspam=humbug)
function pageUrl() {
	return indexUrl().(!empty($_SERVER["QUERY_STRING"])?'?'.$_SERVER["QUERY_STRING"]:'');
}

// Convert post_max_size/upload_max_filesize (eg.'16M') parameters to bytes.
function return_bytes($val) {
	$val  = trim($val);
	$last = strtolower($val[strlen($val)-1]);
	switch ($last) {
		case 'g':$val *= 1024;
		case 'm':$val *= 1024;
		case 'k':$val *= 1024;
	}
	return $val;
}

// Try to determine max file size for uploads (POST).
// Returns an integer (in bytes)
function getMaxFileSize() {
	$size1 = return_bytes(ini_get('post_max_size'));
	$size2 = return_bytes(ini_get('upload_max_filesize'));
	// Return the smaller of two:
	$maxsize = min($size1, $size2);
	// FIXME: Then convert back to readable notations ? (eg. 2M instead of 2000000)
	return $maxsize;
}

// Tells if a string start with a substring or not.
function startsWith($haystack, $needle, $case = true) {
	if ($case) {return (strcmp(substr($haystack, 0, strlen($needle)), $needle) === 0);}
	return (strcasecmp(substr($haystack, 0, strlen($needle)), $needle) === 0);
}

// Tells if a string ends with a substring or not.
function endsWith($haystack, $needle, $case = true) {
	if ($case) {return (strcmp(substr($haystack, strlen($haystack)-strlen($needle)), $needle) === 0);}
	return (strcasecmp(substr($haystack, strlen($haystack)-strlen($needle)), $needle) === 0);
}

/*  Converts a linkdate time (YYYYMMDD_HHMMSS) of an article to a timestamp (Unix epoch)
(used to build the ADD_DATE attribute in Netscape-bookmarks file)
PS: I could have used strptime(), but it does not exist on Windows. I'm too kind. */
function linkdate2timestamp($linkdate) {
	$Y = $M = $D = $h = $m = $s = 0;
	$r = sscanf($linkdate, '%4d%2d%2d_%2d%2d%2d', $Y, $M, $D, $h, $m, $s);
	return mktime($h, $m, $s, $M, $D, $Y);
}

/*  Converts a linkdate time (YYYYMMDD_HHMMSS) of an article to a RFC822 date.
(used to build the pubDate attribute in RSS feed.)  */
function linkdate2rfc822($linkdate) {
	return date('r', linkdate2timestamp($linkdate));// 'r' is for RFC822 date format.
}

/*  Converts a linkdate time (YYYYMMDD_HHMMSS) of an article to a ISO 8601 date.
(used to build the updated tags in ATOM feed.)  */
function linkdate2iso8601($linkdate) {
	return date('c', linkdate2timestamp($linkdate));// 'c' is for ISO 8601 date format.
}

/*  Converts a linkdate time (YYYYMMDD_HHMMSS) of an article to a localized date format.
(used to display link date on screen)
The date format is automatically chosen according to locale/languages sniffed from browser headers (see autoLocale()). */
function linkdate2locale($linkdate) {
	return utf8_encode(strftime('%c', linkdate2timestamp($linkdate)));// %c is for automatic date format according to locale.
	// Note that if you use a local which is not installed on your webserver,
	// the date will not be displayed in the chosen locale, but probably in US notation.
}

// Parse HTTP response headers and return an associative array.
function http_parse_headers_shaarli($headers) {
	$res = array();
	foreach ($headers as $header) {
		$i = strpos($header, ': ');
		if ($i !== false) {
			$key       = substr($header, 0, $i);
			$value     = substr($header, $i+2, strlen($header)-$i-2);
			$res[$key] = $value;
		}
	}
	return $res;
}

/* GET an URL.
Input: $url : url to get (http://...)
$timeout : Network timeout (will wait this many seconds for an anwser before giving up).
Output: An array.  [0] = HTTP status message (eg. "HTTP/1.1 200 OK") or error message
[1] = associative array containing HTTP response headers (eg. echo getHTTP($url)[1]['Content-Type'])
[2] = data
Example: list($httpstatus,$headers,$data) = getHTTP('http://sebauvage.net/');
if (strpos($httpstatus,'200 OK')!==false)
echo 'Data type: '.htmlspecialchars($headers['Content-Type']);
else
echo 'There was an error: '.htmlspecialchars($httpstatus)
 */
function getHTTP($url, $timeout = 30) {
	try
	{
		$options = array('http' => array('method' => 'GET', 'timeout' => $timeout, 'user_agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:23.0) Gecko/20100101 Firefox/23.0'));
		// Force network timeout
		$context = stream_context_create($options);
		$data    = file_get_contents($url, false, $context, -1, 4000000);// We download at most 4 Mb from source.
		if (!$data) {return array('HTTP Error', array(), '');}
		$httpStatus      = $http_response_header[0];// eg. "HTTP/1.1 200 OK"
		$responseHeaders = http_parse_headers_shaarli($http_response_header);
		return array($httpStatus, $responseHeaders, $data);
	}
	 catch (Exception $e)// getHTTP *can* fail silentely (we don't care if the title cannot be fetched)
	{
		return array($e->getMessage(), '', '');
	}
}

// Extract title from an HTML document.
// (Returns an empty string if not found.)
function html_extract_title($html) {
	return preg_match('!<title>(.*?)</title>!is', $html, $matches)?trim(str_replace("\n", ' ', $matches[1])):'';
}

function convertFilteredToDays($filtered) {
	$days  = array();
	$days2 = array();
	foreach ($filtered as $link) {
		$yearmonthday = date("Ymd", $link['linkdate']/1000);
		if (isset($days[$yearmonthday])) {
			array_push($days[$yearmonthday]["links"], $link);
		} else {
			$days[$yearmonthday] = array('show' => 'true', 'links' => array(), 'date' => $yearmonthday);
			array_push($days[$yearmonthday]["links"], $link);
		}
		$days2 = array_values($days);
	}
	return $days2;
}
?>