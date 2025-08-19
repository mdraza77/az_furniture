<?php
session_start();

require '../../config/database.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($errors)) {
        try {
            $check_email = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
            $check_email->bind_param("s", $email);
            $check_email->execute();
            $email_result = $check_email->get_result();
            
            if ($email_result->num_rows === 0) {
                $errors['login'] = "Email is not registered";
            } else {
                $stmt = $conn->prepare("SELECT id, full_name, email, role FROM admin_users WHERE email = ? AND password_hash = ? AND is_active = 1");
                $hashed_password = md5($password);
                $stmt->bind_param("ss", $email, $hashed_password);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if ($user) {
                    // Start session with admin-specific prefix
                    $_SESSION['admin_data'] = [
                        'id' => $user['id'],
                        'name' => $user['full_name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'type' => 'admin'
                    ];
                    
                    $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                    $update_stmt->bind_param("i", $user['id']);
                    $update_stmt->execute();
                    
                    header("Location: ../index.php");
                    exit();
                }
            }
        } catch (Exception $e) {
            $errors['login'] = "Login error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Az Furniture</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <div class="container">
        <a href="../index.php" class="back-button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
            </svg>
        </a>

        <h1>Admin Login</h1>
        <p class="subtitle">Please enter your admin credentials</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your email" required value="mdraza8297@gmail.com">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required value="@Mdraza012345">
            </div>

            <div class="remember-forgot">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    Remember for 30 days
                </label>
                <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
            </div>

            <button type="submit" class="sign-in-btn">Login</button>
        </form>
    </div>
</body>

</html>