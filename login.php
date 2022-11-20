<?php
    session_start();
    require_once('./db/dbhelper.php');
    require_once('./utils/utility.php');

$error = '';
if (isset($_POST['phone']) && isset($_POST['password'])) {
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $hashPass = md5Security($password);
    $sql = "select * from `users` where `phone` = '$phone' and `password` = '$hashPass'";
    $user = executeResult($sql, true);
    if($user != null){
        $state = $user['idState'];
        if($state == 3){
            $_SESSION['id'] = $user['id'];
            $_SESSION['state'] = 3;
            header("Location: index.php");
            exit();
        }else{
            $error = "Only admin can login this page.";
        }
    }else{
        $error = "Invalid Phone/Password";
    }
}

include('header.php');

?>

<body>
    <div id="login-bg" class="container-fluid">
        <div id="loginbox">
            <div class="row">
                <div class="col-12 d-flex flex-column align-items-center my-3">
                    <img src="/img/logo.png" alt="">
                    <h1>E-wallet</h1>
                </div>   
            </div>
            <div class="row justify-content-center mt-2 mb-4">
                <div class="col-lg-4 col-md-6 col-sm-8">
                    <div class="panel">
                        <div class="panel-heading d-flex justify-content-center mb-3">
                            <h3>Sign In</h3>
                        </div>
                        <div class="panel-body">
                            <form action="" method="POST" id="loginform" class="form-horizontal" >
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                    <input id="login-username" type="text" class="form-control" name="phone" value = ""
                                        placeholder="Username or email">
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                    <input id="login-password" type="password" class="form-control" name="password"
                                        placeholder="Password">
                                </div>
                                <div class="mb-3">
                                    <?php
                                        if (!empty($error)) {
                                            echo "<p class='text-danger'>$error</p>";
                                        }
                                    ?>
                                </div>
                                <div class="form-check d-flex align-items-center">
                                    <input id="login-remember" class="form-check-input me-2" type="checkbox"
                                        name="remember" value="1">
                                    <label for="login-remember" class="form-check-label">
                                        Remember me
                                    </label>
                                </div>
                                <button id="btn-signup" type="submit" class="btn btn-outline-info w-100 my-3">Login</button>
                                <div class="col-md-12 d-flex justify-content-center mb-2">
                                    <small>
                                        Don't have an account!
                                        <a href="./register.php" class="text-info">
                                            Sign Up Here
                                        </a>
                                    </small>
                                </div>
                                <div class="col-12 d-flex justify-content-center">
                                    <small>
                                        <a href="./forgetPassword.php" class="text-info">
                                            Forgot password?
                                        </a>
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>