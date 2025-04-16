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
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F5F5;
        }
    </style>
    <style>
        .swal2-popup {
            font-family: 'Inter', sans-serif;
            border-radius: 0.75rem !important;
        }
        .swal2-confirm {
            background-color: #2E7D32 !important;
            border-radius: 0.5rem !important;
        }
        .swal2-cancel {
            border-radius: 0.5rem !important;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-pending {
            background-color: #E3F2FD;
            color: #1976D2;
        }
        .status-submitted {
            background-color: #E3F2FD;
            color: #1976D2;
        }
        .status-processing {
            background-color: #FFF8E1;
            color: #FF8F00;
        }
        .status-paid {
            background-color: #E8F5E9;
            color: #2E7D32;
        }
        .status-denied {
            background-color: #FFEBEE;
            color: #C62828;
        }
        .status-appealed {
            background-color: #EDE7F6;
            color: #5E35B1;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-green-900">Insurance Management</h1>
                <div class="flex space-x-4">
                    <a href="add_insurance.php" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Insurance
                    </a>
                    <a href="add_claim.php" class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-file-upload mr-2"></i> New Claim
                    </a>
                </div>
            </div>

            <!-- Insurance Policies -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold text-green-900 mb-4">Insurance Policies</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="p-3 text-left text-green-900">Patient</th>
                                <th class="p-3 text-left text-green-900">Provider</th>
                                <th class="p-3 text-left text-green-900">Policy #</th>
                                <th class="p-3 text-left text-green-900">Coverage</th>
                                <th class="p-3 text-left text-green-900">Balance</th>
                                <th class="p-3 text-left text-green-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($insurance = $insurance_result->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-green-50">
                                <td class="p-3"><?= htmlspecialchars($insurance['patient_name']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($insurance['provider_name']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($insurance['policy_number']) ?></td>
                                <td class="p-3">
                                    <?= date('m/d/Y', strtotime($insurance['coverage_start'])) ?> - 
                                    <?= date('m/d/Y', strtotime($insurance['coverage_end'])) ?>
                                </td>
                                <td class="p-3">$<?= number_format($insurance['remaining_balance'], 2) ?></td>
                                <td class="p-3">
                                    <a href="view_insurance.php?id=<?= $insurance['id'] ?>" class="text-green-600 hover:text-green-800 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_insurance.php?id=<?= $insurance['id'] ?>" class="text-yellow-600 hover:text-yellow-800 mr-2">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="upload_insurance_doc.php?insurance_id=<?= $insurance['id'] ?>" class="text-blue-600 hover:text-blue-800">
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
                <h2 class="text-xl font-semibold text-green-900 mb-4">Recent Claims</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="p-3 text-left text-green-900">Claim Date</th>
                                <th class="p-3 text-left text-green-900">Patient</th>
                                <th class="p-3 text-left text-green-900">Procedure</th>
                                <th class="p-3 text-left text-green-900">Amount</th>
                                <th class="p-3 text-left text-green-900">Status</th>
                                <th class="p-3 text-left text-green-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($claim = $claims_result->fetch_assoc()): 
                                $status_class = [
                                    'pending' => 'status-pending',
                                    'submitted' => 'status-submitted',
                                    'processing' => 'status-processing',
                                    'paid' => 'status-paid',
                                    'denied' => 'status-denied',
                                    'appealed' => 'status-appealed'
                                ][$claim['status']];
                            ?>
                            <tr class="border-b hover:bg-green-50">
                                <td class="p-3"><?= date('m/d/Y', strtotime($claim['claim_date'])) ?></td>
                                <td class="p-3"><?= htmlspecialchars($claim['patient_name']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($claim['procedure_code']) ?></td>
                                <td class="p-3">$<?= number_format($claim['fee'], 2) ?></td>
                                <td class="p-3">
                                    <span class="status-badge <?= $status_class ?>">
                                        <?= ucfirst($claim['status']) ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <a href="view_claim.php?id=<?= $claim['id'] ?>" class="text-green-600 hover:text-green-800 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                        confirmButtonColor: '#2E7D32',
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