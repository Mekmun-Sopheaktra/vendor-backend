<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .header {
            background-color: #4CAF50;
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            background-image: url('https://scontent.fpnh24-1.fna.fbcdn.net/v/t39.30808-6/430858256_1352785256117051_7182731757430222831_n.jpg?stp=cp6_dst-jpg&_nc_cat=101&ccb=1-7&_nc_sid=833d8c&_nc_eui2=AeENogrPXARYCBZUnIsj0JiDeOeoCbHbejh456gJsdt6ONRMwGw1ezj9ThvTCkM6n0iSE07PWBHIHjN0T2TFKBme&_nc_ohc=KHo7vjrph1gQ7kNvgFYCsBb&_nc_ht=scontent.fpnh24-1.fna&_nc_gid=AepRxPW1kc5PnQsJ6t7_2ni&oh=00_AYC37HREvfeOsP1gdPv8gCdyJi5BzhLb2BDEwG5Oq3yB3w&oe=670C33ED');
            background-size: cover;
            background-position: center -340px; /* Move image down by 30px */
        }

        .header img {
            max-width: 150px;
            border-radius: 50%;
            margin-top: 10px;
        }

        .header h1 {
            margin: 20px 0;
            font-size: 24px;
        }

        .content {
            padding: 30px;
            text-align: center;
        }

        .content img {
            border-radius: 20px;
            max-width: 150px;
            margin: 0 auto 20px;
            display: block;
        }

        .content h1 {
            color: #333;
            font-size: 24px;
        }

        .content p {
            color: #777;
            font-size: 16px;
            margin: 20px 0;
        }

        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: #ffffff;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-size: 16px;
            margin-top: 20px;
        }

        .footer {
            background-color: #f4f4f4;
            color: #777;
            text-align: center;
            padding: 10px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="email-container">
    <!-- Header with background image -->
    <div class="header">
        <img src="https://shop.mekmunsopheaktra.com/assets/imgs/logo/logo.png" alt="Logo">
        <h1>Welcome to Our Community!</h1>
    </div>

    <div class="content">
        <!-- Decorative Image in Content -->
        <img
            src="https://scontent.fpnh24-1.fna.fbcdn.net/v/t39.30808-6/461844509_1519631089432466_5227853746064995418_n.jpg?stp=c0.169.1536.1536a_dst-jpg_s206x206&_nc_cat=102&ccb=1-7&_nc_sid=50ad20&_nc_eui2=AeGX0fOqciaJIymyzBUMis5qqpcU_b2IyMSqlxT9vYjIxFpFtj6ZxoMer6P33eenuj86Jpvc4wLY6lo47lEV51Zj&_nc_ohc=tGXkgwYzOrgQ7kNvgGxsthI&_nc_ht=scontent.fpnh24-1.fna&_nc_gid=AhuTXndOM8OuzcR1BkoRrFM&oh=00_AYAU_OGzoSbgVouQ2SclGH5ImW8Op1JLd8HYw2Z88dD_LQ&oe=670C2E5E"
            alt="Decorative Image">

        <h1>Email Verification</h1>
        <p>Hi {{ $username }},</p>
        <p>Thank you for registering! Please verify your email address by clicking the button below:</p>

        <a href="{{ $verificationUrl }}" class="btn" style="color: #ffffff">Verify Email</a>

        <p>If you did not create an account, no further action is required.</p>
    </div>

    <div class="footer">
        <p>&copy; 2024 {{$appName}}. All rights reserved.</p>
    </div>
</div>

</body>
</html>
