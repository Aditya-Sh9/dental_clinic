<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get all doctors
$query = "SELECT * FROM doctors ORDER BY name";
$doctors = $conn->query($query);

// Handle delete if requested
if(isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM doctors WHERE id = $id");
    $_SESSION['success'] = "Doctor deleted successfully";
    header("Location: doctors.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- SweetAlert2 CSS -->
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
                <h1 class="text-2xl font-bold text-blue-800">Doctors Management</h1>
                <a href="add_doctor.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Doctor
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
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="p-3 text-left text-blue-800">Doctor</th>
                                <th class="p-3 text-left text-blue-800">Specialty</th>
                                <th class="p-3 text-left text-blue-800">Color</th>
                                <th class="p-3 text-left text-blue-800">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while($doctor = $doctors->fetch_assoc()): ?>
                            <tr class="hover:bg-blue-50">
                                <td class="p-3">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center text-white mr-3" 
                                             style="background-color: <?= $doctor['color'] ?: '#3b82f6' ?>">
                                            <?= substr($doctor['name'], 0, 1) ?>
                                        </div>
                                        <span class="font-medium"><?= htmlspecialchars($doctor['name']) ?></span>
                                    </div>
                                </td>
                                <td class="p-3"><?= htmlspecialchars($doctor['specialty']) ?></td>
                                <td class="p-3">
                                    <div class="h-5 w-5 rounded-full" style="background-color: <?= $doctor['color'] ?: '#3b82f6' ?>"></div>
                                </td>
                                <td class="p-3">
                                    <div class="flex space-x-2">
                                        <!-- <a href="view_doctor.php?id=<?= $doctor['id'] ?>" class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded transition">
                                            <i class="fas fa-eye"></i>
                                        </a> -->
                                        <!-- <a href="edit_doctor.php?id=<?= $doctor['id'] ?>" class="text-yellow-600 hover:text-yellow-800 px-2 py-1 rounded transition">
                                            <i class="fas fa-edit"></i>
                                        </a> -->
                                        <a href="doctors.php?delete_id=<?= $doctor['id'] ?>" class="text-red-600 hover:text-red-800 px-2 py-1 rounded transition delete-btn" data-name="<?= htmlspecialchars($doctor['name']) ?>">
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
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Handle delete confirmation
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const doctorName = this.getAttribute('data-name');
                    const deleteUrl = this.getAttribute('href');
                    
                    Swal.fire({
                        title: `Delete ${doctorName}?`,
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