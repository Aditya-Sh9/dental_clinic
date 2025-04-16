<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get patients and their insurance
$patients = $conn->query("SELECT p.id, p.name, pi.id as insurance_id, ip.name as provider_name 
                         FROM patients p
                         LEFT JOIN patient_insurance pi ON p.id = pi.patient_id
                         LEFT JOIN insurance_providers ip ON pi.provider_id = ip.id
                         ORDER BY p.name");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $conn->real_escape_string($_POST['patient_id']);
    $insurance_id = $conn->real_escape_string($_POST['insurance_id']);
    $procedure_date = $conn->real_escape_string($_POST['procedure_date']);
    $procedure_code = $conn->real_escape_string($_POST['procedure_code']);
    $procedure_description = $conn->real_escape_string($_POST['procedure_description']);
    $fee = $conn->real_escape_string($_POST['fee']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    // Insert claim (make insurance_id optional)
    $sql = "INSERT INTO insurance_claims 
            (patient_id, insurance_id, claim_date, procedure_date, procedure_code, procedure_description, fee, status, notes)
            VALUES ('$patient_id', " . ($insurance_id ? "'$insurance_id'" : "NULL") . ", NOW(), '$procedure_date', '$procedure_code', '$procedure_description', '$fee', 'pending', '$notes')";
    
    if($conn->query($sql)) {
        $claim_id = $conn->insert_id;
        
        // Handle document upload if present
        if(!empty($_FILES['document']['name'])) {
            $upload_dir = 'uploads/claim_docs/';
            if(!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_name = basename($_FILES['document']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_file_name = "claim_{$claim_id}_" . time() . ".$file_ext";
            $file_path = $upload_dir . $new_file_name;
            
            $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
            if(in_array($file_ext, $allowed_types)) {
                if(move_uploaded_file($_FILES['document']['tmp_name'], $file_path)) {
                    // Only include insurance_id if it exists
                    $doc_sql = "INSERT INTO insurance_documents 
                               (patient_id, claim_id, document_type, file_name, file_path" . 
                               ($insurance_id ? ", insurance_id" : "") . ")
                               VALUES ('$patient_id', '$claim_id', 'claim_form', '$file_name', '$file_path'" .
                               ($insurance_id ? ", '$insurance_id'" : "") . ")";
                    $conn->query($doc_sql);
                }
            }
        }
        
        $_SESSION['success'] = "Claim submitted successfully";
        header("Location: view_claim.php?id=$claim_id");
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
    <title>New Claim - Toothly</title>
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
        }
        .bg-green-600 {
            background-color: #4CAF50;
        }
        .bg-green-700 {
            background-color: #2E7D32;
        }
        .text-green-900 {
            color: #1B5E20;
        }
        .text-green-200 {
            color: #C8E6C9;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-green-900">New Insurance Claim</h1>
                <a href="insurance.php" class="text-green-600 hover:text-green-800 font-medium flex items-center">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Insurance
                </a>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="max-w-3xl bg-white p-6 rounded-xl shadow-md border-l-4 border-green-600">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="patient_id">Patient *</label>
                        <select name="patient_id" id="patient_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                            <option value="">Select Patient</option>
                            <?php while($patient = $patients->fetch_assoc()): ?>
                            <option value="<?= $patient['id'] ?>" data-insurance="<?= $patient['insurance_id'] ?>">
                                <?= htmlspecialchars($patient['name']) ?> (<?= htmlspecialchars($patient['provider_name']) ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="insurance_id">Insurance Policy *</label>
                        <select name="insurance_id" id="insurance_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                            <option value="">Select Insurance Policy</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="procedure_date">Procedure Date *</label>
                        <input type="date" name="procedure_date" id="procedure_date" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="procedure_code">Procedure Code *</label>
                        <input type="text" name="procedure_code" id="procedure_code" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    </div>
                    
                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="procedure_description">Procedure Description *</label>
                        <textarea name="procedure_description" id="procedure_description" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="fee">Fee ($) *</label>
                        <input type="number" step="0.01" name="fee" id="fee" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="document">Claim Form (Optional)</label>
                        <input type="file" name="document" id="document" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <p class="text-xs text-gray-500 mt-1">Allowed file types: PDF, JPG, PNG (Max 5MB)</p>
                    </div>
                    
                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="notes">Notes</label>
                        <textarea name="notes" id="notes" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition mt-6">
                    <i class="fas fa-paper-plane mr-2"></i> Submit Claim
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Update insurance dropdown when patient is selected
        document.getElementById('patient_id').addEventListener('change', function() {
            const insuranceSelect = document.getElementById('insurance_id');
            insuranceSelect.innerHTML = '<option value="">Select Insurance Policy</option>';
            
            if(this.value) {
                const selectedOption = this.options[this.selectedIndex];
                const insuranceId = selectedOption.getAttribute('data-insurance');
                
                if(insuranceId) {
                    const newOption = document.createElement('option');
                    newOption.value = insuranceId;
                    newOption.textContent = selectedOption.textContent.match(/\((.*?)\)/)[1];
                    insuranceSelect.appendChild(newOption);
                }
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>