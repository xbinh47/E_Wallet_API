<?php
    require_once('../../db/dbhelper.php');
    require_once('../../utils/utility.php');
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");

    date_default_timezone_set('Asia/Ho_Chi_Minh');

    function loginApp($phone, $password){
        $hashPass = md5Security($password);
        $sql = "select * from `users` where `phone` = '$phone' and `password` = '$hashPass'";
        $user = executeResult($sql, true);
        $sql = "select * from `users` where `phone` = '$phone'";
        $userCheck = executeResult($sql, true);

        if($user == null && $userCheck == null) {
            return 2;
        }
        else if($user == null && $userCheck != null){
            $id = $userCheck['id'];
            $sql = "SELECT * FROM times_login WHERE id = '$id'";
            $data = executeResult($sql, true);
            if ($data == null) {
                $sql = "INSERT INTO times_login(id, times)  VALUES('$id', 1)";
                execute($sql);
                return 4; 
            }else{
                $timeLogin = null;
                if ($data['times'] == 5) {
                    $oldState = $userCheck['idState'];
                    $timelock = getNowDateTime();
                    $sql = "UPDATE users SET idState = 2 WHERE id = '$id'";
                    execute($sql);
                    $timeLogin = "UPDATE times_login SET oldState = '$oldState', datelock = '$timelock' WHERE id = '$id'";
                    execute($timeLogin);
                    return 3;
                }
                if ($data['times'] < 5) {
                    $timeLogin = "UPDATE times_login SET times = times + 1 WHERE id = '$id'";
                    execute($timeLogin);
                    return 4;
                }
            }
        }else{
            $state = $user['idState'];
            if($state == 3){
                return 1;
            }else if($state == 2){
                $id = $user['id'];
                $sql = "SELECT * FROM times_login WHERE id = '$id'";
                $data = executeResult($sql, true);
                $datelock = date("Y-m-d", strtotime($data['datelock']));;
                if (getNowDate() >= date('Y-m-d', strtotime('+1 week', strtotime($datelock)))){
                    $oldState = $data['oldState'];
                    $sql = "UPDATE users SET idState = '$oldState' WHERE id = '$id'";
                    execute($sql);
                    $sql = "DELETE FROM times_login WHERE id = '$id'";
                    execute($sql);
                    return 0;
                }else{
                    return 3;
                }   
            }else{
                return 0;
            }
        }
    }

    function uploadFolder($email){
        $target_dir = dirname(dirname(dirname(__FILE__)))."/"."uploads/";
        $renameEmail = str_replace('.', '_', $email);
        $target_dir = $target_dir . $renameEmail;
        if (!file_exists($target_dir)) {
            mkdir($target_dir);
        }
    }

    function register($phone,$email,$name,$password){
        $sql = "select * from `users` where `phone` = '$phone'";
        $user = executeResult($sql, true);
        if ($user != null){
            die(json_encode(array('code' => 1,'data' => 'Phone has already been registered')));
        }
        $sql = "select * from `users` where `email` = '$email'";
        $user = executeResult($sql, true);
        if ($user != null){
            die(json_encode(array('code' => 1,'data' => 'Email has already been registered')));
        }
        $hash = md5Security($password);
        $createAt = date('Y-m-d H:i:s');
        $sql = "insert into users(email,phone,name,password,createAt) values('$email','$phone','$name','$hash','$createAt')";
        execute($sql);
        uploadFolder($email);
        sendOTP($email);
        die(json_encode(array('code' => 0,'data' => 'Register successfully')));
    }

    function sendOTP($email){
        $sql = "SELECT * FROM `otp` WHERE `email` = '$email'";
        $otp = executeResult($sql, true);
        if(empty($otp)){
            $otp_pass=rand(100000, 999999);
            $sql = "INSERT INTO `otp` (`email`, `otp_pass`,`otp_timestamp`) VALUES ('$email', '$otp_pass','".getNowDateTime()."')";
            execute($sql);
            sendOTPMail($email, $otp_pass);
        }else{
            $otp_pass=rand(100000, 999999);
            $sql = "UPDATE `otp` SET `otp_pass` = '$otp_pass', `otp_timestamp` = '".getNowDateTime()."' WHERE `email` = '$email'";
            execute($sql);
            sendOTPMail($email, $otp_pass);
        }
    }

    function checkUser($email){
        $sql = "SELECT * FROM `users` WHERE `email` = '$email'";
        $user = executeResult($sql, true);
        if(empty($user)){
            return false;
        }else{
            return true;
        }
    }

    function resetPassword($email,$password){
        $hash = md5Security($password);
        $sql = "UPDATE `users` SET `password` = '$hash' WHERE `email` = '$email'";
        execute($sql);
        die(json_encode(array('code' => 0, 'data' =>"Reset password successfully")));
    }

    function changePassword($email, $curr_pass, $new_pass){
        if(!checkUser($email)){
            die(json_encode(array('code' => 1,'data' =>"User does not exist")));
        }
        $hash = md5Security($curr_pass);
        $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$hash'";
        $user = executeResult($sql, true);
        if($user==null){
            die(json_encode(array('code' => 1,'data' =>"Password does not match")));
        }else {
            $hashPass = md5Security($new_pass);
            $sql = "UPDATE users SET password ='$hashPass' WHERE email = '$email'";
            execute($sql);
            die(json_encode(array('code' => 0,'data' =>"Your password has changed successfully")));
        }
    }

    function linkCard($email,$cardnumber,$cccd,$name,$type){
        $sql = "INSERT INTO `debidcard` (`cardnumber`, `cccd`, `name`,`type`) VALUES ('$cardnumber', '$cccd', '$name', '$type')";
        execute($sql);
        $sql = "INSERT INTO `usercards` (`email`, `cardnumber`) VALUES ('$email', '$cardnumber')";
        execute($sql);
        die(json_encode(array('code' => 0,'data' =>"Link card successfully")));
    }

    function setPasswordTrans($email,$passwordtrans){
        if(!checkUser($email)){
            die(json_encode(array('code' => 1,'data' =>"User does not exist")));
        }
        $hashPassTrans = md5Security($passwordtrans);
        $sql = "UPDATE users SET passwordTrans = '$hashPassTrans' WHERE email = '$email'";
        execute($sql);
        die(json_encode(array('code' => 0,'data' =>"Your passwordTrans has setted successfully")));
    }

    function getLinkCard($email){
        $sql = "SELECT * FROM `usercards` WHERE `email` = '$email'";
        $usercards = executeResult($sql);
        $result = array();
        foreach ($usercards as $usercard){
            $cardnumber = $usercard['cardnumber'];
            $sql = "SELECT * FROM `debidcard` WHERE `cardnumber` = '$cardnumber'";
            $debidcard = executeResult($sql, true);
            $result[] = $debidcard;
        }
        die(json_encode(array('code' => 0,'data' => $result)));
    }

    function checkTransPass($email,$passtrans){
        $hashPassTrans = md5Security($passtrans);
        $sql = "SELECT * FROM `users` WHERE `email` = '$email' AND `passwordTrans` = '$hashPassTrans'";
        $user = executeResult($sql, true);
        if ($user == null) {
            die (json_encode(array('code' => 1, 'data' => 'Mật khẩu giao dịch không hợp lệ')));
        }
        die(json_encode(array('code' => 0,'data' =>"Mật khẩu giao dịch hợp lệ")));
    }

    function changeTransPass($email,$newtranspass){
        $hashPassTrans = md5Security($newtranspass);
        $sql = "UPDATE users SET passwordTrans = '$hashPassTrans' WHERE email = '$email'";
        execute($sql);
        die(json_encode(array('code' => 0,'data' =>"Your passwordTrans has changed successfully")));
    }

    function getDateForDatabase($date){
        $timestamp = strtotime($date);
        $date_formated = date('Y-m-d', $timestamp);
        return $date_formated;
    }

    function getDateTimeForDatabase($date){
        $timestamp = strtotime($date);
        $date_formated = date('Y-m-d H:i:s', $timestamp);
        return $date_formated;
    }

    function getNowDateTime() {
        $date = new DateTime();
        return $date->format('Y-m-d H:i:s');
    }

    function getNowDate(){
        $date = new DateTime();
        return $date->format('Y-m-d');
    }

    function checkCard($email,$cardnumber){
        $sql = "SELECT * FROM `usercards` WHERE `email` = '$email' AND `cardnumber` = '$cardnumber'";
        $card = executeResult($sql, true);
        if ($card == null) {
            return false;
        }
        return true;
    }

    function checkReceiver($receiver){
        $sql = "SELECT * FROM `users` WHERE `phone` = '$receiver' AND `idState` = 2";
        $user = executeResult($sql, true);
        if (empty($user)) {
            return false;
        }
        return true;
    }

    function getNextIncrement($table) {
        $sql = "SELECT * from `$table` ORDER BY `id` DESC";
        $result = executeResult($sql,true);
        if(!empty($result)){
            return $result['id'] + 1;
        }
        return 1;
    }


    function getIdIncrement($num,$type) {
        return $type.str_pad(intval($num), "6", "0", STR_PAD_LEFT);
    }
    
    function checkUserByEmailAndPhone($email, $phone) {
        $sql = "SELECT * FROM users WHERE email = '$email' and phone = '$phone'";
        $user = executeResult($sql, true);
        if(empty($user)){
            return null;
        }else{
            return $user;
        }
    }

    function depositHistory($email,$cardnumber,$amount){
        $next_increment = getNextIncrement('deposit');
        $id_increment = getIdIncrement($next_increment,'DE');
        $date_time = getNowDateTime();
        $sql = "INSERT INTO `transactions` (`idtrans`, `transtype`, `email`,`datetrans`,`amount`, `approval`) VALUES ('$id_increment', 'deposit', '$email','$date_time' ,'$amount', 1)";
        execute($sql);
        $sql = "INSERT INTO `deposit` (`idtrans`, `cardnumber`) VALUES ('$id_increment', '$cardnumber')";
        execute($sql);
    }

    function deposit($email,$cardnumber,$amount) {
        if(!checkCard($email,$cardnumber)){
            die (json_encode(array('code' => 1, 'data' => 'Thông tin thẻ không hợp lệ')));
        }

        $sql = "SELECT * FROM `debidcard` WHERE `cardnumber` = '$cardnumber'";
        $debidcard = executeResult($sql,true);
        if ($debidcard['balance'] < $amount) {
            die (json_encode(array('code' => 1, 'data' => 'Số dư thẻ không đủ')));
        }
        
        $sql = "SELECT * FROM `users` WHERE `email` = '$email'";
        $user = executeResult($sql, true);
        $balance = $user['balance'] + $amount;
        $sql = "UPDATE `users` SET `balance` = `balance` + '$balance' WHERE email = '$email'";
        execute($sql);
        $sql = "UPDATE `debidcard` SET `balance` = `balance` - '$amount' WHERE `cardnumber` = '$cardnumber'";
        execute($sql);
        depositHistory($email,$cardnumber,$amount);
        die (json_encode(array('code' => 0, 'data' => 'Nạp tiền thành công')));
    }

    function withdrawHistory($email,$cardnumber,$amount,$approval){
        $next_increment = getNextIncrement('withdraw');
        $id_increment = getIdIncrement($next_increment,'WD');
        $date_time = getNowDateTime();
        $sql = "INSERT INTO `transactions` (`idtrans`, `transtype`, `email`,`datetrans`,`amount`, `approval`) VALUES ('$id_increment', 'withdraw', '$email','$date_time' ,'$amount', '$approval')";
        execute($sql);
        $sql = "INSERT INTO `withdraw` (`idtrans`, `cardnumber`) VALUES ('$id_increment', '$cardnumber')";
        execute($sql);
    }

    function withdraw($email,$cardnumber,$amount){
        if(!checkCard($email,$cardnumber)){
            die (json_encode(array('code' => 1, 'data' => 'Thông tin thẻ không hợp lệ')));
        }

        $sql = "SELECT * FROM `users` WHERE `email` = '$email'";
        $user = executeResult($sql, true);
        if ($user['balance'] < $amount) {
            die (json_encode(array('code' => 1, 'data' => 'Số dư tài khoản không đủ')));
        }

        $sql = "UPDATE `users` SET `balance` = `balance` - $amount WHERE `email` = '$email'";
        execute($sql);
        $sql = "UPDATE `debidcard` SET `balance` = `balance` + $amount WHERE `cardnumber` = '$cardnumber'";
        execute($sql);
        withdrawHistory($email,$cardnumber,$amount,1);
        echo json_encode(array('code' => 0, 'data' => 'Rút tiền thành công'));
    }

    function transferHistory($email,$receiver,$amount,$note,$approval,$feepaid){
        $next_increment = getNextIncrement('transfer');
        $id_increment = getIdIncrement($next_increment,'TF');
        $date_time = getNowDateTime();
        $sql = "INSERT INTO `transactions` (`idtrans`, `transtype`, `email`,`datetrans`,`amount`, `approval`,`receiver`) VALUES ('$id_increment', 'transfer', '$email','$date_time' ,'$amount', '$approval','$receiver')";
        execute($sql);
        $sql = "INSERT INTO `transfer` (`idtrans`,`note`,`feepaid`) VALUES ('$id_increment','$note','$feepaid')";
        execute($sql);
        if($approval == 1){
            sendBalance($receiver,$id_increment);
        }
    }

    function transfer($email,$receiver,$amount,$feepaid,$note){
        if ($amount >= 5000000){
            transferHistory($email,$receiver,$amount,$note,0,$feepaid);
            die(json_encode(array('code' => 1, 'data' => 'Số tiền vượt quá 5000000 cần chờ xét duyệt')));
        }else{
            if($feepaid == 0){
                $fee = 0.05*$amount;
                $sql = "SELECT `balance` FROM `users` WHERE `email` = '$email'";
                $balance = executeResult($sql, true);
                if ($balance['balance'] < $amount + $fee){
                    die(json_encode(array('code' => 1, 'data' => 'Số dư không đủ để thực hiện giao dịch')));
                }
                $sql = "UPDATE `users` SET `balance` = `balance` - $amount - $fee WHERE `email` = '$email'";
                execute($sql);
                $sql = "UPDATE `users` SET `balance` = `balance` + $amount WHERE `phone` = '$receiver'";
                execute($sql);
                transferHistory($email,$receiver,$amount,$note,1,$feepaid);
                echo json_encode(array('code' => 0, 'data' => 'Chuyển tiền thành công vui lòng kiểm tra số dư trong tài khoản qua email')); 
            }else{
                $fee = 0.05*$amount;
                $sql = "SELECT `balance` FROM `users` WHERE `email` = '$email'";
                $balance = executeResult($sql, true);
                if ($balance['balance'] < $amount){
                    die(json_encode(array('code' => 1, 'data' => 'Số dư không đủ để thực hiện giao dịch')));
                }else{
                    $sql = "UPDATE `users` SET `balance` = `balance` - $amount WHERE `email` = '$email'";
                    execute($sql);
                    $sql = "UPDATE `users` SET `balance` = `balance` + $amount - $fee WHERE `phone` = '$receiver'";
                    execute($sql);
                    transferHistory($email,$receiver,$amount,$note,1,$feepaid);
                    echo json_encode(array('code' => 0, 'data' => 'Chuyển tiền thành công vui lòng kiểm tra số dư trong tài khoản qua email')); 
                }
            }
        } 
    }

    function generateCardCode($networkname) {
        $sql = "SELECT * FROM `network` WHERE `networkname` = '$networkname'";
        $network = executeResult($sql, true);
        if (empty($network)) {
            return null;
        }
        $networkid = $network['networkid'];
        $random = rand(10000, 99999);
        return $networkid.$random;
    }

    function topupHistory($email,$networkname,$price,$quantity){
        $next_increment = getNextIncrement('topupcard');
        $id_increment = getIdIncrement($next_increment,'TC');
        $date_time = getNowDateTime();
        $amount = $price * $quantity;
        $sql = "INSERT INTO `transactions` (`idtrans`, `transtype`, `email`,`datetrans`,`amount`, `approval`) VALUES ('$id_increment', 'topupcard', '$email', '$date_time','$amount', 1)";
        execute($sql);
        for ($i = 0; $i<$quantity; $i++){
            $cardcode = generateCardCode($networkname);
            $sql = "INSERT INTO `topupcard` (`idtrans`,`cardcode`, `networkname`, `price`) VALUES ('$id_increment','$cardcode', '$networkname', '$price')";
            execute($sql);
        }
    }

    function topupCard($email,$networkname,$price,$quantity){
        $sql = "SELECT * FROM `network` WHERE `networkname` = '$networkname'";
        $network = executeResult($sql, true);
        if (empty($network)){
            die(json_encode(array('code' => 1, 'data' => 'Nhà mạng không tồn tại')));
        }
        $fee =  $network['fee'];
        $sql = "SELECT `balance` FROM `users` WHERE `email` = '$email'";
        $balance = executeResult($sql, true);
        if ($balance['balance'] < ($price*$quantity + $fee*($price*$quantity))){
            die(json_encode(array('code' => 1, 'data' => 'Số dư không đủ để thực hiện giao dịch')));
        }else{
            $updateWallet = "UPDATE `users` SET `balance` = `balance` - $price*$quantity - $fee*($price*$quantity) WHERE `email` = '$email'";
            execute($updateWallet);
            topupHistory($email,$networkname,$price,$quantity);
            echo json_encode(array('code' => 0, 'data' => 'Mua thẻ thành công'));
        }
    }

    function getChart($email, $chartType, $dateType){
        
    }

    function getAllTransactions($email){
        $sql = "SELECT * FROM `users` WHERE `email` = '$email'";
        $user = executeResult($sql, true);
        $phone = $user['phone'];
        $sql = "SELECT * FROM `transactions` WHERE `email` = '$email' OR `receiver` = '$phone' AND `approval` = 1 ORDER BY `datetrans` DESC";
        $transaction = executeResult($sql, false);
        if (empty($transaction)){
            die(json_encode(array('code' => 1, 'data' => [])));
        }
        echo json_encode(array('code' => 0, 'data' => $transaction));
    }

    function getTransaction($idtrans,$transtype){
        $sql = "SELECT * FROM `transactions` JOIN `$transtype` ON `transactions`.`idtrans` = `$transtype`.`idtrans` WHERE `transactions`.`idtrans` = '$idtrans' AND `transactions`.`transtype` = '$transtype'";
        $transaction = executeResult($sql, false);
        if (empty($transaction)){
            die(json_encode(array('code' => 1, 'data' => [])));
        }
        echo json_encode(array('code' => 0, 'data' => $transaction));
    }
?>