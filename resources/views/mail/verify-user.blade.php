<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
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
        .code {
            font-size: 18px;
            font-weight: bold;
            color: #4CAF50;
            background-color: #eafaf1;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Verify Your Email Address from Bek Sloy Team</h2>
    <p>
        Thanks for creating an account with our verification app. Please enter the verification code below to confirm your email address:
    </p>
    <div class="code">{{ $code }}</div>
    <div class="footer">
        <p>Thanks,</p>
        <p>The Verification from Bek Sloy Team</p>
    </div>
</div>
</body>
</html>
