<?php
    session_start();
    require_once('../../db/dbhelper.php');
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
    
    if (!property_exists($data,'email')){
        die(json_encode(array('code' => 1, 'data' => 'Missing Paramenter')));
    }

    $email = $data->email;
    
    if (property_exists($data,'phone')) {
        $phone = $data->phone;

        $user = checkUserByEmailAndPhone($email, $phone);

        if (empty($user)) {
            die(json_encode(array('code' => 1, 'data' => 'User can not found!')));
        }
        
        $_SESSION['forgetPass'] = $user['email'];
        
    }
    else {
        if (!checkUser($email)){
            die(json_encode(array('code' => 1, 'data' => 'Email của User không tồn tại')));
        }
    }

    $sql = "SELECT * FROM `otp` WHERE `email` = '$email'";
    $otp = executeResult($sql, true);
    if(empty($otp)){
        $otp_pass=rand(100000, 999999);
        $sql = "INSERT INTO `otp` (`email`, `otp_pass`,`otp_timestamp`) VALUES ('$email', '$otp_pass','".getNowDateTime()."')";
        execute($sql);
        sendOTP($email, $otp_pass);
        echo json_encode(array('code' => 0, 'data' => 'Đã gửi mã OTP vào email của bạn'));
    }else{
        $otp_pass=rand(100000, 999999);
        $sql = "UPDATE `otp` SET `otp_pass` = '$otp_pass', `otp_timestamp` = '".getNowDateTime()."' WHERE `email` = '$email'";
        execute($sql);
        sendOTP($email, $otp_pass);
        echo json_encode(array('code' => 0, 'data' => 'Đã gửi mã OTP vào email của bạn'));
    }


?>