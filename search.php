<?php
require_once("../../config.php");
//$fid = optional_param('fid', 0, PARAM_INT);					// id department

// $searchq = $_GET['name'];
$search = required_param('name', PARAM_RAW);

// header('Content-type: application/json; charset=utf-8');

// $ousers = $DB->get_records_sql('SELECT id, lastname, firstname FROM {user} WHERE lastname LIKE "%'.addslashes($search).'%"');
$ousers = get_records_sql('SELECT id, lastname, firstname FROM mdl_user WHERE lastname LIKE "%'.addslashes($search).'%"');

$users = array();
foreach ($ousers as $ouser) {
    $users[$ouser->id] = fullname($ouser);    
}
//$userselector = new $classname($name, $options);
// $users = $userselector->find_users($searchq);

echo json_encode(array('results' => $users));
/*
foreach($getName as $g) {
    echo $g->lastname.'<br/>';
}
*/    
?>