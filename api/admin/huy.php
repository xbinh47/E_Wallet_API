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

    $id = $data -> id;
    $sql = "SELECT * FROM users WHERE id = '$id'";
    $user = executeResult($sql, true);
    
    if (!empty($user)) {
        $sql = "UPDATE users SET idState= '03' WHERE id = '$id'";
        execute($sql);
        die(json_encode(array('code' => 0, 'data' => 'Success')));
    }
    else {
        die(json_encode(array('code' => 1, 'data' => 'User not found')));
    }        

?>