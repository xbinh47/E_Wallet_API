<?php 
    require_once('../../db/dbhelper.php');
    require_once('../../utils/utility.php');

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
    
    if (!property_exists($data,'email') || !property_exists($data,'idtrans') || !property_exists($data,'transtype') || !property_exists($data,'decision')){
        die(json_encode(array('code' => 1, 'data' => 'Missing Paramenter')));
    }

    $email = $data->email;
    $idtrans = $data->idtrans;
    $transtype = $data->transtype;
    $decision = $data->decision; //0 la từ chối, 1 la duyệt
    
    $sql = "SELECT * FROM `transactions` WHERE `idtrans` = '$idtrans'";
    $transaction = executeResult($sql,true);
    if (empty($transaction)){
        die(json_encode(array('code' => 1, 'data' => 'ID giao dịch không tồn tại')));
    }

    if($transaction['approval'] != 0){
        die(json_encode(array('code' => 1, 'data' => 'Giao dịch đã được duyệt hoặc từ chối')));
    }

    if($decision == 0){
        $sql = "UPDATE `transactions` SET `approval` = '-1' WHERE `idtrans` = '$idtrans'";
        execute($sql);
        die(json_encode(array('code' => 0, 'data' => 'Hủy giao dịch thành công')));
    }

    if ($transtype == 'withdraw'){
        $sql = "SELECT `balance` FROM `users` WHERE `email` = '$email'";
        $user = executeResult($sql,true);

        if (empty($user)){
            die(json_encode(array('code' => 1, 'data' => 'Email của User không tồn tại')));
        }

        $balance = $user['balance'];
        $amount = $transaction['amount'];
        $fee = $amount * 0.05;

        if($balance < $amount + $fee){
            $sql = "UPDATE `transactions` SET `approval` = -1 WHERE `idtrans` = '$idtrans'";
            execute($sql);
            die(json_encode(array('code' => 1, 'data' => 'Số dư của User không đủ để thực hiện giao dịch')));
        }else{
            $sql = "UPDATE `transactions` SET `approval` = 1 WHERE `idtrans` = '$idtrans'";
            execute($sql);
            $sql = "UPDATE `users` SET `balance` = `balance` - $amount - $fee WHERE `email` = '$email'";
            execute($sql);
            $sql = "UPDATE `debidcard` SET `balance` = `balance` + $amount WHERE `cardnumber` = 111111";
            execute($sql);
            die(json_encode(array('code' => 0, 'data' => 'Duyệt giao dịch thành công')));
        }
    }

    if ($transtype == 'transfer'){
        $sql = "SELECT `balance` FROM `users` WHERE `email` = '$email'";
        $user = executeResult($sql,true);
        if (empty($user)){
            die(json_encode(array('code' => 1, 'data' => 'Email của User không tồn tại')));
        }

        $receiver = $transaction['receiver'];
        $balance = $user['balance'];
        $amount = $transaction['amount'];
        $fee = $amount * 0.05;
        
        $sql = "SELECT `feepaid` FROM `transfer` WHERE `idtrans` = '$idtrans'";
        $transfer_feepaid = executeResult($sql,true);
        $feepaid = $transfer_feepaid['feepaid'];

        if($feepaid == 0){
            if($balance < $amount + $fee){
                $sql = "UPDATE `transactions` SET `approval` = -1 WHERE `idtrans` = '$idtrans'";
                execute($sql);
                die(json_encode(array('code' => 1, 'data' => 'Số dư của User không đủ để thực hiện giao dịch')));
            }else{
                $sql = "UPDATE `transactions` SET `approval` = 1 WHERE `idtrans` = '$idtrans'";
                execute($sql);
                $sql = "UPDATE `users` SET `balance` = `balance` - $amount - $fee WHERE `email` = '$email'";
                execute($sql);
                $sql = "UPDATE `users` SET `balance` = `balance` + $amount WHERE `phone` = '$receiver'";
                execute($sql);
                sendBalance($receiver,$idtrans);
                die(json_encode(array('code' => 0, 'data' => 'Duyệt giao dịch thành côngggg')));
            }
        }else{
            if($balance < $amount) {
                $sql = "UPDATE `transactions` SET `approval` = -1 WHERE `idtrans` = '$idtrans'";
                execute($sql);
                die(json_encode(array('code' => 1, 'data' => 'Số dư của User không đủ để thực hiện giao dịch')));
            }else{
                $sql = "UPDATE `transactions` SET `approval` = 1 WHERE `idtrans` = '$idtrans'";
                execute($sql);
                $sql = "UPDATE `users` SET `balance` = `balance` - $amount WHERE `email` = '$email'";
                execute($sql);
                $sql = "UPDATE `users` SET `balance` = `balance` + $amount - $fee WHERE `phone` = '$receiver'";
                execute($sql);
                sendBalance($receiver,$idtrans);
                die(json_encode(array('code' => 0, 'data' => 'Duyệt giao dịch thành công')));
            }
        }
    }
?>