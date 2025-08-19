<?php
// Database connection
require '../config/database.php';
require_once 'check_session.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($email)) {
        $errors['email'] = "Email is required";
    }
    if (empty($password)) {
        $errors['password'] = "Password is required";
    }

    if (empty($errors)) {
        // Hash password for comparison
        $hashed_password = md5($password);
        
        // Check user credentials
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password_hash = ? AND is_active = TRUE");
        $stmt->bind_param("ss", $email, $hashed_password);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $is_verified = $user['is_email_verified'];
        // echo json_encode($user);
        // echo $is_verified;
        // exit();

        if (!$is_verified) {
            $errors['login'] = "Email is not verified. Please check your email for verification.";

            $_SESSION['email'] = $email;

            // Redirect to verification page
            header("Location: verify.php");
            exit();
        }  else {
            if($result->num_rows > 0) {
                // Start session with user-specific prefix
                $_SESSION['user_data'] = [
                    'id' => $user['id'],
                    'name' => $user['full_name'],
                    'email' => $user['email'],
                    'type' => 'user'
                ];
                
                header("Location: ../index.php");
                exit();
            } else {
                $errors['login'] = "Invalid email or password";
            }
            $stmt->close();
        }
        
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Back - Az Furniture</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #121212;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            color: #fff;
            display: flex;
            flex-direction: column;
        }

        .status-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 44px;
            background: #1E1E1E;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
            z-index: 1000;
        }

        .time {
            font-weight: 500;
        }

        .status-icons {
            display: flex;
            gap: 5px;
        }

        .container {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
            padding: 20px;
            padding-top: calc(44px + 16px);
            height: 100vh;
            overflow-y: auto;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .container::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }

        /* Mobile Styles */
        @media (max-width: 767px) {
            .container {
                padding: 15px;
                padding-top: calc(44px + 10px);
                height: calc(100vh - 44px);
            }

            .form-group {
                margin-bottom: 20px;
            }

            input[type="email"],
            input[type="password"] {
                padding: 12px;
                font-size: 16px;
            }

            .remember-forgot {
                margin-bottom: 20px;
            }

            .sign-in-btn {
                padding: 12px;
            }

            .google-sign-in {
                padding: 12px;
            }

            h1 {
                font-size: 24px;
            }

            .subtitle {
                font-size: 14px;
                margin-bottom: 25px;
            }
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: #ffffff;
            margin-bottom: 30px;
            transition: opacity 0.2s;
        }

        .back-button:hover {
            opacity: 0.7;
        }

        .back-button svg {
            width: 24px;
            height: 24px;
            fill: currentColor;
        }

        h1 {
            font-size: clamp(28px, 5vw, 32px);
            color: #fff;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #999;
            font-size: clamp(14px, 3vw, 16px);
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-size: clamp(14px, 3vw, 16px);
            color: #ffffff;
            margin-bottom: 10px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: clamp(14px, 3vw, 16px);
            outline: none;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #00897B;
        }

        input[type="email"]::placeholder,
        input[type="password"]::placeholder {
            color: #000;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 10px;
            color: #fff;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: clamp(12px, 2.5vw, 14px);
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #00897B;
        }

        .forgot-password {
            color: #fff;
            text-decoration: none;
            font-size: clamp(12px, 2.5vw, 14px);
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: #00897B;
        }

        .sign-in-btn {
            width: 100%;
            padding: 15px;
            background-color: #00897B;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: clamp(14px, 3vw, 16px);
            cursor: pointer;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }

        .sign-in-btn:hover {
            background-color: #007366;
        }

        .google-sign-in {
            width: 100%;
            padding: 15px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            color: #333;
            margin-bottom: 30px;
            transition: background-color 0.3s;
            font-size: clamp(14px, 3vw, 16px);
        }

        .google-sign-in:hover {
            background-color: #f5f5f5;
        }

        .google-sign-in img {
            width: 20px;
            height: 20px;
        }

        .signup-prompt {
            text-align: center;
            color: #fff;
            font-size: clamp(12px, 2.5vw, 14px);
        }

        .signup-link {
            color: #777;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .signup-link:hover {
            color: #00897B;
        }

        /* Tablet Styles */
        @media (min-width: 768px) {
            .container {
                padding: 40px;
                padding-top: calc(44px + 30px);
            }

            .remember-forgot {
                flex-wrap: nowrap;
            }
        }

        /* Desktop Styles */
        @media (min-width: 1024px) {
            body {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #121212;
            }

            .container {
                height: auto !important;
                min-height: auto;
                margin: 2rem auto;
                padding: 2rem;
                background-color: rgba(30, 30, 30, 0.9);
                border-radius: 1rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                max-width: 400px;
                width: 100%;
            }

            .back-button {
                margin-bottom: 1rem;
            }

            h1 {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }

            .subtitle {
                font-size: 0.875rem;
                margin-bottom: 1.5rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            label {
                font-size: 0.875rem;
                margin-bottom: 0.5rem;
            }

            input[type="email"],
            input[type="password"] {
                padding: 0.75rem;
                font-size: 0.875rem;
            }

            .remember-forgot {
                margin-bottom: 1rem;
                font-size: 0.875rem;
            }

            .sign-in-btn {
                padding: 0.75rem;
                font-size: 0.875rem;
                margin-bottom: 1rem;
            }

            .google-sign-in {
                padding: 0.75rem;
                font-size: 0.875rem;
                margin-bottom: 1rem;
            }

            .signup-prompt {
                font-size: 0.875rem;
                margin-top: 0.75rem;
                margin-bottom: 0;
            }

            .status-bar {
                display: none;
            }
        }

        /* Large Desktop Styles */
        @media (min-width: 1440px) {
            .container {
                max-width: 450px;
                padding: 35px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="onboard1.php" class="back-button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
            </svg>
        </a>

        <h1>Welcome Back</h1>
        <p class="subtitle">Welcome Back! Please Enter Your Details.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger p-1">
                <?php foreach ($errors as $error): ?>
                    <small><?php echo htmlspecialchars($error); ?></small>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter Your Email" required value="Ashmit13082004@gmail.com">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required value="Ashmit@123456">
            </div>

            <div class="remember-forgot">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    Remember For 30 Days
                </label>
                <a href="forgot_password.php" class="forgot-password">Forgot Password</a>
            </div>

            <button type="submit" class="sign-in-btn">Sign In</button>
        </form>

        <a href="#" class="google-sign-in">
            <img src="../assets/images/google icon.png" alt="Google">
            Sign In With Google
        </a>

        <p class="signup-prompt">
            Don't Have An Account? <a href="signup.php" class="signup-link">Sign Up</a>
        </p>
    </div>
</body>

</html>