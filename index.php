<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .password-field, .captcha-field {
            display: none;
        }
        .show-password {
            cursor: pointer;
        }
        .bg-gray {
            background-color: #B2BEB5;
        }
        .g-recaptcha {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-gray">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="card p-4 shadow-sm">
                <h3 class="text-center mb-4">LOGIN</h3>

                <!-- Login Form -->
                <form method="POST" action="login.php" id="loginForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                    
                    <!-- Initially hidden password field -->
                    <div class="mb-3 password-field" id="passwordField">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                            <span class="input-group-text show-password" id="togglePassword"><i class="fa fa-eye"></i></span>
                        </div>
                    </div>

                    <!-- Hidden reCAPTCHA field -->
                    <div class="captcha-field" id="captchaField">
                        <div class="g-recaptcha" data-sitekey="6Lcmr0IqAAAAAH2DG5pQpsxyR2VEfKtGZ855WiTf"></div>
                    </div>

                    <button type="button" class="btn btn-primary w-100" id="continueButton">Continue</button>
                    <button type="submit" class="btn btn-primary w-100 mt-3" name="login" id="loginButton" style="display: none;">Login</button>
                </form> <br>

                <div class="mb-3 text-center">
                    <p>Don't have an account? <a href="signup.php">Create an Account</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>
    // Password field toggle and email validation
    document.getElementById('continueButton').addEventListener('click', function() {
        const emailInput = document.getElementById('email').value;
        if (!emailInput) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Please enter your email first!',
            });
        } else {
            document.getElementById('passwordField').style.display = 'block';
            document.getElementById('captchaField').style.display = 'block'; // Show reCAPTCHA
            document.getElementById('loginButton').style.display = 'block';
            this.style.display = 'none'; // Hide the "Continue" button
        }
    });

    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Display SweetAlert based on session message
    <?php if (isset($_SESSION['message'])): ?>
        Swal.fire({
            icon: '<?php echo $_SESSION['message']['icon']; ?>',
            title: '<?php echo $_SESSION['message']['title']; ?>',
            text: '<?php echo $_SESSION['message']['text']; ?>'
        }).then((result) => {
            if (result.isConfirmed || result.isDismissed) {
                window.location.href = '<?php echo $_SESSION['message']['redirect']; ?>';
            }
        });
        <?php unset($_SESSION['message']); ?>

    <?php endif; ?>

    <?php if (isset($_SESSION['message'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Signed up successfully!',
        text: 'Please log in.'
    });
    <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

</script>

</body>
</html>
