<?php
    session_start();
    if (!isset($_SESSION['id'])) {
        header('Location: ../login.php');
        exit();
    }

    if ($_SESSION['state'] != 3) {
        header('Location: ../index.php');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="apple-touch-icon" sizes="180x180" href="../img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img//favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img//favicon-16x16.png">
    <link rel="manifest" href="../img//site.webmanifest">
    <link rel="stylesheet" href="../style.css">
    <title>E-wallet</title>
</head>

<body>
    <div id="permission" class="d-none">admin</div>
    <div id="main-container" class="container-fluid">
        <div class="row h-100">
            <div id="side-bar" class="col-1">
                <div id="logo">
                    <a href="index.php">
                        <img alt="" src="../img/logo.png">
                    </a>
                </div>
                <hr>
                <ul id="tab">
                    <li id="account-management-btn" data-bs-toggle="tooltip" data-bs-placement="right"
                        title="Account Manage">
                        <i class="fa-solid fa-file-invoice icon icon-active"></i>
                    </li>
                    <li id="confirm-transaction-btn" data-bs-toggle="tooltip" data-bs-placement="right"
                        title="Confirm Transaction">
                        <i class="fa-solid fa-clipboard-check icon"></i>
                    </li>
                    <a class="d-block mt-5" href="../logout.php" data-bs-toggle="tooltip" data-bs-placement="right"
                        title="Logout">
                        <i class="fa-solid fa-power-off icon"></i>
                    </a>
                </ul>
            </div>
            <div id="main-content" class="col">
                <header>
                    <div class="container-fluid">
                        <div class="row my-3">
                            <div class="d-flex align-items-center col">
                                <div id="date-time" class="me-auto">
                                    <h5 id="date"></h5>
                                    <h6 id="time"></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="container-fluid">
                    <div class="row mt-3">
                        <div class="col">
                            <h2 id="section-name" class="d-inline-block"></h2>
                        </div>
                    </div>
                    <div id="account-management" class="row">
                        <div class="col-12">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#act-acc"
                                        type="button" role="tab" aria-controls="act-acc" aria-selected="true">Activate
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#actived-acc"
                                        type="button" role="tab" aria-controls="actived-acc"
                                        aria-selected="false">Actived
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#disable-acc"
                                        type="button" role="tab" aria-controls="disable-acc"
                                        aria-selected="false">Disable
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#locked-acc"
                                        type="button" role="tab" aria-controls="locked-acc" aria-selected="false">Locked
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="act-acc" role="tabpanel"
                                    aria-labelledby="home-tab">
                                    <div class="account-container">
                                        <div id="table-container">
                                            <table id="table" class="table table-dark table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>User Id</th>
                                                        <th>User Name</th>
                                                        <th>Phone Number</th>
                                                        <th>Email</th>
                                                        <!-- <th>Date Of Birth</th> -->
                                                    </tr>
                                                </thead>
                                                <tbody id="activate-acc-list"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="actived-acc" role="tabpanel">
                                    <div class="account-container">
                                        <div id="table-container">
                                            <table id="table" class="table table-dark table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>User Id</th>
                                                        <th>User Name</th>
                                                        <th>Phone Number</th>
                                                        <th>Email</th>
                                                        <!-- <th>Date Of Birth</th> -->
                                                    </tr>
                                                </thead>
                                                <tbody id="actived-acc-list"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="disable-acc" role="tabpanel">
                                    <div class="account-container">
                                        <div id="table-container">
                                            <table id="table" class="table table-dark table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>User Id</th>
                                                        <th>User Name</th>
                                                        <th>Phone Number</th>
                                                        <th>Email</th>
                                                        <!-- <th>Date Of Birth</th> -->
                                                    </tr>
                                                </thead>
                                                <tbody id="disable-acc-list"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="locked-acc" role="tabpanel">
                                    <div class="account-container">
                                        <div id="table-container">
                                            <table id="table" class="table table-dark table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>User Id</th>
                                                        <th>User Name</th>
                                                        <th>Phone Number</th>
                                                        <th>Email</th>
                                                        <!-- <th>Date Of Birth</th> -->
                                                    </tr>
                                                </thead>
                                                <tbody id="locked-acc-list"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="confirm-transaction" class="row">
                        <div class="col-12">
                            <div id="table-container">
                                <table id="table" class="table table-dark table-hover">
                                    <thead>
                                        <tr>
                                            <th>Transaction Id</th>
                                            <th>Type</th>
                                            <th>Date and Time</th>
                                            <th>Amount</th>
                                            <!-- <th>Action</th> -->
                                        </tr>
                                    </thead>
                                    <tbody id="confirm-transaction-list"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="mes" class="toast align-items-center" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div id="message" class="toast-body"></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <div id="user-detail-modal" class="modal fade" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header flex-column">
                    <button id="close-detail-form" type="button" class="btn-close bg-danger" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                    <h4 class="modal-title mx-auto">User detail</h4>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col">
                            <label>Id</label>
                            <div id="user-id" class="user-info"></div>
                        </div>
                        <div class="col">
                            <label>Name</label>
                            <div id="user-name" class="user-info"></div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <label>Phone number</label>
                            <div id="user-phone" class="user-info"></div>
                        </div>
                        <div class="col">
                            <label>Email</label>
                            <div id="user-email" class="user-info"></div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <label>Date of birth</label>
                            <div id="user-birthday" class="user-info"></div>
                        </div>
                        <div class="col">
                            <label>Address</label>
                            <div id="user-address" class="user-info"></div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col">
                            <label>Front of identity card</label>
                            <img id="user-front" src="" alt="" class="card-img"></img>
                        </div>
                        <div class="col">
                            <label>Back of identity card</label>
                            <img id=user-back src="" alt="" class="card-img"></img>
                        </div>
                    </div>
                    <div id="verify-btn-group" class="row">
                        <div class="col">
                            <button id="verify-btn" class="btn btn-outline-success w-100">Verify</button>
                        </div>
                        <div class="col">
                            <button id="update-info-btn" class="btn btn-outline-info w-100">Update</button>
                        </div>
                        <div class="col">
                            <button id="cancel-btn" class="btn btn-outline-danger w-100">Cancel</button>
                        </div>
                    </div>
                    <div id="unlock-btn-wrap" class="row justify-content-center">
                        <div class="col-6">
                            <button id="unlock-btn" class="btn btn-outline-success w-100">Unlock</button>
                        </div>
                    </div>
                    <div id="trans-his-btn-wrap" class="row justify-content-center">
                        <div class="col-6">
                            <button id="trans-his-btn" class="btn btn-outline-success w-100">Transaction history</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="trans-his-modal" class="modal fade" data-bs-backdrop="static" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header flex-column">
                    <button id="close-trans-form" type="button" class="btn-close bg-danger" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                    <h4 class="modal-title mx-auto">Transaction History</h4>
                </div>
                <div class="modal-body">
                    <div id="history-container">
                        <div id="table-container">
                            <table id="table" class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Day & Time</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="trans-his-list"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="confirm-dialog" class="modal fade" data-bs-backdrop="static" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header flex-column">
                    <button id="close-pass-form" type="button" class="btn-close bg-danger" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                    <h4 class="modal-title mx-auto"></h4>
                </div>
                <div class="modal-body">
                    <div id="verify-btn-group" class="row">
                        <div class="col-6">
                            <button id="cancel-btn" class="btn btn-outline-secondary w-100">Cancel</button>
                        </div>
                        <div class="col-6">
                            <button id="accept-btn" class="btn btn-outline-info w-100">Accept</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
        integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script src="../main.js"></script>
</body>

</html>