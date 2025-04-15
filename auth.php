<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "dental_clinic";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password']; // Don't escape the password

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify the password against the stored hash
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ];
            header("Location: dashboard.php");
            exit();
        }
    }
    
    // If we get here, authentication failed
    header("Location: login.php?error=1");
    exit();
}

$conn->close();
?>