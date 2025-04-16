<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

$contactId = $_GET['id'] ?? 0;

// Get the contact details
$stmt = $conn->prepare("SELECT * FROM contact_submissions WHERE id = ?");
$stmt->bind_param("i", $contactId);
$stmt->execute();
$contact = $stmt->get_result()->fetch_assoc();

if (!$contact) {
    header("Location: dashboard.php");
    exit();
}

// Mark as read if not already
if (!$contact['is_read']) {
    $updateStmt = $conn->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ?");
    $updateStmt->bind_param("i", $contactId);
    $updateStmt->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Contact Message - Toothly</title>
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
                    <h1 class="text-2xl font-bold text-green-900">Contact Message Details</h1>
                    <a href="dashboard.php" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>

                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">Contact Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Name</p>
                                        <p class="font-medium"><?= htmlspecialchars($contact['name']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Email</p>
                                        <p class="font-medium"><?= htmlspecialchars($contact['email']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Phone</p>
                                        <p class="font-medium"><?= htmlspecialchars($contact['phone'] ?? 'Not provided') ?></p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">Submission Details</h3>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Submitted At</p>
                                        <p class="font-medium"><?= date('M j, Y g:i A', strtotime($contact['submitted_at'])) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Status</p>
                                        <p class="font-medium">
                                            <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                                Read
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Message</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($contact['message']) ?></p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <!-- Add this to the button group at the bottom of the page -->
                            <button onclick="deleteContact(<?= $contact['id'] ?>)" 
                                    class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 mr-4 rounded-lg shadow transition ml-4">
                                <i class="fas fa-trash mr-2"></i> Delete
                            </button>
                            <button onclick="window.location.href='dashboard.php'" 
                                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg shadow transition">
                                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
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