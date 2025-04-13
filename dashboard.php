<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php'; // Use your centralized DB config instead of repeating credentials

// Count total patients
$patients_count = 0;
$patients_query = "SELECT COUNT(*) as total FROM patients";
$patients_result = $conn->query($patients_query);
if ($patients_result) {
    $patients_count = $patients_result->fetch_assoc()['total'];
}

// Count today's appointments
// At the top of dashboard.php
date_default_timezone_set('Asia/Kolkata');

// Then use this query for today's appointments:
$today = date("Y-m-d");
$today_query = "SELECT COUNT(*) as total FROM appointments WHERE DATE(appointment_date) = ?";
$stmt = $conn->prepare($today_query);
$stmt->bind_param("s", $today);
$stmt->execute();
$today_result = $stmt->get_result();
if ($today_result) {
    $today_appointments_count = $today_result->fetch_assoc()['total'];
}
// Get recent appointments
$appointments_query = "SELECT a.*, p.name as patient_name 
                      FROM appointments a 
                      JOIN patients p ON a.patient_id = p.id 
                      ORDER BY a.appointment_date DESC 
                      LIMIT 5";
$appointments_result = $conn->query($appointments_query);

// Get all doctors
$doctors_query = "SELECT id, name, specialty, color FROM doctors ORDER BY name";
$doctors_result = $conn->query($doctors_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-2xl font-bold text-blue-800 mb-6">Dashboard Overview</h1>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-600">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-700">Total Patients</h2>
                            <p class="text-3xl font-bold text-blue-600"><?= $patients_count ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-600">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-700">Today's Appointments</h2>
                            <p class="text-3xl font-bold text-blue-600"><?= $today_appointments_count ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-600">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-700">Our Doctors</h2>
                            <p class="text-3xl font-bold text-blue-600"><?= $doctors_result->num_rows ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-user-md text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doctors Section -->
            <div class="bg-white p-6 rounded-xl shadow-md mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-blue-800">Our Dental Team</h2>
                    <a href="doctors.php" class="text-blue-600 hover:text-blue-800 font-medium">View All</a>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php while($doctor = $doctors_result->fetch_assoc()): 
                        $color = $doctor['color'] ?: '#3b82f6'; // Default to blue if no color set
                    ?>
                    <div class="bg-white border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition">
                        <div class="h-2" style="background-color: <?= $color ?>"></div>
                        <div class="p-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-full flex items-center justify-center text-white" 
                                         style="background-color: <?= $color ?>">
                                        <?= substr($doctor['name'], 0, 1) ?>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-lg font-semibold text-gray-900 truncate">
                                        <?= htmlspecialchars($doctor['name']) ?>
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        <?= htmlspecialchars($doctor['specialty']) ?>
                                    </p>
                                </div>
                            </div>
                            <!-- <div class="mt-4 flex justify-end">
                                <a href="doctor_profile.php?id=<?= $doctor['id'] ?>" 
                                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    View Profile
                                </a>
                            </div> -->
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="bg-white p-6 rounded-xl shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-blue-800">Recent Appointments</h2>
                    <a href="appointments.php" class="text-blue-600 hover:text-blue-800 font-medium">View All</a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="p-3 text-left text-blue-800">Patient</th>
                                <th class="p-3 text-left text-blue-800">Date</th>
                                <th class="p-3 text-left text-blue-800">Time</th>
                                <th class="p-3 text-left text-blue-800">Reason</th>
                                <th class="p-3 text-left text-blue-800">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while($row = $appointments_result->fetch_assoc()): ?>
                            <tr class="hover:bg-blue-50 transition">
                                <td class="p-3"><?= htmlspecialchars($row['patient_name']) ?></td>
                                <td class="p-3"><?= date('M j, Y', strtotime($row['appointment_date'])) ?></td>
                                <td class="p-3"><?= date('g:i A', strtotime($row['appointment_time'])) ?></td>
                                <td class="p-3"><?= $row['reason'] ? htmlspecialchars($row['reason']) : '--' ?></td>
                                <td class="p-3">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                        Upcoming
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>