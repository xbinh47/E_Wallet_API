<?php
    require_once('services.php');
    require_once('../../db/dbhelper.php');
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    // $monthArr = array();
    // for ($i = 0; $i < 5; $i++){
    //     $date = getNowDate();
    //     $date = date('m', strtotime("-$i month", strtotime($date)));
    //     $monthArr[$i] = $date;
    // }
    $monthArr = array_fill(0, 12, new stdClass());
    echo json_encode($monthArr);
?>
