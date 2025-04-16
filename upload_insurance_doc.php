<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

$insurance_id = isset($_GET['insurance_id']) ? intval($_GET['insurance_id']) : 0;

// Get insurance info using prepared statement
$stmt = $conn->prepare("SELECT pi.*, p.name as patient_name 
                       FROM patient_insurance pi
                       JOIN patients p ON pi.patient_id = p.id
                       WHERE pi.id = ?");
$stmt->bind_param("i", $insurance_id);
$stmt->execute();
$insurance = $stmt->get_result()->fetch_assoc();

if(!$insurance) {
    $_SESSION['error'] = "Invalid insurance record";
    header("Location: insurance.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $document_type = $conn->real_escape_string($_POST['document_type']);
    $patient_id = $insurance['patient_id'];
    
    // File upload handling
    $upload_dir = 'uploads/insurance_docs/';
    if(!is_dir($upload_dir)) {
        if(!mkdir($upload_dir, 0755, true)) {
            $error = "Failed to create upload directory";
        }
    }
    
    // Check if file was uploaded without errors
    if(isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['document']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = "insurance_{$insurance_id}_" . time() . ".$file_ext";
        $file_path = $upload_dir . $new_file_name;
        
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        
        // Validate file
        if($_FILES['document']['size'] > $max_file_size) {
            $error = "File size exceeds maximum limit of 5MB";
        } elseif(!in_array($file_ext, $allowed_types)) {
            $error = "Invalid file type. Allowed types: " . implode(', ', $allowed_types);
        } else {
            if(move_uploaded_file($_FILES['document']['tmp_name'], $file_path)) {
                // Use prepared statement for database insert
                $sql = "INSERT INTO insurance_documents 
                       (patient_id, insurance_id, document_type, file_name, file_path)
                       VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisss", $patient_id, $insurance_id, $document_type, $file_name, $file_path);
                
                if($stmt->execute()) {
                    $_SESSION['success'] = "Document uploaded successfully";
                    header("Location: view_insurance.php?id=$insurance_id");
                    exit();
                } else {
                    $error = "Database error: " . $conn->error;
                    // Clean up the uploaded file if database insert fails
                    if(file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
            } else {
                $error = "Error moving uploaded file. Check directory permissions.";
            }
        }
    } else {
        $error = "File upload error: " . $this->getUploadError($_FILES['document']['error']);
    }
}

function getUploadError($error_code) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    return $errors[$error_code] ?? 'Unknown upload error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Document - Toothly</title>
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
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-green-900">Upload Insurance Document</h1>
                <a href="view_insurance.php?id=<?= $insurance_id ?>" class="text-green-600 hover:text-green-800 font-medium flex items-center">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Insurance
                </a>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 border-l-4 border-green-600">
                <h2 class="text-lg font-semibold text-green-900 mb-2">Insurance Policy</h2>
                <p class="text-gray-700"><span class="font-medium">Patient:</span> <?= htmlspecialchars($insurance['patient_name']) ?></p>
                <p class="text-gray-700"><span class="font-medium">Policy #:</span> <?= htmlspecialchars($insurance['policy_number']) ?></p>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="max-w-md bg-white p-6 rounded-xl shadow-md border-l-4 border-green-600">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="document_type">Document Type *</label>
                    <select name="document_type" id="document_type" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                        <option value="">Select Type</option>
                        <option value="insurance_card">Insurance Card</option>
                        <option value="claim_form">Claim Form</option>
                        <option value="eob">Explanation of Benefits</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="document">Document *</label>
                    <input type="file" name="document" id="document" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    <p class="text-xs text-gray-500 mt-1">Allowed file types: PDF, JPG, PNG (Max 5MB)</p>
                </div>
                
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition">
                    <i class="fas fa-upload mr-2"></i> Upload Document
                </button>
            </form>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>