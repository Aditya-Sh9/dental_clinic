<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "dental_clinic";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password']; // Don't escape the password
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if email already exists using prepared statement
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "Email already registered";
        } else {
            // Use proper password hashing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert using prepared statement
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            
            if ($stmt->execute()) {
                header("Location: login.php?signup=success");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Sign Up - Toothly</title>
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #F5FDFF 0%, #E8F5E9 100%);
        }
        
        .signup-card {
            box-shadow: 0 10px 25px -5px rgba(46, 125, 50, 0.1), 0 10px 10px -5px rgba(46, 125, 50, 0.04);
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .signup-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(46, 125, 50, 0.1), 0 10px 10px -5px rgba(46, 125, 50, 0.04);
        }
        
        .btn-primary {
            transition: all 0.3s ease;
            background-image: linear-gradient(to right, #4CAF50, #2E7D32);
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(46, 125, 50, 0.3);
        }
        
        .input-field {
            transition: all 0.3s ease;
            border: 1px solid #E2E8F0;
        }
        
        .input-field:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="signup-card max-w-md w-full bg-white">
        <!-- Header with logo and title -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 py-6 px-8 text-center">
            <div class="flex items-center justify-center space-x-3">
                <div class="bg-white p-2 rounded-full">
                    <i class="fas fa-tooth text-green-600 text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white">Toothly Clinic</h1>
            </div>
            <p class="mt-2 text-green-100">Staff Registration</p>
        </div>
        
        <!-- Form section -->
        <div class="p-8">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg flex items-start">
                    <i class="fas fa-exclamation-circle text-red-500 mt-1 mr-3"></i>
                    <div>
                        <p class="font-medium">Registration Error</p>
                        <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="name">
                        <i class="fas fa-user text-green-600 mr-1"></i> Full Name
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-id-card text-gray-400"></i>
                        </div>
                        <input type="text" name="name" id="name" 
                               class="input-field w-full pl-10 pr-3 py-3 rounded-lg focus:outline-none" 
                               placeholder="John Doe" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="email">
                        <i class="fas fa-envelope text-green-600 mr-1"></i> Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-at text-gray-400"></i>
                        </div>
                        <input type="email" name="email" id="email" 
                               class="input-field w-full pl-10 pr-3 py-3 rounded-lg focus:outline-none" 
                               placeholder="your@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="password">
                        <i class="fas fa-lock text-green-600 mr-1"></i> Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-key text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" 
                               class="input-field w-full pl-10 pr-3 py-3 rounded-lg focus:outline-none" 
                               placeholder="••••••••" required minlength="8">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="confirm_password">
                        <i class="fas fa-lock text-green-600 mr-1"></i> Confirm Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-key text-gray-400"></i>
                        </div>
                        <input type="password" name="confirm_password" id="confirm_password" 
                               class="input-field w-full pl-10 pr-3 py-3 rounded-lg focus:outline-none" 
                               placeholder="••••••••" required minlength="8">
                    </div>
                </div>
                
                <button type="submit" class="btn-primary w-full text-white font-semibold py-3 px-4 rounded-lg shadow-md flex items-center justify-center">
                    <i class="fas fa-user-plus mr-2"></i> Create Account
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600 text-sm">
                    Already have an account? 
                    <a href="login.php" class="font-medium text-green-600 hover:text-green-500">
                        Login here
                    </a>
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="bg-gray-50 px-8 py-4 text-center border-t border-gray-200">
            <p class="text-xs text-gray-500">
                &copy; <?= date('Y') ?> Toothly Clinic. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>