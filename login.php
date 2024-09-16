<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "elec"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare the SQL query
    $stmt = $conn->prepare("SELECT * FROM account WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            if ($user['verify'] == 1) {
                $_SESSION['message'] = [
                    'icon' => 'success',
                    'title' => 'Login Successful!',
                    'text' => 'You will be redirected to your dashboard.',
                    'redirect' => 'Dashboard.php'
                ];
            } else {
                $_SESSION['message'] = [
                    'icon' => 'warning',
                    'title' => 'Email Not Verified!',
                    'text' => 'Please verify your email first.',
                    'redirect' => 'index.php'
                ];
            }
        } else {
            $_SESSION['message'] = [
                'icon' => 'error',
                'title' => 'Invalid Credentials!',
                'text' => 'The email or password you entered is incorrect.',
                'redirect' => 'index.php'
            ];
        }
    } else {
        $_SESSION['message'] = [
            'icon' => 'error',
            'title' => 'No Account Found!',
            'text' => 'No account found with that email address.',
            'redirect' => 'index.php'
        ];
    }

    $stmt->close(); // Close the prepared statement
    header("Location: index.php"); // Redirect to index.php
    exit();
}

$conn->close(); // Close the connection
