<?php
    require_once('services.php');
    date_default_timezone_set('Asia/Ho_Chi_Minh');

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
    
    if (!property_exists($data,'email') || !property_exists($data,'otp_code')){
        die(json_encode(array('code' => 1, 'data' => 'Missing Paramenter')));
    }

    $email = $data->email;
    $otp_code = $data->otp_code;

    if (!checkUser($email)){
        die(json_encode(array('code' => 1, 'data' => 'Email của User không tồn tại')));
    }
    
    $sql = "SELECT * FROM `otp` WHERE `email` = '$email'";
    $otp = executeResult($sql, true);
    if(!$otp){
        die(json_encode(array('code' => 1, 'data' => 'Email này không có mã OTP')));
    }else{
        $otp_timestamp = $otp['otp_timestamp'];
        $expire_time = date ("Y-m-d H:i:s", strtotime ($otp_timestamp ."+60 seconds"));
        if (strtotime(getNowDateTime()) > strtotime($expire_time)){
            $sql = "DELETE FROM `otp` WHERE `email` = '$email'";
            execute($sql);
            die(json_encode(array('code' => 1, 'data' => 'Mã OTP đã hết hạn')));
        }
        else if($otp_code != $otp['otp_pass']){
            die(json_encode(array('code' => 1, 'data' => 'Mã OTP không đúng')));
        }else{
            $sql = "DELETE FROM `otp` WHERE `email` = '$email'";
            execute($sql);
            echo json_encode(array('code' => 0, 'data' => 'OTP đúng'));
        }
    }
?>