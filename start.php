<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Az Furniture</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #00897B;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Arial', sans-serif;
        }

        .splash-container {
            text-align: center;
            padding: 20px;
            animation: fadeIn 1.5s ease-in;
        }

        .logo-icon {
            width: 120px;
            height: auto;
            margin-bottom: 20px;
        }

        .brand-name {
            color: white;
            font-size: 2.5rem;
            margin: 20px 0;
            font-weight: 300;
        }

        .enter-btn {
            display: inline-block;
            padding: 12px 40px;
            background-color: white;
            color: #00897B;
            text-decoration: none;
            border-radius: 25px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            margin-top: 30px;
        }

        .enter-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Responsive Design */
        @media (max-width: 768px) {
            .splash-container {
                padding: 15px;
            }

            .logo-icon {
                width: 100px;
            }

            .brand-name {
                font-size: 2rem;
            }

            .enter-btn {
                padding: 10px 30px;
                font-size: 1rem;
            }
        }
    </style>
    <script>
        // Wait for the page to load
        document.addEventListener('DOMContentLoaded', function() {
            // Set timeout for 2 seconds (2000 milliseconds)
            setTimeout(function() {
                // Redirect to the main page
                window.location.href = 'index.php';
            }, 3000);
        });
    </script>
</head>

<body>
    <div class="splash-container">
        <img src="assets\images\Logo.svg" alt="Az Furniture Logo" class="logo-icon">
        <!-- <h1 class="brand-name">Az Furniture</h1> -->
    </div>
</body>

</html>