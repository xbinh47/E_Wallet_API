<?php
    require_once('../../db/dbhelper.php');
    if ($_SERVER['REQUEST_METHOD'] != 'POST'){
        die(json_encode(array('code' => 4, 'data' => 'Only POST method is supported')));
    }

    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if ($contentType !== "application/json") {
        die(json_encode(array('code' => 4, 'data' => 'Content-Type is not set as "application/json"')));
    }
    $content = file_get_contents('php://input');
    
    $data=json_decode($content);
    
    
    if(is_null($data)){
        die(json_encode(array('code' => 2, 'data' => 'Only json is supported')));
    }
    
    if (!property_exists($data,'id')) {
        die(json_encode(array('code' => 1, 'data' => 'Missing Paramenter')));
    }
    
    $id = $data->id;
    $sql= "SELECT * FROM `users` WHERE `id` = '$id'";
    $user = executeResult($sql, true);
    if (!empty($user)) {
        die(json_encode(array('code' => 0, 'data' => $user)));
    } else {
        die(json_encode(array('code' => 1, 'data' => [])));
    }
?>