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

// Get associated documents
$documents = $conn->query("SELECT * FROM insurance_documents 
                          WHERE insurance_id = $insurance_id 
                          ORDER BY upload_date DESC");

// Format dates for display
$coverage_start = date('m/d/Y', strtotime($insurance['coverage_start']));
$coverage_end = date('m/d/Y', strtotime($insurance['coverage_end']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance Details - Toothly</title>
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
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
                <h1 class="text-2xl font-bold text-green-900">Insurance Policy Details</h1>
                <a href="insurance.php" class="text-green-600 hover:text-green-800 font-medium flex items-center">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Insurance
                </a>
            </div>
            
            <!-- Success/Error Messages -->
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
            
            <!-- Policy Information -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8 border-l-4 border-green-600">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-green-900 mb-2">Policy Information</h2>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Patient:</span> <?= htmlspecialchars($insurance['patient_name']) ?></p>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Provider:</span> <?= htmlspecialchars($insurance['provider_name']) ?></p>
                        <p class="text-gray-700"><span class="font-medium">Policy #:</span> <?= htmlspecialchars($insurance['policy_number']) ?></p>
                    </div>
                    
                    <div>
                        <h2 class="text-lg font-semibold text-green-900 mb-2">Coverage Details</h2>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Coverage Period:</span> <?= $coverage_start ?> to <?= $coverage_end ?></p>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Annual Max:</span> $<?= number_format($insurance['annual_max'], 2) ?></p>
                        <p class="text-gray-700"><span class="font-medium">Remaining Balance:</span> $<?= number_format($insurance['remaining_balance'], 2) ?></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-lg font-semibold text-green-900 mb-2">Deductible Information</h2>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Deductible:</span> $<?= number_format($insurance['deductible'], 2) ?></p>
                        <p class="text-gray-700"><span class="font-medium">Deductible Met:</span> $<?= number_format($insurance['deductible_met'], 2) ?></p>
                    </div>
                    
                    <div>
                        <h2 class="text-lg font-semibold text-green-900 mb-2">Additional Information</h2>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Group #:</span> <?= $insurance['group_number'] ? htmlspecialchars($insurance['group_number']) : '--' ?></p>
                        <p class="text-gray-700"><span class="font-medium">Status:</span> 
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                Active
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Documents Section -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-600">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-green-900">Policy Documents</h2>
                    <a href="upload_insurance_doc.php?insurance_id=<?= $insurance_id ?>" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Document
                    </a>
                </div>
                
                <?php if($documents->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php while($doc = $documents->fetch_assoc()): 
                            $icon = [
                                'insurance_card' => 'fa-id-card',
                                'claim_form' => 'fa-file-alt',
                                'eob' => 'fa-file-invoice-dollar',
                                'other' => 'fa-file'
                            ][$doc['document_type']];
                        ?>
                        <div class="border rounded-lg p-4 hover:shadow-md transition bg-white">
                            <div class="flex items-center mb-2">
                                <i class="fas <?= $icon ?> text-green-600 text-xl mr-3"></i>
                                <div>
                                    <h3 class="font-medium text-green-900"><?= ucfirst(str_replace('_', ' ', $doc['document_type'])) ?></h3>
                                    <p class="text-xs text-gray-500"><?= date('m/d/Y H:i', strtotime($doc['upload_date'])) ?></p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-700 mb-3 truncate"><?= htmlspecialchars($doc['file_name']) ?></p>
                            <div class="flex space-x-2">
                                <a href="<?= $doc['file_path'] ?>" target="_blank" class="text-green-600 hover:text-green-800 text-sm">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                <a href="<?= $doc['file_path'] ?>" download class="text-green-600 hover:text-green-800 text-sm">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-file-alt text-gray-300 text-5xl mb-4"></i>
                        <p class="text-gray-500">No documents uploaded for this policy</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>