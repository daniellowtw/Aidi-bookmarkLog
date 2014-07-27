<?php 
    header('Access-Control-Allow-Origin: *');  //I have also tried the * wildcard and get the same response
    header("Access-Control-Allow-Credentials: true");
    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 1000');
    header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

require 'aidi.php';
$allowed_operations = array('count', 'key', 'current', 'next', 'filterTags', 'filterDay', 'filterSmallHash', 'allTags', 'days', 'filterFulltext', 'deleteLink','getLinkFromDate','checkSecret','changeSecret');
$db = new linkdb(1);
// var_dump(get_class_methods ($db));
// var_dump($db);
if (!empty($_GET)) {
	foreach ($_GET as $key => $value){
		// var_dump($_GET);
		if (in_array($key, $allowed_operations)) {
			echo json_encode($db->$key($value));
		}
	}
}



 ?>