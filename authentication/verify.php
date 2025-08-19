<?php
require '../config/mail.php';
require '../config/database.php';
require_once 'check_session.php';


$email = $_SESSION['email'];
// Check if the email is already registered
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$is_verified = $result->fetch_assoc()['is_email_verified'];

if ($is_verified == 1) {
    header("Location: login.php");
    exit();
}

// Generate a random 6-digit OTP
$otp = rand(100000, 999999);

// Send OTP to the user's email
$to = $email;
$subject = "OTP Verification";
$message = "Your OTP is: $otp";

// Send the email using PHPMailer
$mailer->sendMail($to, $subject, $message);

// Store the OTP in db
$stmt = $conn->prepare("INSERT INTO otp (email, otp) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$stmt->close();

$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate OTP
    $enteredOTP = (int) $_POST['otp'];

    // Retrieve the stored OTP from the database
    $stmt = $conn->prepare("SELECT otp, email FROM otp WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    // echo json_encode($row);
    $storedOTP = $row['otp'];
    $storedEmail = $row['email'];

    // Check if the entered OTP matches the stored OTP
    if ($enteredOTP === $storedOTP && $email === $storedEmail) {
        // OTP is valid, change is_verified to 1
        $stmt = $conn->prepare("UPDATE users SET is_email_verified = 1 WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Delete the OTP from the database
        $stmt = $conn->prepare("DELETE FROM otp WHERE email =?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        // Set success message
        $_SESSION['success_message'] = "Email verified successfully!";
        
        // redirect to login page
        header("Location: login.php");
        exit();
    }
}


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
</head>

<body>
    <div class="container">
        <a href="login.php" class="back-button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
            </svg>
        </a>

        <h1>Verify</h1>
        <p class="subtitle">Please enter your otp send to your email</p>

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
                <label for="otp">OTP</label>
                <input type="text" name="otp" id="otp" placeholder="Enter your otp her" required>
            </div>

            <button type="submit" class="sign-up-btn">Verify</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
