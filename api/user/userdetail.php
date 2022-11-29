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
    
    if (!property_exists($data,'phone') && !property_exists($data,'email')) {
        die(json_encode(array('code' => 1, 'data' => 'Missing Paramenter')));
    }

    if(property_exists($data,'phone')){
        $phone = $data->phone;
        $sql = "SELECT `email`,`balance`,`phone`,`birthday`,`address`,`front`,`back`,`createAt`,`updateAt` FROM `users` JOIN `state` ON `users`.`idState` = `state`.`idState`  AND `users`.`phone` = '$phone'";
        $users = executeResult($sql, true);
        if (!empty($users)) {
            // $sql = "SELECT `phone` FROM `users` WHERE `email` = '$email'";
            // $phoneSender = executeResult($sql, true);
            // if($phoneSender['phone'] == $phone){
            //     die(json_encode(array('code' => 1, 'data' => 'Không thể chuyển tiền cho chính mình')));
            // }
            // if ($users['idState'] ==2 ){
            //     die(json_encode(array('code' => 0, 'data' => $users)));
            // }else{
            //     die(json_encode(array('code' => 1, 'data' => 'Người dùng chưa được xác minh tài khoản')));
            // }
            die(json_encode(array('code' => 0, 'data' => $users)));
        } else {
            die(json_encode(array('code' => 1, 'data' => "Không có người dùng này trong hệ thống")));
        }
    }else{
        $email = $data->email;
        $sql= "SELECT * FROM `users` JOIN `state` ON `users`.`idState` = `state`.`idState` WHERE `users`.`email` = '$email'";
        $user = executeResult($sql, true);
        if (!empty($user)) {
            die(json_encode(array('code' => 0, 'data' => $user)));
        } else {
            die(json_encode(array('code' => 1, 'data' => [])));
        }
    }
    
?>