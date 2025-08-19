<?php
// database connection

require '../config/database.php';

// Form validation functions
function validateName($name)
{
    return preg_match('/^[a-zA-Z\s]{2,50}$/', $name);
}

function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password)
{
    // Minimum 12 characters
    if (strlen($password) < 12) return false;

    // Check for uppercase, lowercase, numbers and special characters
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) return false;

    return true;
}

$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Name validation
    if (!validateName($name)) {
        $errors['name'] = "Please enter a valid name (only letters and spaces allowed)";
    }

    // Email validation
    if (!validateEmail($email)) {
        $errors['email'] = "Please enter a valid email address";
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors['email'] = "This email is already registered";
    }
    $stmt->close();

    // Password validation
    if (!validatePassword($password)) {
        $errors['password'] = "Password must contain: minimum 12 characters, uppercase and lowercase letters, numbers, and special characters";
    }

    // Proceed if no errors
    if (empty($errors)) {
        // Hash password using MD5
        $hashed_password = md5($password);

        // Insert user data
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role, is_active) VALUES (?, ?, ?, 'user', TRUE)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);


        if ($stmt->execute()) {
            // Get the newly created user's ID
            $user_id = $stmt->insert_id;

            // Start session and set user data
            session_start();
            $_SESSION['success_message'] = "Account created successfully!, Please verify your email to continue.";

            $_SESSION['email'] = $email;
            // Redirect to login page instead of home page
            header("Location: verify.php");
            exit();
        } else {
            $errors['db'] = "Error creating account. Please try again.";
        }
        $stmt->close();
    }
}

