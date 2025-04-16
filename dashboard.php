<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Count total patients
$patients_count = 0;
$patients_query = "SELECT COUNT(*) as total FROM patients";
$patients_result = $conn->query($patients_query);
if ($patients_result) {
    $patients_count = $patients_result->fetch_assoc()['total'];
}

// Count today's appointments
date_default_timezone_set('Asia/Kolkata');
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

$contact_query = "SELECT id, name, email, phone, submitted_at, is_read FROM contact_submissions ORDER BY submitted_at DESC LIMIT 5";
$contact_result = $conn->query($contact_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Toothly</title>
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F5F5;
        }

        
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .doctor-card:hover {
            transform: translateY(-3px);
        }
        .unread-contact {
            background-color: #E8F5E9;
            border-left: 4px solid #4CAF50;
        }
        .read-contact {
            background-color: #ffffff;
        }
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(76, 175, 80, 0.1);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(46, 125, 50, 0.7);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(46, 125, 50, 0.9);
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
        .border-green-600 {
            border-color: #4CAF50;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto">
            <div class="p-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold text-green-900">Dashboard Overview</h1>
                    <div class="text-sm text-green-600">
                        <i class="far fa-calendar-alt mr-2"></i>
                        <?php echo date('l, F j, Y'); ?>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="stat-card bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-600 transition duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-medium text-gray-600">Total Patients</h2>
                                <p class="text-3xl font-bold text-green-800 mt-2"><?= $patients_count ?></p>
                                <p class="text-sm text-green-600 mt-1">
                                    <!-- <i class="fas fa-arrow-up mr-1"></i> 12% from last month -->
                                </p>
                            </div>
                            <div class="bg-green-100 p-4 rounded-full">
                                <i class="fas fa-users text-green-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-600 transition duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-medium text-gray-600">Today's Appointments</h2>
                                <p class="text-3xl font-bold text-green-800 mt-2"><?= $today_appointments_count ?></p>
                                <p class="text-sm text-green-500 mt-1">
                                    <!-- <i class="fas fa-clock mr-1"></i> 2 upcoming in next hour -->
                                </p>
                            </div>
                            <div class="bg-green-100 p-4 rounded-full">
                                <i class="fas fa-calendar-day text-green-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-600 transition duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-medium text-gray-600">Active Doctors</h2>
                                <p class="text-3xl font-bold text-green-800 mt-2"><?= $doctors_result->num_rows ?></p>
                                <p class="text-sm text-green-500 mt-1">
                                    <i class="fas fa-user-md mr-1"></i> All available today
                                </p>
                            </div>
                            <div class="bg-green-100 p-4 rounded-full">
                                <i class="fas fa-user-md text-green-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Appointment Requests -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                    <div class="p-6 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-green-900">Pending Appointment Requests</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-green-50">
                                <tr>
                                    <th class="p-4 text-left text-green-900">Patient</th>
                                    <th class="p-4 text-left text-green-900">Preferred Date</th>
                                    <th class="p-4 text-left text-green-900">Service</th>
                                    <th class="p-4 text-left text-green-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php 
                                // Modified query to get data directly from appointment_requests
                                $requests_query = "SELECT * FROM appointment_requests WHERE status = 'pending' ORDER BY preferred_date ASC";
                                $requests_result = $conn->query($requests_query);

                                if($requests_result->num_rows > 0): 
                                    while($request = $requests_result->fetch_assoc()): 
                                ?>
                                <tr class="hover:bg-green-50 transition">
                                    <td class="p-4"><?= htmlspecialchars($request['name']) ?></td>
                                    <td class="p-4">
                                        <?= date('M j, Y', strtotime($request['preferred_date'])) ?> at 
                                        <?= date('g:i A', strtotime($request['preferred_time'])) ?>
                                    </td>
                                    <td class="p-4"><?= htmlspecialchars($request['service']) ?></td>
                                    <td class="p-4">
                                        <div class="flex space-x-2">
                                            <button onclick="approveRequest(<?= $request['id'] ?>)" 
                                                    class="text-green-600 hover:text-green-800 px-2 py-1 rounded transition">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button onclick="rejectRequest(<?= $request['id'] ?>)" 
                                                    class="text-red-600 hover:text-red-800 px-2 py-1 rounded transition">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                            <button onclick="viewRequestDetails(<?= $request['id'] ?>)" 
                                                    class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded transition">
                                                <i class="fas fa-eye"></i> Details
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-gray-500">No pending appointment requests</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                    <div class="p-6 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-green-900">Recent Contact Messages</h2>
                        <a href="contact_submissions.php" class="text-green-600 hover:text-green-800 font-medium flex items-center">
                            View All <i class="fas fa-chevron-right ml-1 text-sm"></i>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-green-50">
                                <tr>
                                    <th class="p-4 text-left text-green-900">Name</th>
                                    <th class="p-4 text-left text-green-900">Email</th>
                                    <th class="p-4 text-left text-green-900">Phone</th>
                                    <th class="p-4 text-left text-green-900">Date</th>
                                    <th class="p-4 text-left text-green-900">Status</th>
                                    <th class="p-4 text-left text-green-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if($contact_result->num_rows > 0): 
                                    while($contact = $contact_result->fetch_assoc()): 
                                        $statusClass = $contact['is_read'] ? 'read-contact' : 'unread-contact';
                                ?>
                                <tr class="hover:bg-green-50 transition <?= $statusClass ?>">
                                    <td class="p-4"><?= htmlspecialchars($contact['name']) ?></td>
                                    <td class="p-4"><?= htmlspecialchars($contact['email']) ?></td>
                                    <td class="p-4"><?= htmlspecialchars($contact['phone'] ?? 'N/A') ?></td>
                                    <td class="p-4"><?= date('M j, Y g:i A', strtotime($contact['submitted_at'])) ?></td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded-full text-xs 
                                            <?= $contact['is_read'] ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800' ?>">
                                            <?= $contact['is_read'] ? 'Read' : 'Unread' ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex space-x-2">
                                            <button onclick="viewContactDetails(<?= $contact['id'] ?>)" 
                                                    class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded transition">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <?php if(!$contact['is_read']): ?>
                                            <button onclick="markAsRead(<?= $contact['id'] ?>)" 
                                                    class="text-green-600 hover:text-green-800 px-2 py-1 rounded transition">
                                                <i class="fas fa-check"></i> Mark Read
                                            </button>
                                            <!-- In the actions column of the contact submissions table (after the existing buttons) -->
                                            <button onclick="deleteContact(<?= $contact['id'] ?>)" 
                                                    class="text-red-600 hover:text-red-800 px-2 py-1 rounded transition">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-gray-500">No contact messages found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Doctors Section -->
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-green-900">Dental Team</h2>
                            <a href="doctors.php" class="text-green-600 hover:text-green-800 font-medium flex items-center">
                                View All <i class="fas fa-chevron-right ml-1 text-sm"></i>
                            </a>
                        </div>
                        
                        <div class="space-y-4">
                            <?php 
                            $doctors_result->data_seek(0);
                            while($doctor = $doctors_result->fetch_assoc()): 
                                $color = $doctor['color'] ?: '#4CAF50';
                            ?>
                            <div class="doctor-card bg-white border border-gray-200 rounded-lg overflow-hidden shadow-xs hover:shadow-md transition duration-300">
                                <div class="p-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="h-12 w-12 rounded-full flex items-center justify-center text-white font-bold" 
                                                 style="background-color: <?= $color ?>">
                                                <?= substr($doctor['name'], 0, 1) ?>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-lg font-semibold text-gray-900">
                                                <?= htmlspecialchars($doctor['name']) ?>
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                <?= htmlspecialchars($doctor['specialty']) ?>
                                            </p>
                                        </div>
                                        <div class="text-green-600">
                                            <!-- <i class="fas fa-chevron-right"></i> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Recent Appointments -->
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-green-900"> Appointments</h2>
                            <a href="appointments.php" class="text-green-600 hover:text-green-800 font-medium flex items-center">
                                View All <i class="fas fa-chevron-right ml-1 text-sm"></i>
                            </a>
                        </div>
                        
                        <div class="space-y-4">
                            <?php 
                            $appointments_result->data_seek(0);
                            while($row = $appointments_result->fetch_assoc()): 
                                $time = strtotime($row['appointment_time']);
                                $time_class = (date('H', $time) < 12) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                            ?>
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-green-50 transition duration-300">
                                <div class="flex items-center space-x-4">
                                    <div class="bg-green-100 p-3 rounded-full">
                                        <i class="fas fa-calendar-day text-green-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900"><?= htmlspecialchars($row['patient_name']) ?></h3>
                                        <p class="text-sm text-gray-500">
                                            <?= date('g:i A', $time) ?> • <?= $row['reason'] ? htmlspecialchars($row['reason']) : 'General Checkup' ?>
                                        </p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?= $time_class ?>">
                                    <?= (date('H', $time) < 12 ? 'Morning' : 'Afternoon') ?>
                                </span>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function approveRequest(requestId) {
            Swal.fire({
                title: 'Approve Appointment?',
                text: "This will send a confirmation email to the patient",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4CAF50',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('process_request_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=approve&id=' + requestId,
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (response.status === 401) {
                            window.location.href = 'login.php';
                            return;
                        }
                        return response.json();
                    })
                    .then(data => {
                        if(data.success) {
                            Swal.fire({
                                title: 'Approved!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#4CAF50'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Error approving request',
                                icon: 'error',
                                confirmButtonColor: '#4CAF50'
                            });
                        }
                    });
                }
            });
        }

        function rejectRequest(requestId) {
            Swal.fire({
                title: 'Reject Appointment?',
                text: "Are you sure you want to reject this appointment request?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4CAF50',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, reject it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('process_request_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=reject&id=' + requestId,
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (response.status === 401) {
                            window.location.href = 'login.php';
                            return;
                        }
                        return response.json();
                    })
                    .then(data => {
                        if(data.success) {
                            Swal.fire({
                                title: 'Rejected!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#4CAF50'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Error rejecting request',
                                icon: 'error',
                                confirmButtonColor: '#4CAF50'
                            });
                        }
                    });
                }
            });
        }

        function viewRequestDetails(requestId) {
            window.location.href = 'view_request.php?id=' + requestId;
        }

        function viewContactDetails(contactId) {
            window.location.href = 'view_contact.php?id=' + contactId;
        }
        

        function markAsRead(contactId) {
            fetch('mark_contact_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + contactId,
                credentials: 'same-origin'
            })
            .then(response => {
                if (response.status === 401) {
                    window.location.href = 'login.php';
                    return;
                }
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        title: 'Marked as Read!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#4CAF50'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Error marking as read',
                        icon: 'error',
                        confirmButtonColor: '#4CAF50'
                    });
                }
            });
        }
        function deleteContact(contactId) {
    Swal.fire({
        title: 'Delete Contact Message?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4CAF50',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('delete_contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + contactId,
                credentials: 'same-origin'
            })
            .then(response => {
                if (response.status === 401) {
                    window.location.href = 'login.php';
                    return;
                }
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#4CAF50'
                    }).then(() => {
                        // Redirect to dashboard if on view page, otherwise reload
                        if (window.location.pathname.includes('view_contact.php')) {
                            window.location.href = 'dashboard.php';
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Error deleting contact',
                        icon: 'error',
                        confirmButtonColor: '#4CAF50'
                    });
                }
            });
        }
    });
}
    </script>
</body>
</html>
<?php $conn->close(); ?>