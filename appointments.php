<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

date_default_timezone_set('Asia/Kolkata');
$today = date("Y-m-d");

// Check if we're filtering for today's appointments
$showTodayOnly = isset($_GET['filter']) && $_GET['filter'] === 'today';

// Base query
$query = "SELECT a.*, p.name as patient_name 
          FROM appointments a 
          JOIN patients p ON a.patient_id = p.id";

// Add filter condition if needed
if ($showTodayOnly) {
    $query .= " WHERE a.appointment_date = CURDATE()";
}

// Complete query
$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$result = $conn->query($query);

function getAppointmentStatus($appointmentDate, $appointmentTime) {
    $appointmentDateTime = new DateTime($appointmentDate . ' ' . $appointmentTime);
    $currentDateTime = new DateTime();
    return ($appointmentDateTime < $currentDateTime) ? 'Completed' : 'Upcoming';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Toothly</title>
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
<body>
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-green-900">Appointment Management</h1>
                <div class="flex space-x-4">
                    <a href="?filter=<?= $showTodayOnly ? '' : 'today' ?>" 
                       class="<?= $showTodayOnly ? 'bg-green-600 text-white' : 'bg-green-100 text-green-800' ?> hover:bg-green-700 hover:text-white font-medium py-2 px-4 rounded-lg transition">
                        <?= $showTodayOnly ? 'Show All Appointments' : 'Today\'s Appointments' ?>
                    </a>
                    <a href="add_appointment.php" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Appointment
                    </a>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="p-4 text-left text-green-900">Patient</th>
                                <th class="p-4 text-left text-green-900">Date</th>
                                <th class="p-4 text-left text-green-900">Time</th>
                                <th class="p-4 text-left text-green-900">Reason</th>
                                <th class="p-4 text-left text-green-900">Status</th>
                                <th class="p-4 text-left text-green-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): 
                                    $status = getAppointmentStatus($row['appointment_date'], $row['appointment_time']);
                                    $isToday = date('Y-m-d', strtotime($row['appointment_date'])) == $today;
                                ?>
                                <tr class="hover:bg-green-50 transition <?= $isToday ? 'bg-green-50' : '' ?>">
                                    <td class="p-4"><?= htmlspecialchars($row['patient_name']) ?></td>
                                    <td class="p-4">
                                        <?= date('M j, Y', strtotime($row['appointment_date'])) ?>
                                        <?php if($isToday): ?>
                                            <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Today</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4"><?= date('g:i A', strtotime($row['appointment_time'])) ?></td>
                                    <td class="p-4"><?= htmlspecialchars($row['reason'] ?: '--') ?></td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium 
                                            <?= $status == 'Completed' ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800' ?>">
                                            <?= $status ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex space-x-2">
                                            <a href="edit_appointment.php?id=<?= $row['id'] ?>" 
                                               class="text-green-600 hover:text-green-800 px-2 py-1 rounded transition">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_appointment.php?id=<?= $row['id'] ?>" 
                                               class="text-red-600 hover:text-red-800 px-2 py-1 rounded transition delete-btn" 
                                               data-name="appointment">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-gray-500">
                                        <?= $showTodayOnly ? 'No appointments today' : 'No appointments found' ?>
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