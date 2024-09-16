<?php
session_start();

require 'vendor/autoload.php';
require 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$errors = [];
$response = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Password validation
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || preg_match('/[^A-Za-z0-9]/', $password)) {
        $response['status'] = 'error';
        $response['message'] = 'Password should be 8 characters long, a combination of uppercase and lowercase letters with numbers, and no special characters.';
        echo json_encode($response);
        exit;
    }

    // Password match check
    if ($password !== $cpassword) {
        $response['status'] = 'error';
        $response['message'] = 'Passwords do not match!';
        echo json_encode($response);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $verify_code = rand(100000, 999999); // Generate OTP code

    // Check if email already exists in the account table
    $stmt = $pdo->prepare("SELECT * FROM account WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Email already exists!';
    } else {
        // Insert into accounts table
        $stmt = $pdo->prepare("INSERT INTO account (email, password, verify) VALUES (?, ?, ?)");
        if ($stmt->execute([$email, $password_hash, 0])) { // 0 for unverified account
            
            // Store the user's email in session
            $_SESSION['email'] = $email; 
            
            // Insert email and verification code into otp_verification table
            $stmt = $pdo->prepare("INSERT INTO otp_verification (email, verify_code) VALUES (?, ?)");
            $stmt->execute([$email, $verify_code]);

            // PHPMailer: Sending email verification code
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'marksribsandsteakdiner@gmail.com'; // Your email
                $mail->Password   = 'pwjagyhypjfbzsid'; // Use app-specific password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('marksribsandsteakdiner@gmail.com', 'OTP VERIFICATION');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email Address';
                $mail->Body    = "Please verify your email by using this code: <b>$verify_code</b>";

                $mail->send();

                $response['status'] = 'success';
                $response['message'] = 'Signup successful! Please check your email for verification.';
                $response['redirect'] = "verify.php";
            } catch (Exception $e) {
                $response['status'] = 'error';
                $response['message'] = 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo;
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error during signup.';
        }
    }

    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
    <style>
          .password-wrapper {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="card p-4 shadow-sm">
                <h3 class="text-center mb-4">Sign Up</h3>

                <!-- Signup Form -->
                <form id="signupForm" method="post" action="signup.php">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                     <!-- Password Field with Show/Hide -->
                     <div class="mb-3 password-field" id="passwordField">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                            <span class="input-group-text show-password" id="togglePassword"><i class="fa fa-eye"></i></span>
                        </div>
                    </div>

                    <!-- Confirm Password Field with Show/Hide -->
                    <div class="mb-3 password-field" id="cpasswordField">
                        <label for="cpassword" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" name="cpassword" id="cpassword" class="form-control" placeholder="Confirm your password" required>
                            <span class="input-group-text show-password" id="toggleCPassword"><i class="fa fa-eye"></i></span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Sign Up</button>
                </form> <br>
                <div class="mb-3 text-center">
                    <p>Already have an account? <a href="index.php">Login Here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>

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

    document.getElementById('toggleCPassword').addEventListener('click', function() {
        const cpasswordInput = document.getElementById('cpassword');
        const icon = this.querySelector('i');
        if (cpasswordInput.type === 'password') {
            cpasswordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            cpasswordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    
document.getElementById('signupForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the form from submitting normally

    const formData = new FormData(this);

    fetch('signup.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message
            }).then(() => {
                window.location.href = data.redirect; // Use data.redirect to navigate to verify page
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred.'
        });
    });
});
</script>
</body>
</html>
