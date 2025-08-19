<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Az Furniture - Step 1</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #fff;
            height: 100vh;
            position: relative;
            overflow: hidden;
            /* Prevent scrolling */
        }

        .onboard-container {
            width: 100%;
            height: 100%;
            position: relative;
            padding-top: 44px;
            /* Height of status bar */
        }

        .slide {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            text-align: center;
            gap: 20px;
        }

        .image-container {
            width: 100%;
            max-width: 280px;
            aspect-ratio: 1;
            border-radius: 50%;
            overflow: hidden;
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slide-text {
            max-width: 280px;
            padding: 0 10px;
        }

        h2 {
            font-size: 18px;
            color: #333;
            line-height: 1.4;
        }

        .skip-button {
            position: absolute;
            bottom: 30px;
            left: 20px;
            padding: 12px 24px;
            color: #666;
            text-decoration: none;
            font-size: 18px;
            z-index: 10;
        }

        .next-button {
            position: absolute;
            bottom: 25px;
            right: 20px;
            width: 50px;
            height: 50px;
            background-color: #00897B;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 10;
            text-decoration: none;
        }

        .next-button svg {
            width: 20px;
            height: 20px;
            fill: white;
        }

        .status-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 44px;
            background: white;
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
    </style>
</head>

<body>
    <div class="onboard-container">
        <div class="slide">
            <div class="image-container">
                <img src="assets\images\slide1.png" alt="Furniture View">
            </div>
            <div class="slide-text">
                <h2>View And Experience Furniture With The Help Of Augmented Reality</h2>
            </div>
        </div>

        <!-- <div class="dots-container">
            <span class="dot active"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div> -->

        <a href="index.php" class="skip-button">Skip</a>
        <a href="login.php" class="next-button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z" />
            </svg>
        </a>
    </div>
</body>

</html>