<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

$insurance_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get insurance policy details
$stmt = $conn->prepare("SELECT pi.*, p.name as patient_name, ip.name as provider_name 
                       FROM patient_insurance pi
                       JOIN patients p ON pi.patient_id = p.id
                       JOIN insurance_providers ip ON pi.provider_id = ip.id
                       WHERE pi.id = ?");
$stmt->bind_param("i", $insurance_id);
$stmt->execute();
$insurance = $stmt->get_result()->fetch_assoc();

if(!$insurance) {
    $_SESSION['error'] = "Insurance policy not found";
    header("Location: insurance.php");
    exit();
}

// Get all patients and providers for dropdowns
$patients = $conn->query("SELECT id, name FROM patients ORDER BY name");
$providers = $conn->query("SELECT id, name FROM insurance_providers ORDER BY name");

// Get associated documents
$documents = $conn->query("SELECT * FROM insurance_documents 
                          WHERE insurance_id = $insurance_id 
                          ORDER BY upload_date DESC");

// Handle document deletion
if(isset($_GET['delete_doc'])) {
    $doc_id = intval($_GET['delete_doc']);
    
    // Get document info before deleting
    $doc_stmt = $conn->prepare("SELECT file_path FROM insurance_documents WHERE id = ? AND insurance_id = ?");
    $doc_stmt->bind_param("ii", $doc_id, $insurance_id);
    $doc_stmt->execute();
    $document = $doc_stmt->get_result()->fetch_assoc();
    
    if($document) {
        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM insurance_documents WHERE id = ?");
        $delete_stmt->bind_param("i", $doc_id);
        
        if($delete_stmt->execute()) {
            // Delete the actual file
            if(file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }
            $_SESSION['success'] = "Document deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting document";
        }
    }
    header("Location: edit_insurance.php?id=$insurance_id");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle document upload
    if(isset($_FILES['new_document']) && $_FILES['new_document']['error'] === UPLOAD_ERR_OK) {
        $document_type = $conn->real_escape_string($_POST['document_type']);
        $patient_id = $insurance['patient_id'];
        
        $upload_dir = 'uploads/insurance_docs/';
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = basename($_FILES['new_document']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = "insurance_{$insurance_id}_" . time() . ".$file_ext";
        $file_path = $upload_dir . $new_file_name;
        
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        
        if($_FILES['new_document']['size'] > $max_file_size) {
            $error = "File size exceeds maximum limit of 5MB";
        } elseif(!in_array($file_ext, $allowed_types)) {
            $error = "Invalid file type. Allowed types: " . implode(', ', $allowed_types);
        } else {
            if(move_uploaded_file($_FILES['new_document']['tmp_name'], $file_path)) {
                $sql = "INSERT INTO insurance_documents 
                       (patient_id, insurance_id, document_type, file_name, file_path)
                       VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisss", $patient_id, $insurance_id, $document_type, $file_name, $file_path);
                
                if(!$stmt->execute()) {
                    $error = "Error uploading document: " . $conn->error;
                    unlink($file_path);
                } else {
                    $_SESSION['success'] = "Document uploaded successfully";
                }
            } else {
                $error = "Error moving uploaded file";
            }
        }
    }
    
    // Handle policy updates
    $patient_id = $conn->real_escape_string($_POST['patient_id']);
    $provider_id = $conn->real_escape_string($_POST['provider_id']);
    $policy_number = $conn->real_escape_string($_POST['policy_number']);
    $group_number = $conn->real_escape_string($_POST['group_number']);
    $coverage_start = $conn->real_escape_string($_POST['coverage_start']);
    $coverage_end = $conn->real_escape_string($_POST['coverage_end']);
    $annual_max = $conn->real_escape_string($_POST['annual_max']);
    $deductible = $conn->real_escape_string($_POST['deductible']);
    $remaining_balance = $conn->real_escape_string($_POST['remaining_balance']);
    $deductible_met = $conn->real_escape_string($_POST['deductible_met']);

    $sql = "UPDATE patient_insurance SET
            patient_id = ?,
            provider_id = ?,
            policy_number = ?,
            group_number = ?,
            coverage_start = ?,
            coverage_end = ?,
            annual_max = ?,
            remaining_balance = ?,
            deductible = ?,
            deductible_met = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssssdddi", 
        $patient_id,
        $provider_id,
        $policy_number,
        $group_number,
        $coverage_start,
        $coverage_end,
        $annual_max,
        $remaining_balance,
        $deductible,
        $deductible_met,
        $insurance_id
    );

    if($stmt->execute()) {
        $_SESSION['success'] = "Insurance policy updated successfully";
        header("Location: view_insurance.php?id=$insurance_id");
        exit();
    } else {
        $error = "Error updating policy: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Insurance - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(219, 234, 254, 0.5);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.7);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(37, 99, 235, 0.9);
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Translucent effects */
        .translucent-card {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">Edit Insurance Policy</h1>
                <a href="view_insurance.php?id=<?= $insurance_id ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Policy
                </a>
            </div>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="max-w-3xl bg-white p-6 rounded-xl shadow-md">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="patient_id">Patient *</label>
                        <select name="patient_id" id="patient_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Patient</option>
                            <?php while($patient = $patients->fetch_assoc()): ?>
                            <option value="<?= $patient['id'] ?>" <?= $patient['id'] == $insurance['patient_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($patient['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="provider_id">Provider *</label>
                        <select name="provider_id" id="provider_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Provider</option>
                            <?php while($provider = $providers->fetch_assoc()): ?>
                            <option value="<?= $provider['id'] ?>" <?= $provider['id'] == $insurance['provider_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($provider['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="policy_number">Policy Number *</label>
                        <input type="text" name="policy_number" id="policy_number" 
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="<?= htmlspecialchars($insurance['policy_number']) ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="group_number">Group Number</label>
                        <input type="text" name="group_number" id="group_number" 
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="<?= htmlspecialchars($insurance['group_number']) ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="coverage_start">Coverage Start *</label>
                        <input type="date" name="coverage_start" id="coverage_start" 
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="<?= $insurance['coverage_start'] ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="coverage_end">Coverage End *</label>
                        <input type="date" name="coverage_end" id="coverage_end" 
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="<?= $insurance['coverage_end'] ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="annual_max">Annual Max ($) *</label>
                        <input type="number" step="0.01" name="annual_max" id="annual_max" 
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="<?= $insurance['annual_max'] ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="remaining_balance">Remaining Balance ($) *</label>
                        <input type="number" step="0.01" name="remaining_balance" id="remaining_balance" 
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="<?= $insurance['remaining_balance'] ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="deductible">Deductible ($) *</label>
                        <input type="number" step="0.01" name="deductible" id="deductible" 
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="<?= $insurance['deductible'] ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="deductible_met">Deductible Met ($) *</label>
                        <input type="number" step="0.01" name="deductible_met" id="deductible_met" 
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="<?= $insurance['deductible_met'] ?>" required>
                    </div>
                </div>
                
                <!-- Documents Section -->
                <div class="mt-8 border-t pt-6">
                    <h2 class="text-xl font-semibold text-blue-800 mb-4">Policy Documents</h2>
                    
                    <!-- Document Upload Form -->
                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium text-blue-800 mb-3">Upload New Document</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="document_type">Document Type *</label>
                                <select name="document_type" id="document_type" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Type</option>
                                    <option value="insurance_card">Insurance Card</option>
                                    <option value="claim_form">Claim Form</option>
                                    <option value="eob">Explanation of Benefits</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="new_document">Document *</label>
                                <input type="file" name="new_document" id="new_document" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                <p class="text-xs text-gray-500 mt-1">Allowed file types: PDF, JPG, PNG (Max 5MB)</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Existing Documents List -->
                    <?php if($documents->num_rows > 0): ?>
                        <div class="space-y-3">
                            <h3 class="text-lg font-medium text-blue-800 mb-2">Existing Documents</h3>
                            <?php while($doc = $documents->fetch_assoc()): 
                                $icon = [
                                    'insurance_card' => 'fa-id-card',
                                    'claim_form' => 'fa-file-alt',
                                    'eob' => 'fa-file-invoice-dollar',
                                    'other' => 'fa-file'
                                ][$doc['document_type']];
                            ?>
                            <div class="flex items-center justify-between bg-white p-3 border rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas <?= $icon ?> text-blue-600 text-xl mr-3"></i>
                                    <div>
                                        <p class="font-medium"><?= htmlspecialchars($doc['file_name']) ?></p>
                                        <p class="text-xs text-gray-500"><?= ucfirst(str_replace('_', ' ', $doc['document_type'])) ?> - <?= date('m/d/Y H:i', strtotime($doc['upload_date'])) ?></p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="<?= $doc['file_path'] ?>" target="_blank" class="text-blue-600 hover:text-blue-800 p-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= $doc['file_path'] ?>" download class="text-green-600 hover:text-green-800 p-2">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="edit_insurance.php?id=<?= $insurance_id ?>&delete_doc=<?= $doc['id'] ?>" 
                                       class="text-red-600 hover:text-red-800 p-2"
                                       onclick="return confirm('Are you sure you want to delete this document?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 py-4">No documents uploaded for this policy</p>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition mt-6">
                    <i class="fas fa-save mr-2"></i> Update Insurance Policy
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const coverageStart = new Date(document.getElementById('coverage_start').value);
            const coverageEnd = new Date(document.getElementById('coverage_end').value);
            
            if(coverageStart >= coverageEnd) {
                alert('Coverage end date must be after start date');
                e.preventDefault();
                return;
            }
            
            const annualMax = parseFloat(document.getElementById('annual_max').value);
            const remainingBalance = parseFloat(document.getElementById('remaining_balance').value);
            
            if(remainingBalance > annualMax) {
                alert('Remaining balance cannot exceed annual maximum');
                e.preventDefault();
                return;
            }
            
            const deductible = parseFloat(document.getElementById('deductible').value);
            const deductibleMet = parseFloat(document.getElementById('deductible_met').value);
            
            if(deductibleMet > deductible) {
                alert('Deductible met cannot exceed deductible amount');
                e.preventDefault();
                return;
            }
            
            // Additional validation for file upload if present
            if(document.getElementById('new_document').files.length > 0) {
                const file = document.getElementById('new_document').files[0];
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if(!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Please upload a PDF, JPG, or PNG file.');
                    e.preventDefault();
                    return;
                }
                
                if(file.size > maxSize) {
                    alert('File size exceeds 5MB limit');
                    e.preventDefault();
                    return;
                }
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>