<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get recall ID from URL
$recall_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($recall_id <= 0) {
    $_SESSION['error'] = "Invalid recall ID";
    header("Location: recalls.php");
    exit();
}

// Get recall details
$recall_query = "SELECT * FROM recalls WHERE id = $recall_id";
$recall_result = $conn->query($recall_query);

if($recall_result->num_rows === 0) {
    $_SESSION['error'] = "Recall not found";
    header("Location: recalls.php");
    exit();
}

$recall = $recall_result->fetch_assoc();

// Get patients for dropdown
$patients = $conn->query("SELECT id, name FROM patients ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recall - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">Edit Recall</h1>
                <a href="recalls.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Recalls
                </a>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-md p-6">
                <form action="process_recall.php" method="POST">
                    <input type="hidden" name="recall_id" value="<?= $recall_id ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 mb-2" for="patient_id">Patient</label>
                            <select name="patient_id" id="patient_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <?php while($patient = $patients->fetch_assoc()): ?>
                                    <option value="<?= $patient['id'] ?>" <?= $patient['id'] == $recall['patient_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($patient['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="recall_type">Recall Type</label>
                            <select name="recall_type" id="recall_type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="Checkup" <?= $recall['recall_type'] == 'Checkup' ? 'selected' : '' ?>>Checkup</option>
                                <option value="Cleaning" <?= $recall['recall_type'] == 'Cleaning' ? 'selected' : '' ?>>Cleaning</option>
                                <option value="Follow-up" <?= $recall['recall_type'] == 'Follow-up' ? 'selected' : '' ?>>Follow-up</option>
                                <option value="Treatment" <?= $recall['recall_type'] == 'Treatment' ? 'selected' : '' ?>>Treatment</option>
                                <option value="Other" <?= $recall['recall_type'] == 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="recall_date">Recall Date</label>
                            <input type="date" name="recall_date" id="recall_date" 
                                   value="<?= date('Y-m-d', strtotime($recall['recall_date'])) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="due_date">Due Date</label>
                            <input type="date" name="due_date" id="due_date" 
                                   value="<?= date('Y-m-d', strtotime($recall['due_date'])) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="status">Status</label>
                            <select name="status" id="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="pending" <?= $recall['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="completed" <?= $recall['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $recall['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 mb-2" for="notes">Notes</label>
                            <textarea name="notes" id="notes" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($recall['notes']) ?></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="submit" name="update_recall" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg shadow transition">
                            Update Recall
                        </button>
                        <button type="button" onclick="sendRecallNotification()" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg shadow transition">
                            Send Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function sendRecallNotification() {
        Swal.fire({
            title: 'Send Recall Notification?',
            text: "This will send an email reminder to the patient.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, send it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `process_recall.php?action=send_notification&id=<?= $recall_id ?>`;
            }
        });
    }
    </script>
</body>
</html>
<?php $conn->close(); ?>