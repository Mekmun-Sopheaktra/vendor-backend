<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank for Your Joining with Us As Vendor</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #333;
            font-size: 24px;
        }

        p {
            font-size: 16px;
            color: #555;
            line-height: 1.5;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Welcome, {{ $name }}!</h1>
    <p>Your vendor account has been created successfully.</p>
    <p><strong>Temporary Password:</strong> {{ $password }}</p>
    <p>Please log in and change your password as soon as possible.</p>
    <p>Thank you for joining us!</p>
</div>
</body>
</html>
