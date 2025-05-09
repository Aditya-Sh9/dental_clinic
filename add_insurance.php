<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get patients and providers
$patients = $conn->query("SELECT id, name FROM patients ORDER BY name");
$providers = $conn->query("SELECT id, name FROM insurance_providers ORDER BY name");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $conn->real_escape_string($_POST['patient_id']);
    $provider_id = $conn->real_escape_string($_POST['provider_id']);
    $policy_number = $conn->real_escape_string($_POST['policy_number']);
    $group_number = $conn->real_escape_string($_POST['group_number']);
    $coverage_start = $conn->real_escape_string($_POST['coverage_start']);
    $coverage_end = $conn->real_escape_string($_POST['coverage_end']);
    $annual_max = $conn->real_escape_string($_POST['annual_max']);
    $deductible = $conn->real_escape_string($_POST['deductible']);

    $sql = "INSERT INTO patient_insurance 
            (patient_id, provider_id, policy_number, group_number, coverage_start, coverage_end, annual_max, remaining_balance, deductible, deductible_met)
            VALUES ('$patient_id', '$provider_id', '$policy_number', '$group_number', '$coverage_start', '$coverage_end', '$annual_max', '$annual_max', '$deductible', '0')";

    if($conn->query($sql)) {
        $_SESSION['success'] = "Insurance added successfully";
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
    <title>Add Insurance - Toothly</title>
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F5F5;
        }
    </style>
    <style>
        .form-input {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            width: 100%;
            transition: all 0.2s;
        }
        .form-input:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
            outline: none;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-green-900">Add Insurance Policy</h1>
                <a href="insurance.php" class="text-green-600 hover:text-green-800 font-medium flex items-center">
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
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="patient_id">Patient *</label>
                        <select name="patient_id" id="patient_id" class="form-input" required>
                            <option value="">Select Patient</option>
                            <?php while($patient = $patients->fetch_assoc()): ?>
                            <option value="<?= $patient['id'] ?>"><?= htmlspecialchars($patient['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="provider_id">Provider *</label>
                        <select name="provider_id" id="provider_id" class="form-input" required>
                            <option value="">Select Provider</option>
                            <?php while($provider = $providers->fetch_assoc()): ?>
                            <option value="<?= $provider['id'] ?>"><?= htmlspecialchars($provider['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="policy_number">Policy Number *</label>
                        <input type="text" name="policy_number" id="policy_number" class="form-input" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="group_number">Group Number</label>
                        <input type="text" name="group_number" id="group_number" class="form-input">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="coverage_start">Coverage Start *</label>
                        <input type="date" name="coverage_start" id="coverage_start" class="form-input" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="coverage_end">Coverage End *</label>
                        <input type="date" name="coverage_end" id="coverage_end" class="form-input" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="annual_max">Annual Max ($) *</label>
                        <input type="number" step="0.01" name="annual_max" id="annual_max" class="form-input" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="deductible">Deductible ($) *</label>
                        <input type="number" step="0.01" name="deductible" id="deductible" class="form-input" required>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition mt-6">
                    <i class="fas fa-save mr-2"></i> Save Insurance Policy
                </button>
            </form>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>