<?php
    require_once('../../db/dbhelper.php');
    require_once('../../utils/utility.php');

    date_default_timezone_set('Asia/Ho_Chi_Minh');

    function checkUser($email){
        $sql = "SELECT * FROM `users` WHERE `email` = '$email'";
        $user = executeResult($sql, true);
        if(empty($user)){
            return false;
        }else{
            return true;
        }
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

    function checkCard($cardnumber,$expdate,$cvv){
        $expdateformat = getDateForDatabase($expdate);
        $sql = "SELECT * FROM `debidcard` WHERE `cardnumber` = '$cardnumber' AND `expdate` = '$expdateformat' AND `cvv` = '$cvv'";
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
   
    function depositHistory($email,$cardnumber,$expdate,$cvv,$amount){
        $next_increment = getNextIncrement('deposit');
        $id_increment = getIdIncrement($next_increment,'DE');
        $date_time = getNowDateTime();
        $sql = "INSERT INTO `transactions` (`idtrans`, `transtype`, `email`,`datetrans`,`amount`, `approval`) VALUES ('$id_increment', 'deposit', '$email','$date_time' ,'$amount', 1)";
        execute($sql);
        $sql = "INSERT INTO `deposit` (`idtrans`, `cardnumber`) VALUES ('$id_increment', '$cardnumber')";
        execute($sql);
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

    function deposit($email,$cardnumber,$expdate,$cvv,$amount) {
        if(!checkCard($cardnumber,$expdate,$cvv)){
            die (json_encode(array('code' => 1, 'data' => 'Thông tin thẻ không hợp lệ')));
        }
        if ($cardnumber == 333333){
            die(json_encode(array('code' => 1, 'data' => 'Thẻ hết tiền')));
        }
        if ($cardnumber == 222222){
            if ($amount > 1000000){
                die(json_encode(array('code' =>1, 'data' => 'Số tiền không được vượt quá 1000000')));
            }
            $sql = "SELECT `balance` FROM `debidcard` WHERE `cardnumber` = '$cardnumber'";
            $balance = executeResult($sql, true);
            if ($balance < $amount){
                die(json_encode(array('code' => 1, 'data' => 'Số tiền không đủ để thực hiện giao dịch')));
            }
            $sql = "UPDATE `users` SET `balance` = `balance` + '$amount' WHERE `email` = '$email'";
            execute($sql);
            $sql = "UPDATE `debidcard` SET `balance` = `balance` - '$amount' WHERE `cardnumber` = 222222";
            execute($sql);
            depositHistory($email,$cardnumber,$expdate,$cvv,$amount);
            echo json_encode(array('code' => 0, 'data' => 'Nạp tiền thành công'));
        }
        if ($cardnumber == 111111){
            $sql = "UPDATE `users` SET `balance` = `balance` + '$amount' WHERE `email` = '$email'";
            execute($sql);
            $sql = "UPDATE `debidcard` SET `balance` = `balance` - '$amount' WHERE `cardnumber` = 111111";
            execute($sql);
            depositHistory($email,$cardnumber,$expdate,$cvv,$amount);
            echo json_encode(array('code' => 0, 'data' => 'Nạp tiền thành công'));
        }
    }

    function withdrawHistory($email,$cardnumber,$amount,$note,$approval){
        $next_increment = getNextIncrement('withdraw');
        $id_increment = getIdIncrement($next_increment,'WD');
        $date_time = getNowDateTime();
        $sql = "INSERT INTO `transactions` (`idtrans`, `transtype`, `email`,`datetrans`,`amount`, `approval`) VALUES ('$id_increment', 'withdraw', '$email','$date_time' ,'$amount', '$approval')";
        execute($sql);
        $sql = "INSERT INTO `withdraw` (`idtrans`, `cardnumber`, `note`) VALUES ('$id_increment', '$cardnumber', '$note')";
        execute($sql);
    }

    function withdraw($email,$cardnumber,$expdate,$cvv,$amount,$note){
        if(!checkCard($cardnumber,$expdate,$cvv)){
            die (json_encode(array('code' => 1, 'data' => 'Thông tin thẻ không hợp lệ')));
        }
        if ($cardnumber == 222222 || $cardnumber == 333333){
            die(json_encode(array('code' => 1, 'data' => 'Thẻ này không hỗ trợ để rút tiền')));
        }
        if ($cardnumber == 111111){
            $date_now = getNowDate();
            $sql = "SELECT * FROM `transactions` WHERE `email` = '$email' AND `transtype` = 'withdraw' AND CAST(`datetrans` AS DATE) = '$date_now' AND `approval` = 1 OR `approval` = 0";
            $transactions = executeResult($sql, false);
            if (count($transactions) > 1){
                die(json_encode(array('code' => 1, 'data' => 'Bạn đã rút tiền quá 2 lần trong ngày')));
            }
            $sql = "SELECT `balance` FROM `users` WHERE `email` = '$email'";
            $balance = executeResult($sql, true);
            $fee = $amount*0.05;
            if ($balance['balance'] < ($amount+$fee)){
                die(json_encode(array('code' => 1, 'data' => 'Số dư không đủ để thực hiện giao dịch')));
            }
            if ($amount < 5000000){
                $sql = "UPDATE `users` SET `balance` = `balance` - $amount - $fee WHERE `email` = '$email'";
                execute($sql);
                $sql = "UPDATE `debidcard` SET `balance` = `balance` + $amount WHERE `cardnumber` = '$cardnumber'";
                execute($sql);
                withdrawHistory($email,$cardnumber,$amount,$note,1);
                echo json_encode(array('code' => 0, 'data' => 'Rút tiền thành công'));
            }else{
                withdrawHistory($email,$cardnumber,$amount,$note,0);
                echo(json_encode(array('code' => 0, 'data' => 'Số tiền vượt quá 5000000 cần chờ xét duyệt')));
            }
        }
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