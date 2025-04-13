<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get filter if set
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Base query
$query = "SELECT r.*, p.name as patient_name, p.phone, p.email, 
          a.appointment_date, a.appointment_time
          FROM recalls r
          JOIN patients p ON r.patient_id = p.id
          LEFT JOIN appointments a ON r.appointment_id = a.id";

// Add filter condition
if ($filter === 'upcoming') {
    $query .= " WHERE r.due_date >= CURDATE() AND r.status = 'pending'";
} elseif ($filter === 'overdue') {
    $query .= " WHERE r.due_date < CURDATE() AND r.status = 'pending'";
} elseif ($filter === 'completed') {
    $query .= " WHERE r.status = 'completed'";
}

// Complete query
$query .= " ORDER BY r.due_date ASC";
$recalls = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recall Management - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">Recall Management</h1>
                <div class="flex space-x-4">
                    <div class="flex space-x-2">
                        <a href="?filter=upcoming" class="<?= $filter === 'upcoming' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800' ?> hover:bg-blue-700 hover:text-white font-medium py-2 px-4 rounded-lg transition">
                            Upcoming
                        </a>
                        <a href="?filter=overdue" class="<?= $filter === 'overdue' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800' ?> hover:bg-blue-700 hover:text-white font-medium py-2 px-4 rounded-lg transition">
                            Overdue
                        </a>
                        <a href="?filter=completed" class="<?= $filter === 'completed' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800' ?> hover:bg-blue-700 hover:text-white font-medium py-2 px-4 rounded-lg transition">
                            Completed
                        </a>
                        <a href="recalls.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
                            All Recalls
                        </a>
                    </div>
                    <a href="add_recall.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-plus mr-2"></i> New Recall
                    </a>
                </div>
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
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="p-4 text-left text-blue-800">Patient</th>
                                <th class="p-4 text-left text-blue-800">Recall Type</th>
                                <th class="p-4 text-left text-blue-800">Due Date</th>
                                <th class="p-4 text-left text-blue-800">Status</th>
                                <th class="p-4 text-left text-blue-800">Contact Info</th>
                                <th class="p-4 text-left text-blue-800">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if($recalls->num_rows > 0): ?>
                                <?php while($recall = $recalls->fetch_assoc()): 
                                    $isOverdue = strtotime($recall['due_date']) < strtotime('today') && $recall['status'] === 'pending';
                                ?>
                                <tr class="hover:bg-blue-50 <?= $isOverdue ? 'bg-red-50' : '' ?>">
                                    <td class="p-4"><?= htmlspecialchars($recall['patient_name']) ?></td>
                                    <td class="p-4"><?= htmlspecialchars($recall['recall_type']) ?></td>
                                    <td class="p-4 <?= $isOverdue ? 'text-red-600 font-semibold' : '' ?>">
                                        <?= date('M j, Y', strtotime($recall['due_date'])) ?>
                                        <?php if($isOverdue): ?>
                                            <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Overdue</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium 
                                            <?= $recall['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($recall['status'] === 'cancelled' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800') ?>">
                                            <?= ucfirst($recall['status']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-sm">
                                            <div><?= htmlspecialchars($recall['phone']) ?></div>
                                            <div class="text-blue-600"><?= htmlspecialchars($recall['email']) ?></div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex space-x-2">
                                            <a href="edit_recall.php?id=<?= $recall['id'] ?>" 
                                               class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded transition">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_recall.php?id=<?= $recall['id'] ?>" 
                                               class="text-red-600 hover:text-red-800 px-2 py-1 rounded transition delete-btn" 
                                               data-name="recall">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php if($recall['status'] === 'pending'): ?>
                                                <a href="process_recall.php?action=complete&id=<?= $recall['id'] ?>" 
                                                   class="text-green-600 hover:text-green-800 px-2 py-1 rounded transition">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-gray-500">
                                        No recalls found
                                    </td>
                                </tr>
                            <?php endif; ?>
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