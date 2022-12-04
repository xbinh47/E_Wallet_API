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
    
    if (!property_exists($data,'idState')) {
        die(json_encode(array('code' => 1, 'data' => 'Missing Paramenter')));
    }
    
    $idState = $data->idState;

    if($idState == 0){
        $sql = "SELECT * FROM `users` WHERE `idState` = 0 ORDER BY `createAT` DESC";
        $users = executeResult($sql,false);
        if (!empty($users)) {
            die(json_encode(array('code' => 0, 'data' => $users)));
        }else{
            die(json_encode(array('code' => 1, 'data' => [])));
        }

    }else if($idState == 2){
        $sql = "SELECT * FROM `users` JOIN `times_login` ON `users`.`id` = `times_login`.`id` WHERE `users`.`idState` = 2 ORDER BY `times_login`.`datelock` DESC";
        $users = executeResult($sql,false);
        if (!empty($users)) {
            die(json_encode(array('code' => 0, 'data' => $users)));
        }else{
            die(json_encode(array('code' => 1, 'data' => [])));
        }
    }
    else{
        $sql = "SELECT * FROM `users` WHERE `idState` = '$idState' ORDER BY `createAT` DESC";
        $users = executeResult($sql, false);
        if (!empty($users)) {
            die(json_encode(array('code' => 0, 'data' => $users)));
        } else {
            die(json_encode(array('code' => 1, 'data' => [])));
        }
    }
?>