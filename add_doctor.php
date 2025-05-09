<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

$errors = [];
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $specialty = trim($_POST['specialty']);
    $color = trim($_POST['color']);
    
    // Validation
    if(empty($name)) {
        $errors['name'] = 'Doctor name is required';
    }
    
    if(empty($specialty)) {
        $errors['specialty'] = 'Specialty is required';
    }
    
    if(empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO doctors (name, specialty, color) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $specialty, $color);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Doctor added successfully";
            header("Location: doctors.php");
            exit();
        } else {
            $errors['database'] = "Error adding doctor: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Doctor - Toothly</title>
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Custom green shades */
        .bg-green-100 {
            background-color: #E8F5E9;
        }
        .text-green-800 {
            color: #2E7D32;
        }
        .border-green-600 {
            border-color: #4CAF50;
        }
        .text-green-600 {
            color: #4CAF50;
        }/*
        .bg-green-600 {
            background-color: #4CAF50;
        }
        .bg-green-700 {
            background-color: #2E7D32;
        } */
        /* .text-green-900 {
            color: #1B5E20;
        }
        .text-green-200 {
            color: #C8E6C9;
        } */
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-green-900">Add New Doctor</h1>
                <a href="doctors.php" class="text-green-700 hover:text-green-800 font-medium flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Doctors
                </a>
            </div>
            
            <?php if(!empty($errors['database'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $errors['database'] ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-600">
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-green-500 focus:border-green-500 <?= !empty($errors['name']) ? 'border-red-500' : 'border-gray-300' ?>">
                            <?php if(!empty($errors['name'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?= $errors['name'] ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label for="specialty" class="block text-sm font-medium text-gray-700 mb-1">Specialty *</label>
                            <input type="text" id="specialty" name="specialty" value="<?= htmlspecialchars($_POST['specialty'] ?? '') ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-green-500 focus:border-green-500 <?= !empty($errors['specialty']) ? 'border-red-500' : 'border-gray-300' ?>">
                            <?php if(!empty($errors['specialty'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?= $errors['specialty'] ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Color Code</label>
                            <div class="flex items-center">
                                <input type="color" id="color-picker" name="color" value="<?= htmlspecialchars($_POST['color'] ?? '#4CAF50') ?>" 
                                       class="h-10 w-10 rounded cursor-pointer mr-2">
                                <input type="text" id="color" name="color" value="<?= htmlspecialchars($_POST['color'] ?? '#4CAF50') ?>" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Hex color code (e.g., #4CAF50)</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg shadow transition">
                            <i class="fas fa-save mr-2"></i> Save Doctor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Sync color picker and text input
        document.getElementById('color-picker').addEventListener('input', function() {
            document.getElementById('color').value = this.value;
        });
        
        document.getElementById('color').addEventListener('input', function() {
            document.getElementById('color-picker').value = this.value;
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>