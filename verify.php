<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_otp = $_POST["verify_code"];
    $email = $_SESSION['email']; // Retrieve the email from the session

    // Ensure the OTP is not empty
    if (!empty($user_otp)) {
        // Query the 'otp_verification' table using PDO to check if the provided OTP matches
        $query = "SELECT * FROM otp_verification WHERE verify_code = :otp AND email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['otp' => $user_otp, 'email' => $email]);

        if ($stmt->rowCount() == 1) {
            // OTP matched, get the user's email
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_email = $row['email'];

            // Update the 'verify' column in the 'account' table using PDO
            $update_query = "UPDATE account SET verify = 1 WHERE email = :email";
            $update_stmt = $pdo->prepare($update_query);
            if ($update_stmt->execute(['email' => $user_email])) {
                $_SESSION['sweetalert'] = [
                    'icon' => 'success',
                    'title' => 'Verification Successful',
                    'text' => 'Your account has been verified. You can now log in.',
                    'redirect' => 'index.php'
                ];
                header("Location: verify.php");
                exit();
            } else {
                $_SESSION['sweetalert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error updating verification status: ' . $pdo->errorInfo()[2]
                ];
            }
        } else {
            $_SESSION['sweetalert'] = [
                'icon' => 'error',
                'title' => 'Invalid OTP',
                'text' => 'The verification code you entered is incorrect.'
            ];
        }
    } else {
        $_SESSION['sweetalert'] = [
            'icon' => 'warning',
            'title' => 'Missing OTP',
            'text' => 'Please enter a verification code.'
        ];
    }
    header("Location: verify.php");
    exit();
}

if (isset($_SESSION['sweetalert'])) {
    $sweetalert = $_SESSION['sweetalert'];
    unset($_SESSION['sweetalert']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="card p-4 shadow-sm">
                <h3 class="text-center mb-4">Verify Your Email</h3>

                <!-- Verify Form -->
                <form id="verifyForm" method="post" action="verify.php">
                    <div class="mb-3">
                        <label for="verify_code" class="form-label">Verification Code</label>
                        <input type="text" id="verify_code" name="verify_code" class="form-control" placeholder="Enter your verification code" required>
                        <h3>Please Input Verification Code sent to </h3>
                        <span style="display:block; text-align:center;"> <b><?php echo $_SESSION['email']; ?></span></b>

                    </div>
                    <button type="submit" class="btn btn-primary w-100">Verify</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($sweetalert)) : ?>
    <script>
        Swal.fire({
            icon: '<?php echo $sweetalert['icon']; ?>',
            title: '<?php echo $sweetalert['title']; ?>',
            text: '<?php echo $sweetalert['text']; ?>',
            confirmButtonText: 'OK'
        }).then((result) => {
            <?php if (isset($sweetalert['redirect'])) : ?>
            if (result.isConfirmed) {
                window.location.href = '<?php echo $sweetalert['redirect']; ?>'; // Redirect to login page
            }
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

</body>
</html>
