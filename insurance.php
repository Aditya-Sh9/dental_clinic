<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get insurance data
$query = "SELECT pi.*, p.name as patient_name, ip.name as provider_name 
          FROM patient_insurance pi
          JOIN patients p ON pi.patient_id = p.id
          JOIN insurance_providers ip ON pi.provider_id = ip.id";
$insurance_result = $conn->query($query);

// Get recent claims
$claims_query = "SELECT c.*, p.name as patient_name 
                 FROM insurance_claims c
                 JOIN patients p ON c.patient_id = p.id
                 ORDER BY c.claim_date DESC LIMIT 5";
$claims_result = $conn->query($claims_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance Management - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Add SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .swal2-popup {
            font-family: 'Inter', sans-serif;
            border-radius: 0.75rem !important;
        }
        .swal2-confirm {
            background-color: #2563eb !important;
            border-radius: 0.5rem !important;
        }
        .swal2-cancel {
            border-radius: 0.5rem !important;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">Insurance Management</h1>
                <div class="flex space-x-4">
                    <a href="add_insurance.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Insurance
                    </a>
                    <a href="add_claim.php" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-file-upload mr-2"></i> New Claim
                    </a>
                </div>
            </div>

            <!-- Insurance Policies -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold text-blue-800 mb-4">Insurance Policies</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="p-3 text-left text-blue-800">Patient</th>
                                <th class="p-3 text-left text-blue-800">Provider</th>
                                <th class="p-3 text-left text-blue-800">Policy #</th>
                                <th class="p-3 text-left text-blue-800">Coverage</th>
                                <th class="p-3 text-left text-blue-800">Balance</th>
                                <th class="p-3 text-left text-blue-800">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($insurance = $insurance_result->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-blue-50">
                                <td class="p-3"><?= htmlspecialchars($insurance['patient_name']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($insurance['provider_name']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($insurance['policy_number']) ?></td>
                                <td class="p-3">
                                    <?= date('m/d/Y', strtotime($insurance['coverage_start'])) ?> - 
                                    <?= date('m/d/Y', strtotime($insurance['coverage_end'])) ?>
                                </td>
                                <td class="p-3">$<?= number_format($insurance['remaining_balance'], 2) ?></td>
                                <td class="p-3">
                                    <a href="view_insurance.php?id=<?= $insurance['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_insurance.php?id=<?= $insurance['id'] ?>" class="text-yellow-600 hover:text-yellow-800 mr-2">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="upload_insurance_doc.php?insurance_id=<?= $insurance['id'] ?>" class="text-green-600 hover:text-green-800">
                                        <i class="fas fa-upload"></i>
                                    </a>
                                    <a href="delete_insurance.php?id=<?= $insurance['id'] ?>" class="text-red-600 hover:text-red-800 ml-2 delete-btn" data-name="insurance policy">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Claims -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-semibold text-blue-800 mb-4">Recent Claims</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="p-3 text-left text-blue-800">Claim Date</th>
                                <th class="p-3 text-left text-blue-800">Patient</th>
                                <th class="p-3 text-left text-blue-800">Procedure</th>
                                <th class="p-3 text-left text-blue-800">Amount</th>
                                <th class="p-3 text-left text-blue-800">Status</th>
                                <th class="p-3 text-left text-blue-800">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($claim = $claims_result->fetch_assoc()): 
                                $status_color = [
                                    'pending' => 'bg-gray-200 text-gray-800',
                                    'submitted' => 'bg-blue-200 text-blue-800',
                                    'processing' => 'bg-yellow-200 text-yellow-800',
                                    'paid' => 'bg-green-200 text-green-800',
                                    'denied' => 'bg-red-200 text-red-800',
                                    'appealed' => 'bg-purple-200 text-purple-800'
                                ][$claim['status']];
                            ?>
                            <tr class="border-b hover:bg-blue-50">
                                <td class="p-3"><?= date('m/d/Y', strtotime($claim['claim_date'])) ?></td>
                                <td class="p-3"><?= htmlspecialchars($claim['patient_name']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($claim['procedure_code']) ?></td>
                                <td class="p-3">$<?= number_format($claim['fee'], 2) ?></td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?= $status_color ?>">
                                        <?= ucfirst($claim['status']) ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <a href="view_claim.php?id=<?= $claim['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <!-- <a href="upload_claim_doc.php?claim_id=<?= $claim['id'] ?>" class="text-green-600 hover:text-green-800">
                                        <i class="fas fa-upload"></i>
                                    </a> -->
                                    <a href="delete_claim.php?id=<?= $claim['id'] ?>" class="text-red-600 hover:text-red-800 ml-2 delete-btn" data-name="claim">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle all delete buttons
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const itemName = this.getAttribute('data-name');
                    const deleteUrl = this.getAttribute('href');
                    
                    Swal.fire({
                        title: `Delete ${itemName}?`,
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            popup: 'shadow-lg rounded-xl'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = deleteUrl;
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>