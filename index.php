<?php
    session_start();
    
    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }

    require_once('./db/dbhelper.php');
    $id = $_SESSION['id'];
    $sql = "SELECT * FROM users WHERE id = $id";
    $user = executeResult($sql, true);
    if (!empty($user)) {
        if ($user['idState'] == 0) {
            $_SESSION['first'] = true;
            header('Location: firstLogin.php');
		    exit();
        }
    }

    if(isset($_SESSION['state'])) {
        if ($_SESSION['state'] == 2) {
            header('Location: user/index.php');
            exit();
        }else if ($_SESSION['state'] == 5) {
            header('Location: admin/index.php');
            exit();
        }
        else{
            header('Location: newuser/index.php');
            exit();
        }
    }

?>