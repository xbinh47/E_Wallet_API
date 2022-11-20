<?php
    require_once('services.php');
    require_once('../../db/dbhelper.php');
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    if (date('Y-m-d', strtotime('+1 week', strtotime(getNowDate()))) > getNowDate())
        echo "a is bigger than b";
    else
        echo "a is smaller than b";
    
    register("0909043072","conghienqt0205@gmail.com","123456");
    
?>
