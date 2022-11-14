<?php
    require_once('config.php');

    function execute($sql) {
        $con = mysqli_connect(HOST, USER, PASS, DB);
        mysqli_set_charset($con, 'utf8');
        mysqli_query($con, $sql);
        mysqli_close($con);
    }

    function executeResult($sql, $isSingle = false) {
        $con = mysqli_connect(HOST, USER, PASS, DB);
        mysqli_set_charset($con, 'utf8');
        $result = mysqli_query($con, $sql);
        if ($isSingle) {
            $data = mysqli_fetch_array($result, 1);
        }
        else {
            $data = [];
            while(($row = mysqli_fetch_array($result, 1)) != null) {
                $data[] = $row;
            }
        }
        
        mysqli_close($con);

        return $data;
    }

?>