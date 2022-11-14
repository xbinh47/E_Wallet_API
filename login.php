<?php
require_once('./api/authen.php');

if (isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

$error = '';

if (isset($_COOKIE['login'])) {
    $error = "Your account have been lock now. Try again after 1 minutes";
} else {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $data = login($username, $password);
        if ($data['code'] != 1) {
            $error = $data['error'];
            if ($data['code'] == 2) {
                if ($username != "admin") {
                    $times = loginwrong($username);
                    if ($times >= 6) {
                        $error = "Your account has been locked because enter the wrong password many times. Please contact administrator for assistance.";
                    }
                }
            }
        } else {
            if($_SESSION['state'] == 4){
                $error = "Your account has been locked because enter the wrong password many times. Please contact administrator for assistance.";
                session_destroy();
            }
            else if($_SESSION['state'] == 3){
                $error = "Tài khoản này đã bị vô hiệu hóa, vui lòng liên hệ tổng đài 18001008";
                session_destroy();
            }else{
                header('Location: index.php');
                exit();
            }
        }
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
                            <form id="loginform" class="form-horizontal" action="" method="post">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                    <input id="login-username" type="text" class="form-control" name="username" value=""
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