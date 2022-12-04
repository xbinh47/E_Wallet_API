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
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $user = executeResult($sql, true);
        $id = $user['id'];
        $userId = (int) $user['id'] + 100000;
        $sql = "UPDATE users SET idUser = $userId WHERE id = '$id'";
        execute($sql);
        uploadFolder($email);
        if(!sendOTP($email)){
            die(json_encode(array('code' => 1,'data' => 'Send OTP fail')));
        }  
        die(json_encode(array('code' => 0,'data' => 'Register successfully')));
    }

    function sendOTP($email){
        $sql = "SELECT * FROM `otp` WHERE `email` = '$email'";
        $otp = executeResult($sql, true);
        if(empty($otp)){
            $otp_pass=rand(100000, 999999);
            $sql = "INSERT INTO `otp` (`email`, `otp_pass`,`otp_timestamp`) VALUES ('$email', '$otp_pass','".getNowDateTime()."')";
            execute($sql);
            if(!sendOTPMail($email, $otp_pass)){
                return false;
            }
            return true;
        }else{
            $otp_pass=rand(100000, 999999);
            $sql = "UPDATE `otp` SET `otp_pass` = '$otp_pass', `otp_timestamp` = '".getNowDateTime()."' WHERE `email` = '$email'";
            execute($sql);
            if(!sendOTPMail($email, $otp_pass)){
                return false;
            }
            return true;
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
        $sql = "SELECT * FROM `debidcard` WHERE `cardnumber` = '$cardnumber'";
        $card = executeResult($sql, true);
        if(!empty($card)){
            die(json_encode(array('code' => 1,'data' =>"Card has already been registered")));
        }
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
        $sql = "SELECT * FROM `users` WHERE `phone` = '$receiver'";
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

    function deposit($email,$cardnumber,$amount,$passtrans) {
        if(!checkCard($email,$cardnumber)){
            die (json_encode(array('code' => 1, 'data' => 'Thông tin thẻ không hợp lệ')));
        }

        $hashPassTrans = md5Security($passtrans);
        $sql = "SELECT * FROM `users` WHERE `email` = '$email' AND `passwordTrans` = '$hashPassTrans'";
        $user = executeResult($sql, true);
        if ($user == null) {
            die (json_encode(array('code' => 1, 'data' => 'Mật khẩu giao dịch không hợp lệ')));
        }

        $sql = "SELECT * FROM `debidcard` WHERE `cardnumber` = '$cardnumber'";
        $debidcard = executeResult($sql,true);
        if ($debidcard['balance'] < $amount) {
            die (json_encode(array('code' => 1, 'data' => 'Số dư thẻ không đủ')));
        }

        $sql = "UPDATE `users` SET `balance` = `balance` + '$amount' WHERE email = '$email'";
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

    function withdraw($email,$cardnumber,$amount,$passtrans) {
        if(!checkCard($email,$cardnumber)){
            die (json_encode(array('code' => 1, 'data' => 'Thông tin thẻ không hợp lệ')));
        }

        $hashPassTrans = md5Security($passtrans);
        $sql = "SELECT * FROM `users` WHERE `email` = '$email' AND `passwordTrans` = '$hashPassTrans'";
        $user = executeResult($sql, true);
        if ($user == null) {
            die (json_encode(array('code' => 1, 'data' => 'Mật khẩu giao dịch không hợp lệ')));
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

    function transferHistory($email,$receiver,$amount,$note,$approval){
        $next_increment = getNextIncrement('transfer');
        $id_increment = getIdIncrement($next_increment,'TF');
        $date_time = getNowDateTime();
        $sql = "INSERT INTO `transactions` (`idtrans`, `transtype`, `email`,`datetrans`,`amount`, `approval`,`receiver`) VALUES ('$id_increment', 'transfer', '$email','$date_time' ,'$amount', '$approval','$receiver')";
        execute($sql);
        $sql = "INSERT INTO `transfer` (`idtrans`,`note`) VALUES ('$id_increment','$note')";
        execute($sql);
        if(!sendBalance($receiver,$id_increment)){
            return false;
        }
        return true;
    }

    function transfer($email,$receiver,$amount,$note,$passtrans){
        $hashPassTrans = md5Security($passtrans);
        $sql = "SELECT * FROM `users` WHERE `email` = '$email' AND `passwordTrans` = '$hashPassTrans'";
        $user = executeResult($sql, true);
        if ($user == null) {
            die (json_encode(array('code' => 1, 'data' => 'Mật khẩu giao dịch không hợp lệ')));
        }

        if ($user['balance'] < $amount) {
            die (json_encode(array('code' => 1, 'data' => 'Số dư tài khoản không đủ')));
        }
        
        $sql = "UPDATE `users` SET `balance` = `balance` - $amount WHERE `email` = '$email'";
        execute($sql);
        $sql = "UPDATE `users` SET `balance` = `balance` + $amount WHERE `phone` = '$receiver'";
        execute($sql);
        if(!transferHistory($email,$receiver,$amount,$note,1)){
            die (json_encode(array('code' => 1, 'data' => 'Mail không tồn tại')));
        };
        die(json_encode(array('code' => 0, 'data' => 'Chuyển tiền thành công vui lòng kiểm tra số dư trong tài khoản qua email'))); 
    }

    function generateCardCode() {
        $random = rand(1000000000000, 9999999999999);
        return $random;
    }

    function generateCardSeri() {
        $random = rand(100000000000000, 999999999999999);
        return $random;
    }

    function topupHistory($email,$networkname,$price){
        $next_increment = getNextIncrement('topupcard');
        $id_increment = getIdIncrement($next_increment,'TC');
        $date_time = getNowDateTime();
        $amount = $price;
        $sql = "INSERT INTO `transactions` (`idtrans`, `transtype`, `email`,`datetrans`,`amount`, `approval`) VALUES ('$id_increment', 'topupcard', '$email', '$date_time','$amount', 1)";
        execute($sql);
        $cardcode = generateCardCode();
        $cardseri = generateCardSeri();
        $sql = "INSERT INTO `topupcard` (`idtrans`,`cardseri`,`cardcode`, `networkname`, `price`) VALUES ('$id_increment','$cardseri','$cardcode', '$networkname', '$price')";
        execute($sql);
        return $id_increment;
    }

    function topupCard($email,$networkname,$price,$passtrans){
        $hashPassTrans = md5Security($passtrans);
        $sql = "SELECT * FROM `users` WHERE `email` = '$email' AND `passwordTrans` = '$hashPassTrans'";
        $user = executeResult($sql, true);
        if ($user == null) {
            die (json_encode(array('code' => 1, 'data' => 'Mật khẩu giao dịch không hợp lệ')));
        }
        
        $sql = "SELECT * FROM `network` WHERE `networkname` = '$networkname'";
        $network = executeResult($sql, true);
        if (empty($network)){
            die(json_encode(array('code' => 1, 'data' => 'Nhà mạng không tồn tại')));
        }
        $sql = "SELECT `balance` FROM `users` WHERE `email` = '$email'";
        $balance = executeResult($sql, true);
        if ($balance['balance'] < $price){
            die(json_encode(array('code' => 1, 'data' => 'Số dư không đủ để thực hiện giao dịch')));
        }else{
            $updateWallet = "UPDATE `users` SET `balance` = `balance` - $price WHERE `email` = '$email'";
            execute($updateWallet);
            $id_increment=topupHistory($email,$networkname,$price);
            $sql = "SELECT `cardseri`,`cardcode`,`networkname`,`price` FROM `topupcard` WHERE `idtrans` = '$id_increment'";
            $card = executeResult($sql, true);
            die(json_encode(array('code' => 0, 'data' => $card)));
        }
    }

    function transObject($deposit,$withdraw,$transferIn,$transferOut,$topupcard){
        $transObject = array(
            'deposit' => $deposit,
            'withdraw' => $withdraw,
            'transferIn' => $transferIn,
            'transferOut' => $transferOut,
            'topupcard' => $topupcard
        );
        return $transObject;
    }

    function getDailyTransactions($email){
        $sql = "SELECT * FROM `Users` WHERE `email` = '$email'";
        $user = executeResult($sql, true);
        $phone = $user['phone'];
        $result = array();
        $dailyArr = array();
        #get 5 days of transactions from now sql
        $sql = "SELECT * FROM `transactions` WHERE `email` = '$email' AND `datetrans` >= DATE_SUB(NOW(), INTERVAL 5 DAY) ORDER BY `datetrans` DESC";
        for ($i = 0; $i < 5; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dailyArr[$i] = $date;
            $result[$i] = transObject(0,0,0,0,0);
        }
        $transactions = executeResult($sql);
        foreach ($transactions as $transaction) {
            $transtype = $transaction['transtype'];
            $amount = $transaction['amount'];
            $datetrans = $transaction['datetrans'];
            $datetrans = date('Y-m-d', strtotime($datetrans));
            $key = array_search($datetrans, $dailyArr);
            if ($key !== false) {
                if ($transtype == 'deposit') {
                    $result[$key]['deposit'] += $amount;
                } elseif ($transtype == 'withdraw') {
                    $result[$key]['withdraw'] += $amount;
                } elseif ($transtype == 'transfer') {
                    if ($transaction['receiver'] == $phone) {
                        $result[$key]['transferOut'] += $amount;
                    } else {
                        $result[$key]['transferIn'] += $amount;
                    }
                } elseif ($transtype == 'topupcard') {
                    $result[$key]['topupcard'] += $amount;
                }
            }
        }
        return $result;
    }

    function getMonthlyTransactions($email){
        $sql = "SELECT * FROM `Users` WHERE `email` = '$email'";
        $user = executeResult($sql, true);
        $phone = $user['phone'];
        $date = getNowDate();
        $sql = "SELECT * FROM `transactions` WHERE `email` = '$email' AND CAST(`datetrans` AS DATE) BETWEEN DATE_SUB('$date', INTERVAL 5 MONTH) AND '$date'";
        $transactions = executeResult($sql);
        $result = array();
        $monthArr = array();
        for ($i = 0; $i < 5; $i++){
            $date = getNowDate();
            $date = date('m', strtotime("-$i month", strtotime($date)));
            $monthArr[$i] = $date;
            $result[$i] = transObject(0,0,0,0,0);
        }
        foreach($transactions as $transaction){
            $date = $transaction['datetrans'];
            $date = date('m', strtotime($date));
            $index = array_search($date, $monthArr);
            if ($transaction['transtype'] == 'deposit'){
                $result[$index]['deposit'] += $transaction['amount'];
            }else if ($transaction['transtype'] == 'withdraw'){
                $result[$index]['withdraw'] += $transaction['amount'];
            }else if ($transaction['transtype'] == 'transfer'){
                if ($transaction['receiver'] != $phone){
                    $result[$index]['transferOut'] += $transaction['amount'];
                }else{
                    $result[$index]['transferIn'] += $transaction['amount'];
                }
            }else if ($transaction['transtype'] == 'topupcard'){
                $result[$index]['topupcard'] += $transaction['amount'];
            }
        }
        return $result;
    }

    function getChart($email, $dateType){
        $result = array();
        if ($dateType == 'Daily'){
            $result = getDailyTransactions($email);
            die(json_encode(array('code' => 0, 'data' => $result)));

        }else if ($dateType == 'Monthly'){
            $result = getMonthlyTransactions($email);
            die(json_encode(array('code' => 0, 'data' => $result)));

        }else{
            die(json_encode(array('code' => 1, 'data' => 'Invalid date type')));
        }
    }

    function getAllTransactions($email){
        $sql = "SELECT * FROM `users` WHERE `email` = '$email'";
        $user = executeResult($sql, true);
        $phone = $user['phone'];
        $sql = "SELECT * FROM `transactions` WHERE `email` = '$email' OR `receiver` = '$phone' ORDER BY `datetrans` DESC";
        $transactions = executeResult($sql);
        if (empty($transactions)){
            die(json_encode(array('code' => 1, 'data' => [])));
        }
        $result = array();
        for ($i = 0; $i < 12; $i++){
            $result[$i] = array();
        }
        foreach($transactions as $transaction){
            $date = $transaction['datetrans'];
            $transMonth = date('m', strtotime($date));
            $transYear = date('Y', strtotime($date));
            if($transYear == date('Y')){
                $index = $transMonth - 1;
                array_push($result[$index], $transaction);
            }
        }
        die(json_encode(array('code' => 0, 'data' => $result)));
    }

    function getTransaction($idtrans,$transtype){
        $sql = "SELECT * FROM `transactions` JOIN `$transtype` ON `transactions`.`idtrans` = `$transtype`.`idtrans` WHERE `transactions`.`idtrans` = '$idtrans' AND `transactions`.`transtype` = '$transtype'";
        $transaction = executeResult($sql, false);
        if (empty($transaction)){
            die(json_encode(array('code' => 1, 'data' => [])));
        }
        die(json_encode(array('code' => 0, 'data' => $transaction)));
    }
?>