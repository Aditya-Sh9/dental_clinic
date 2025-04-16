<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get all treatment plans
$query = "SELECT tp.*, p.name as patient_name, 
          a.appointment_date, d.name as doctor_name
          FROM treatment_plans tp
          JOIN patients p ON tp.patient_id = p.id
          LEFT JOIN appointments a ON tp.appointment_id = a.id
          LEFT JOIN doctors d ON a.doctor_id = d.id
          ORDER BY tp.created_at DESC";
$treatment_plans = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Plans - Toothly</title>
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
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-green-900">Treatment Plans</h1>
                <a href="add_treatment_plan.php" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                    <i class="fas fa-plus mr-2"></i> New Plan
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
            
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="p-3 text-left text-green-900">Patient</th>
                                <th class="p-3 text-left text-green-900">Title</th>
                                <th class="p-3 text-left text-green-900">Appointment</th>
                                <th class="p-3 text-left text-green-900">Status</th>
                                <th class="p-3 text-left text-green-900">Created</th>
                                <th class="p-3 text-left text-green-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while($plan = $treatment_plans->fetch_assoc()): 
                                $statusColors = [
                                    'pending' => 'bg-gray-100 text-gray-800',
                                    'in_progress' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800'
                                ];
                                $statusText = ucwords(str_replace('_', ' ', $plan['status']));
                            ?>
                            <tr class="hover:bg-green-50 transition">
                                <td class="p-3"><?= htmlspecialchars($plan['patient_name']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($plan['title']) ?></td>
                                <td class="p-3">
                                    <?= $plan['appointment_date'] ? date('m/d/Y', strtotime($plan['appointment_date'])) : '--' ?>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?= $statusColors[$plan['status']] ?>">
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td class="p-3"><?= date('m/d/Y', strtotime($plan['created_at'])) ?></td>
                                <td class="p-3">
                                    <div class="flex space-x-2">
                                        <a href="view_treatment_plan.php?id=<?= $plan['id'] ?>" class="text-green-600 hover:text-green-800 px-2 py-1 rounded transition" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_treatment_plan.php?id=<?= $plan['id'] ?>" class="text-yellow-600 hover:text-yellow-800 px-2 py-1 rounded transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_treatment_plan.php?id=<?= $plan['id'] ?>" class="text-red-600 hover:text-red-800 px-2 py-1 rounded transition delete-btn" title="Delete" data-name="treatment plan">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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