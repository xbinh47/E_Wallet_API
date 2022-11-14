<?php 
    require_once ('../authen.php');

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
    
    if (!property_exists($data,'email') || !property_exists($data,'curr_pass')|| !property_exists($data,'new_pass') || !property_exists($data,'confirm_pass')){
        die(json_encode(array('code' => 1, 'data' => 'Missing Paramenter')));
    }

    $email = $data->email;
    $curr_pass = $data->curr_pass;
    $new_pass = $data->new_pass;
    $confirm_pass = $data->confirm_pass;

    echo json_encode(changepwd($email, $curr_pass, $new_pass, $confirm_pass));
?>