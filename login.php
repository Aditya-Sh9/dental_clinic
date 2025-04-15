<?php
session_start();
if(isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #F5FDFF 0%, #E8F5E9 100%);
        }
        
        .login-card {
            box-shadow: 0 10px 25px -5px rgba(46, 125, 50, 0.1), 0 10px 10px -5px rgba(46, 125, 50, 0.04);
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .login-card:hover {
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
    <div class="login-card max-w-md w-full bg-white">
        <!-- Header with logo and title -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 py-6 px-8 text-center">
            <div class="flex items-center justify-center space-x-3">
                <div class="bg-white p-2 rounded-full">
                    <i class="fas fa-tooth text-green-600 text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white">Toothly Clinic</h1>
            </div>
            <p class="mt-2 text-green-100">Staff Login Portal</p>
        </div>
        
        <!-- Form section -->
        <div class="p-8">
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg flex items-start">
                    <i class="fas fa-exclamation-circle text-red-500 mt-1 mr-3"></i>
                    <div>
                        <p class="font-medium">Login Failed</p>
                        <p class="text-sm">Invalid email or password</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['signup'])): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg flex items-start">
                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                    <div>
                        <p class="font-medium">Registration Successful!</p>
                        <p class="text-sm">Please login with your credentials</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form action="auth.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="email">
                        <i class="fas fa-envelope text-green-600 mr-1"></i> Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="email" name="email" id="email" 
                               class="input-field w-full pl-10 pr-3 py-3 rounded-lg focus:outline-none" 
                               placeholder="your@email.com" required>
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
                               placeholder="••••••••" required>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                    <!-- <div class="text-sm">
                        <a href="forgot-password.php" class="font-medium text-green-600 hover:text-green-500">
                            Forgot password?
                        </a>
                    </div> -->
                </div>
                
                <button type="submit" class="btn-primary w-full text-white font-semibold py-3 px-4 rounded-lg shadow-md flex items-center justify-center">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login to Dashboard
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600 text-sm">
                    New to Toothly? 
                    <a href="signup.php" class="font-medium text-green-600 hover:text-green-500">
                        Create an account
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