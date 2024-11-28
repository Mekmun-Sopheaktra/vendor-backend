<!DOCTYPE html>
<html
    lang="en"
    class="light-style customizer-hide"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../assets/"
    data-template="vertical-menu-template-free"
>
<head>
    <meta charset="utf-8"/>
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Login | panel</title>

    <meta name="description" content=""/>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico"/>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet"
    />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{asset("assets/vendor/fonts/boxicons.css")}}"/>

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{asset("assets/vendor/css/core.css")}}" class="template-customizer-core-css"/>
    <link rel="stylesheet" href="{{asset("assets/vendor/css/theme-default.css")}}"
          class="template-customizer-theme-css"/>
    <link rel="stylesheet" href="{{asset("assets/css/demo.css")}}"/>

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{asset("assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css")}}"/>

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="{{asset("assets/vendor/css/pages/page-auth.css")}}"/>
    <!-- Helpers -->
    <script src="{{asset("assets/vendor/js/helpers.js")}}"></script>

    <script src="{{asset("assets/js/config.js")}}"></script>
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 500px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0px 8px 24px rgba(0, 0, 0, 0.15);
            padding: 30px;
            margin-top: 50px;
        }

        h2 {
            color: #007bff;
            font-weight: bold;
        }

        .form-label {
            font-weight: bold;
            color: #495057;
        }

        .form-control {
            border-radius: 10px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-size: 16px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .alert {
            margin-bottom: 20px;
        }

        .card-info {
            text-align: center;
            margin-bottom: 20px;
        }

        .card-info img {
            width: 60px;
            margin-bottom: 10px;
        }

        .card-info p {
            margin: 0;
            font-size: 16px;
            color: #6c757d;
        }

        .click-to-pay {
            align-content: center;
            align-items: center;
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background-color: #4eaaff;
            border-radius: 10px;
            cursor: pointer;
            border: 1px solid #e9ecef;
        }
    </style>
</head>
<body>


<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <!-- Register -->
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center mb-4">Payment Transaction</h2>

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="card-info">
                        <img src="https://cdn-icons-png.flaticon.com/512/825/825529.png" alt="Credit Card">
                        <p>We accept Visa, MasterCard, and more.</p>
                    </div>
                    <button onclick="document.getElementById('payment-form').classList.remove('d-none')" class="btn btn-primary">Click to Enter Payment Info</button>

                    <form id="payment-form" class="d-none" action="{{ route('processTransaction') }}" method="POST">
                        @csrf

                        <div class="mb-3 mt-4">
                            <label for="card_holder_name" class="form-label">Card Holder Name</label>
                            <input type="text" class="form-control" id="card_holder_name" name="card_holder_name" placeholder="La SAMYOUNG"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number"
                                   placeholder="XXXX XXXX XXXX XXXX" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="card_expiry" class="form-label">Expiration Date</label>
                                <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YYYY"
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="card_cvc" class="form-label">CVC</label>
                                <input type="text" class="form-control" id="card_cvc" name="card_cvc" placeholder="CVC" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Pay Now</button>
                    </form>
                </div>
            </div>
            <!-- /Register -->
        </div>
    </div>
</div>

<script>
    // Scroll to form when clicking to enter payment info
    document.querySelector('.click-to-pay').addEventListener('click', function () {
        document.getElementById('payment-form').scrollIntoView({behavior: 'smooth'});
    });
</script>
<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{asset("assets/vendor/libs/jquery/jquery.js")}}"></script>
<script src="{{asset("assets/vendor/libs/popper/popper.js")}}"></script>
<script src="{{asset("assets/vendor/js/bootstrap.js")}}"></script>
<script src="{{asset("assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js")}}"></script>

<script src="{{asset("assets/vendor/js/menu.js")}}"></script>
<!-- endbuild -->

<!-- Vendors JS -->

<!-- Main JS -->
<script src="{{asset("assets/js/main.js")}}"></script>

</body>
</html>
