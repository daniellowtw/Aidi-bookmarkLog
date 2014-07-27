<?php

// We need to to allow access control for posting
header('Access-Control-Allow-Origin: *');//I have also tried the * wildcard and get the same response
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

require 'aidi.php';
$LINKSDB             = new linkdb(1);
$response            = array();
$response['message'] = "No post data";

if (!empty($_POST)) {
	$expected = array('linkdate', 'url', 'tags');
	foreach ($expected as $key) {
		if (!in_array($key, array_keys($_POST))) {
			echo "ILLEGAL POST Fields";
			var_dump($key, array_keys($_POST));
			die();
		}
	}

	$description = isset($_POST['description'])?$_POST['description']:"";

	$linkdate = $_POST['linkdate'];
	$url      = trim($_POST['url']);
	$tags     = trim(preg_replace('/\s\s+/', ' ', $_POST['tags']));// Remove multiple spaces.
	if (!startsWith($url, 'http:') && !startsWith($url, 'https:') && !startsWith($url, 'ftp:') && !startsWith($url, 'magnet:') && !startsWith($url, '?')) {
		$url = 'http://'.$url;
	}

	$link = array('title' => trim($_POST['title']), 'url' => $url, 'description' => trim($description), 'private' => (isset($_POST['private'])?1:0),
		'linkdate'           => $linkdate, 'tags'           => str_replace(',', ' ', $tags));
	if ($link['title'] == '') {$link['title'] = $link['url'];
	}
	// If title is empty, use the URL as title.
	$LINKSDB[$linkdate] = $link;
	$LINKSDB->savedb();// save to disk
	$response['message'] = "Saved successfully";

}

echo json_encode($response);

?>