<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

$claim_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get claim details
$claim_query = $conn->query("SELECT c.*, p.name as patient_name, ip.name as provider_name
                            FROM insurance_claims c
                            JOIN patients p ON c.patient_id = p.id
                            JOIN patient_insurance pi ON c.insurance_id = pi.id
                            JOIN insurance_providers ip ON pi.provider_id = ip.id
                            WHERE c.id = $claim_id");
$claim = $claim_query->fetch_assoc();

if(!$claim) {
    $_SESSION['error'] = "Claim not found";
    header("Location: insurance.php");
    exit();
}

// Get documents for this claim
$documents = $conn->query("SELECT * FROM insurance_documents WHERE claim_id = $claim_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Details - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">Claim Details</h1>
                <a href="insurance.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Insurance
                </a>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-blue-800 mb-2">Claim Information</h2>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Claim Date:</span> <?= date('m/d/Y', strtotime($claim['claim_date'])) ?></p>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Status:</span> 
                            <?php 
                            $status_color = [
                                'pending' => 'bg-gray-200 text-gray-800',
                                'submitted' => 'bg-blue-200 text-blue-800',
                                'processing' => 'bg-yellow-200 text-yellow-800',
                                'paid' => 'bg-green-200 text-green-800',
                                'denied' => 'bg-red-200 text-red-800',
                                'appealed' => 'bg-purple-200 text-purple-800'
                            ][$claim['status']];
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?= $status_color ?>">
                                <?= ucfirst($claim['status']) ?>
                            </span>
                        </p>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Procedure Date:</span> <?= date('m/d/Y', strtotime($claim['procedure_date'])) ?></p>
                        <p class="text-gray-700"><span class="font-medium">Procedure Code:</span> <?= htmlspecialchars($claim['procedure_code']) ?></p>
                    </div>
                    
                    <div>
                        <h2 class="text-lg font-semibold text-blue-800 mb-2">Financial Information</h2>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Fee:</span> $<?= number_format($claim['fee'], 2) ?></p>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Covered Amount:</span> $<?= number_format($claim['covered_amount'] ?? 0, 2) ?></p>
                        <p class="text-gray-700"><span class="font-medium">Patient Responsibility:</span> $<?= number_format($claim['patient_responsibility'] ?? 0, 2) ?></p>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-blue-800 mb-2">Procedure Description</h2>
                    <p class="text-gray-700"><?= htmlspecialchars($claim['procedure_description']) ?></p>
                </div>
                
                <div>
                    <h2 class="text-lg font-semibold text-blue-800 mb-2">Notes</h2>
                    <p class="text-gray-700"><?= htmlspecialchars($claim['notes']) ?></p>
                </div>
            </div>
            
            <!-- Documents Section -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-blue-800">Documents</h2>
                    <a href="upload_claim_doc.php?claim_id=<?= $claim_id ?>" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
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
                        <div class="border rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-center mb-2">
                                <i class="fas <?= $icon ?> text-blue-600 text-xl mr-3"></i>
                                <div>
                                    <h3 class="font-medium text-blue-800"><?= ucfirst(str_replace('_', ' ', $doc['document_type'])) ?></h3>
                                    <p class="text-xs text-gray-500"><?= date('m/d/Y H:i', strtotime($doc['upload_date'])) ?></p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-700 mb-3 truncate"><?= htmlspecialchars($doc['file_name']) ?></p>
                            <div class="flex space-x-2">
                                <a href="<?= $doc['file_path'] ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
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
                    <p class="text-gray-500">No documents uploaded for this claim.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>