<?php
/**
 * Student/Admin Login Page
 * Handles authentication for both students and admins
 */

require_once 'config.php';
startSession();

$error = '';

// If user is already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } elseif (isFaculty()) {
        header('Location: faculty/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required!';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email format!';
    } else {
        try {
            $pdo = getDBConnection();

            // Check user credentials
            $stmt = $pdo->prepare("SELECT stu_id, name, email, password, role FROM students WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['stu_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } elseif ($user['role'] === 'faculty') {
                    header('Location: faculty/dashboard.php');
                } else {
                    header('Location: user/dashboard.php');
                }
                exit();
            } else {
                $error = 'Invalid email or password!';
            }
        } catch (PDOException $e) {
            $error = 'Login failed! Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Login - University</title>
    <style>
        .login-container {
            max-width: 500px;
            margin: 5rem auto;
            padding: 3rem;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(108, 64, 64, 0.2);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #6c4040;
            font-weight: 600;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #6c4040;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Source Sans Pro', sans-serif;
        }
        .alert-error {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="./ChatGPT Image Oct 1, 2025, 10_03_37 AM.png" class="logo" alt="University Logo">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="program.html">Programs</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php">Signup</a></li>
        </ul>
    </div>

    <div class="login-container">
        <h1 style="font-family: 'Cinzel', serif; color: #6c4040; text-align: center; margin-bottom: 2rem;">Login</h1>
        
        <?php if ($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem;">
            Don't have an account? <a href="signup.php" style="color: #6c4040; font-weight: 600;">Sign up here</a>
        </p>
    </div>
</body>
</html>

