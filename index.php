<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'officer':
            header("Location: officer.php");
            break;
        case 'medical':
            header("Location: medical.php");
            break;
        case 'visitor':
            header("Location: visitor.php");
            break;
        case 'admin':
        default:
            header("Location: dashboard.php");
    }
    exit();
}

$error = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Hardcoded credentials
    $users = [
        'admin'   => 'admin123',
        'officer' => 'officer123',
        'medical' => 'medical123',
        'visitor' => 'visitor123'
    ];

    if (isset($users[$username]) && $users[$username] === $password) {
        $_SESSION['role'] = $username;

        // Redirect based on role
        switch ($username) {
            case 'officer':
                header("Location: officer.php");
                break;
            case 'medical':
                header("Location: medical.php");
                break;
            case 'visitor':
                header("Location: visitor.php");
                break;
            case 'admin':
            default:
                header("Location: dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Multi-role Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('https://media.istockphoto.com/id/157569394/photo/prison-cells.jpg?s=612x612&w=0&k=20&c=im4yiOLHO1ttjysrYkw5ArP9u7AeKDvClGF90jZe7Xw=') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #333;
            text-align: center;
            width: 350px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .signup-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .signup-link a {
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        h2 {
            margin-bottom: 20px;
        }

        input[type="submit"] {
            background-color: #333;
            color: white;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Login</h2>
    <form method="post">
        <label>Username (Role):</label>
        <select name="username" required>
            <option value="">-- Select Role --</option>
            <option value="admin">Admin</option>
            <option value="officer">Officer</option>
            <option value="medical">Medical</option>
            <option value="visitor">Visitor</option>
        </select>

        <label>Password:</label>
        <input type="password" name="password" required>

        <input type="submit" value="Login">
    </form>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="signup-link">
        Don't have an account? <a href="signup.php">Sign Up Here</a>
    </div>
</div>
</body>
</html>
