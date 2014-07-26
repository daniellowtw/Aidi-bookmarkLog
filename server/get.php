<?php 
header('Access-Control-Allow-Origin: *');  //I have also tried the * wildcard and get the same response
    
require 'aidi.php';
$LINKSDB = new linkdb(1);
// $response = array();
// $response['message'] = "No post data";
// var_dump($LINKSDB->days);

// krsort($LINKSDB->days);
// var_dump($LINKSDB->days);
echo json_encode($LINKSDB->days);
// echo json_encode($LINKSDB->links);
 ?>