// Show success message if exists
session_start();
if (isset($_SESSION['success_message'])) {
    echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['success_message'], ENT_QUOTES) . "</div>";
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Az Furniture</title>
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

            input[type="text"],
            input[type="email"],
            input[type="password"] {
                padding: 12px;
                font-size: 16px;
            }

            .sign-up-btn {
                padding: 12px;
            }

            .google-sign-up {
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
            color: #ffffff;
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

        input[type="text"],
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

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #00897B;
        }

        input::placeholder {
            color: #999;
        }

        .sign-up-btn {
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

        .sign-up-btn:hover {
            background-color: #007366;
        }

        .google-sign-up {
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

        .google-sign-up:hover {
            background-color: #f5f5f5;
        }

        .google-sign-up img {
            width: 20px;
            height: 20px;
        }

        .login-prompt {
            text-align: center;
            color: #ffffff;
            font-size: clamp(12px, 2.5vw, 14px);
        }

        .login-link {
            color: #999;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .login-link:hover {
            color: #00897B;
        }

        /* Tablet Styles */
        @media (min-width: 768px) {
            .container {
                padding: 40px;
                padding-top: calc(44px + 30px);
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

            input[type="text"],
            input[type="email"],
            input[type="password"] {
                padding: 0.75rem;
                font-size: 0.875rem;
            }

            .sign-up-btn {
                padding: 0.75rem;
                font-size: 0.875rem;
                margin-bottom: 1rem;
            }

            .google-sign-up {
                padding: 0.75rem;
                font-size: 0.875rem;
                margin-bottom: 1rem;
            }

            .login-prompt {
                font-size: 0.875rem;
                margin-top: 0.75rem;
                margin-bottom: 0;
            }

            .status-bar {
                display: none;
            }
        }

        .text-muted {
            color: #ffffff;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="login.php" class="back-button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
            </svg>
        </a>

        <h1>Create Account</h1>
        <p class="subtitle">Let's Create Account Together</p>

        <!-- <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin-bottom: 0;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?> -->

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="Enter Your Name"
                    value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter Your Email"
                    value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="sign-up-btn">Sign Up</button>
        </form>

        <a href="#" class="google-sign-up">
            <img src="../assets/images/google icon.png" alt="Google">
            Sign Up With Google
        </a>

        <p class="login-prompt">
            Already Have An Account? <a href="login.php" class="login-link">Sign In</a>
        </p>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>


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

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 12px;
            font-size: 16px;
        }

        .sign-up-btn {
            padding: 12px;
        }

        .google-sign-up {
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
        color: #ffffff;
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

    input[type="text"],
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

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus {
        border-color: #00897B;
    }

    input::placeholder {
        color: #999;
    }

    .sign-up-btn {
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

    .sign-up-btn:hover {
        background-color: #007366;
    }

    .google-sign-up {
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

    .google-sign-up:hover {
        background-color: #f5f5f5;
    }

    .google-sign-up img {
        width: 20px;
        height: 20px;
    }

    .login-prompt {
        text-align: center;
        color: #ffffff;
        font-size: clamp(12px, 2.5vw, 14px);
    }

    .login-link {
        color: #999;
        text-decoration: none;
        font-weight: bold;
        transition: color 0.3s;
    }

    .login-link:hover {
        color: #00897B;
    }

    /* Tablet Styles */
    @media (min-width: 768px) {
        .container {
            padding: 40px;
            padding-top: calc(44px + 30px);
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

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 0.75rem;
            font-size: 0.875rem;
        }

        .sign-up-btn {
            padding: 0.75rem;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .google-sign-up {
            padding: 0.75rem;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .login-prompt {
            font-size: 0.875rem;
            margin-top: 0.75rem;
            margin-bottom: 0;
        }

        .status-bar {
            display: none;
        }
    }

    .text-muted {
        color: #ffffff;
    }
</style>

<!-- Custom Alert Modal -->
<style>
    /* Custom Alert Modal Styles */
    .alert-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        backdrop-filter: blur(4px);
    }

    .custom-alert {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #1E1E1E;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        z-index: 10000;
        width: 90%;
        max-width: 350px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .alert-title {
        color: #ffffff;
        font-size: 1.25rem;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .alert-message {
        color: #B0B0B0;
        margin-bottom: 25px;
        font-size: 1rem;
        line-height: 1.5;
    }

    .alert-buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    .alert-btn {
        padding: 10px 25px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .alert-btn-ok {
        background: #00897B;
        color: white;
        flex: 1;
        max-width: 120px;
    }

    .alert-btn-ok:hover {
        background: #007366;
    }

    .alert-btn-cancel {
        background: #333333;
        color: white;
        flex: 1;
        max-width: 120px;
    }

    .alert-btn-cancel:hover {
        background: #444444;
    }
</style>

<body>
    <div class="container">
        <a href="login.php" class="back-button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
            </svg>
        </a>

        <h1>Create Account</h1>
        <p class="subtitle">Let's Create Account Together</p>

        <!-- <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin-bottom: 0;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?> -->

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="Enter Your Name"
                    value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter Your Email"
                    value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="sign-up-btn">Sign Up</button>
        </form>

        <a href="#" class="google-sign-up">
            <img src="../assets/images/google icon.png" alt="Google">
            Sign Up With Google
        </a>

        <p class="login-prompt">
            Already Have An Account? <a href="login.php" class="login-link">Sign In</a>
        </p>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>


<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();

        const nameInput = document.querySelector('input[name="fullname"]');
        const emailInput = document.querySelector('input[name="email"]');
        const passwordInput = document.querySelector('input[name="password"]');

        // Name validation
        if (!/^[a-zA-Z\s]{2,50}$/.test(nameInput.value.trim())) {
            alert("Please enter a valid name (only letters and spaces allowed)");
            nameInput.focus();
            return;
        }

        // Email validation
        if (!emailInput.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            alert("Please enter a valid email address");
            emailInput.focus();
            return;
        }

        // Password validation
        const password = passwordInput.value;
        if (
            password.length < 12 ||
            !/[A-Z]/.test(password) ||
            !/[a-z]/.test(password) ||
            !/[0-9]/.test(password) ||
            !/[!@#$%^&*(),.?":{}|<>]/.test(password)
        ) {
            alert("Password must contain:\n• Minimum 12 characters\n• Uppercase letters\n• Lowercase letters\n• Numbers\n• Special characters");
            passwordInput.focus();
            return;
        }

        // If all validations pass, submit the form
        this.submit();
    });
</script>
</body>

</html>