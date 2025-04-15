<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get all contact submissions
$contact_query = "SELECT id, name, email, phone, submitted_at, is_read FROM contact_submissions ORDER BY submitted_at DESC";
$contact_result = $conn->query($contact_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .unread-contact {
            background-color: #E8F5E9;
            border-left: 4px solid #4CAF50;
        }
        .read-contact {
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include('sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold text-green-900">All Contact Messages</h1>
                    <a href="dashboard.php" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>

                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
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
                                            <button onclick="window.location.href='view_contact.php?id=<?= $contact['id'] ?>'" 
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
            </div>
        </div>
    </div>

    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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
                        title: 'Success!',
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