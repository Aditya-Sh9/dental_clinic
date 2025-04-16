<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $website = $conn->real_escape_string($_POST['website']);

    $sql = "INSERT INTO insurance_providers (name, address, phone, email, website) 
            VALUES ('$name', '$address', '$phone', '$email', '$website')";

    if($conn->query($sql)) {
        $_SESSION['success'] = "Insurance provider added successfully";
        header("Location: insurance.php");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Provider - Toothly</title>
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">Add Insurance Provider</h1>
                <a href="insurance.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Insurance
                </a>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="max-w-2xl bg-white p-6 rounded-xl shadow-md">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="name">Provider Name *</label>
                        <input type="text" name="name" id="name" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="address">Address</label>
                        <input type="text" name="address" id="address" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="phone">Phone</label>
                        <input type="tel" name="phone" id="phone" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="email">Email</label>
                        <input type="email" name="email" id="email" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="website">Website</label>
                        <input type="url" name="website" id="website" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition mt-6">
                    <i class="fas fa-save mr-2"></i> Save Provider
                </button>
            </form>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>