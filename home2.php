<?php
session_start();

if (!isset($_SESSION["user_id"], $_SESSION["role"])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit;
}

if (isset($_GET["logout"])) {
    $_SESSION = [];
    session_destroy();

    header("Location: index.php");
    exit;
}

$username = htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Homepage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-dark-red {
            background: linear-gradient(135deg, #842029 0%, #b02a37 100%) !important;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark-red shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">Admin Portal</a>
        <div class="d-flex">
            <a href="home2.php?logout=1" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4" style="border-left: 5px solid #842029 !important;">
                <div class="card-body p-4">
                    <h1 class="card-title h3 mb-3">Welcome, Admin <?= $username ?>!</h1>
                    <p class="card-text text-secondary">You are logged in as an administrator.</p>
                    <span class="badge bg-danger">Role: admin</span>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="h5 card-title fw-bold text-danger">Admin Dashboard</h3>
                    <p class="card-text text-muted mb-0">You have access to administrator-only content and settings.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>