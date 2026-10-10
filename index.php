<?php
session_start();
require_once "db.php";

// EXISTING ACCOUNTS
$existing_accounts = [
    "admin" => [
        "password" => "admin123",
        "role"     => "admin"
    ],
    "Marwin" => [
        "password" => "Mawi093005",
        "role"     => "admin"
    ],
    "Renzon" => [
        "password" => "Renz101005",
        "role"     => "user"
    ],
    "Randolp" => [
        "password" => "Dolp062506",
        "role"     => "user"
    ],
    "Danmar" => [
        "password" => "DM122505",
        "role"     => "user"
    ]
];

// Redirect if already logged in
if (isset($_SESSION["user_id"], $_SESSION["role"])) {
    header("Location: " . ($_SESSION["role"] === "admin" ? "home2.php" : "home.php"));
    exit;
}

$error = "";
$success = "";
$page = $_GET["page"] ?? "login";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    // REGISTRATION
    if ($action === "register") {
        $page = "register";
        $confirm = $_POST["confirm_password"] ?? "";

        if (!$username || !$password || !$confirm) {
            $error = "All fields are required.";
        } elseif (array_key_exists($username, $existing_accounts)) {
            $error = "Username already exists in system accounts.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $error = "Username must be 3-50 characters (letters, numbers, underscores).";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            // Check Database
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $check->bind_param("s", $username);
            $check->execute();
            
            if ($check->get_result()->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
                $stmt->bind_param("ss", $username, $hashedPassword);
                
                if ($stmt->execute()) {
                    $success = "Registration successful. You can now log in.";
                    $page = "login";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }

    // LOGIN
    } elseif ($action === "login") {
        $page = "login";

        if (!$username || !$password) {
            $error = "Please fill in all fields.";
        } else {
            // 1. CHECK HARDCODED EXISTING ACCOUNTS FIRST
            if (array_key_exists($username, $existing_accounts) && $password === $existing_accounts[$username]["password"]) {
                session_regenerate_id(true);
                $_SESSION["user_id"] = 999;
                $_SESSION["username"] = $username;
                $_SESSION["role"] = $existing_accounts[$username]["role"];

                header("Location: " . ($_SESSION["role"] === "admin" ? "home2.php" : "home.php"));
                exit;
            }

            // 2. CHECK DATABASE IF NOT IN HARDCODED ARRAY
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user && password_verify($password, $user["password"])) {
                session_regenerate_id(true);
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                header("Location: " . ($user["role"] === "admin" ? "home2.php" : "home.php"));
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Registration</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --red-theme: #dc3545;
            --red-dark: #b02a37;
        }
        body {
            background-color: #f8f9fa;
        }
        .bg-red-gradient {
            background: linear-gradient(135deg, #dc3545 0%, #842029 100%);
        }
        .btn-red {
            background-color: var(--red-theme);
            color: #fff;
            border: none;
        }
        .btn-red:hover {
            background-color: var(--red-dark);
            color: #fff;
        }
        .text-red {
            color: var(--red-theme) !important;
        }
        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }
        .card {
            border-top: 4px solid var(--red-theme) !important;
        }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>

                    <h2 class="card-title text-center mb-4 fw-bold text-red"><?= $page === "register" ? "Register" : "Login" ?></h2>

                    <form method="POST" action="index.php<?= $page === "register" ? "?page=register" : "" ?>">
                        <input type="hidden" name="action" value="<?= $page ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control" maxlength="50" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="pass" class="form-control" required>
                                <button class="btn btn-outline-danger toggle-pass" type="button" data-target="pass"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>

                        <?php if ($page === "register"): ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" name="confirm_password" id="conf_pass" class="form-control" required>
                                    <button class="btn btn-outline-danger toggle-pass" type="button" data-target="conf_pass"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-red w-100 mb-3 py-2 fw-semibold"><?= $page === "register" ? "Register" : "Login" ?></button>
                    </form>

                    <p class="text-center mb-0">
                        <?php if ($page === "register"): ?>
                            Already registered? <a href="index.php" class="text-red fw-bold text-decoration-none">Login here</a>
                        <?php else: ?>
                            Don't have an account? <a href="index.php?page=register" class="text-red fw-bold text-decoration-none">Register here</a>
                        <?php endif; ?>
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.toggle-pass').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = document.getElementById(this.dataset.target);
        const icon = this.querySelector('i');
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
});
</script>
</body>
</html>