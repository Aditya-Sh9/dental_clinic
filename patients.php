<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT id, name, email, phone, dob, gender FROM patients";
if(!empty($search)) {
    $query .= " WHERE name LIKE '%$search%' OR email LIKE '%$search%'";
}
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - Toothly</title>
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F5F5;
        }
    </style>
</head>
<body>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('sidebar.php'); ?>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-green-900">Patient Management</h1>
                <a href="add_patient.php" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Patient
                </a>
            </div>
            
            <!-- Search Form -->
            <form method="GET" class="mb-8">
                <div class="relative">
                    <input type="text" name="search" placeholder="Search patients..." 
                           class="w-full px-4 py-2 border rounded-lg pl-10 focus:outline-none focus:ring-2 focus:ring-green-500"
                           value="<?php echo htmlspecialchars($search); ?>">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </form>
            
            <!-- Patients Table -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="p-4 text-left text-green-900">Name</th>
                                <th class="p-4 text-left text-green-900">Email</th>
                                <th class="p-4 text-left text-green-900">Phone</th>
                                <th class="p-4 text-left text-green-900">DOB</th>
                                <th class="p-4 text-left text-green-900">Gender</th>
                                <th class="p-4 text-left text-green-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-green-50 transition">
                                <td class="p-4"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td class="p-4"><?php echo $row['dob'] ? date('m/d/Y', strtotime($row['dob'])) : '--'; ?></td>
                                <td class="p-4"><?php echo $row['gender'] ? htmlspecialchars($row['gender']) : '--'; ?></td>
                                <td class="p-4">
                                    <div class="flex space-x-2">
                                        <a href="edit_patient.php?id=<?php echo $row['id']; ?>" 
                                           class="text-green-600 hover:text-green-800 px-2 py-1 rounded transition">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <a href="delete_patient.php?id=<?php echo $row['id']; ?>" 
                                           class="text-red-600 hover:text-red-800 px-2 py-1 rounded transition delete-btn" 
                                           data-name="patient">
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