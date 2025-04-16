<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

$requestId = $_GET['id'] ?? 0;

// Get the request details
$stmt = $conn->prepare("SELECT * FROM appointment_requests WHERE id = ?");
$stmt->bind_param("i", $requestId);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Request - Toothly</title>
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .bg-primary {
            background-color: #4CAF50;
        }
        .text-primary {
            color: #4CAF50;
        }
        .border-primary {
            border-color: #4CAF50;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold text-green-900">Appointment Request Details</h1>
                    <a href="dashboard.php" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>

                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">Patient Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Full Name</p>
                                        <p class="font-medium"><?= htmlspecialchars($request['name']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Email</p>
                                        <p class="font-medium"><?= htmlspecialchars($request['email']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Phone</p>
                                        <p class="font-medium"><?= htmlspecialchars($request['phone']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Date of Birth</p>
                                        <p class="font-medium"><?= $request['dob'] ? date('M j, Y', strtotime($request['dob'])) : 'Not provided' ?></p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">Appointment Details</h3>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Preferred Date</p>
                                        <p class="font-medium"><?= date('M j, Y', strtotime($request['preferred_date'])) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Preferred Time</p>
                                        <p class="font-medium"><?= date('g:i A', strtotime($request['preferred_time'])) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Service Requested</p>
                                        <p class="font-medium"><?= htmlspecialchars($request['service']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Status</p>
                                        <p class="font-medium capitalize">
                                            <span class="px-2 py-1 rounded-full text-xs 
                                                <?= $request['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                   ($request['status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') ?>">
                                                <?= $request['status'] ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Additional Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-gray-700"><?= $request['message'] ? nl2br(htmlspecialchars($request['message'])) : 'No additional information provided' ?></p>
                            </div>
                        </div>

                        <?php if ($request['status'] === 'pending'): ?>
                        <div class="mt-6 flex space-x-4">
                            <button onclick="approveRequest(<?= $request['id'] ?>)" 
                                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg shadow transition">
                                <i class="fas fa-check mr-2"></i> Approve
                            </button>
                            <button onclick="rejectRequest(<?= $request['id'] ?>)" 
                                    class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-lg shadow transition">
                                <i class="fas fa-times mr-2"></i> Reject
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function approveRequest(requestId) {
            Swal.fire({
                title: 'Approve Appointment?',
                text: "Are you sure you want to approve this appointment request?",
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
                                window.location.href = 'dashboard.php';
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
                                window.location.href = 'dashboard.php';
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
    </script>
</body>
</html>
<?php $conn->close(); ?>