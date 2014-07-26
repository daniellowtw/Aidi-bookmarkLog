<?php 
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