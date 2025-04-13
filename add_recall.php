<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get patients for dropdown
$patients = $conn->query("SELECT id, name FROM patients ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Recall - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">Add New Recall</h1>
                <a href="recalls.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Recalls
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <form action="process_recall.php" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 mb-2" for="patient_id">Patient</label>
                            <select name="patient_id" id="patient_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Patient</option>
                                <?php while($patient = $patients->fetch_assoc()): ?>
                                    <option value="<?= $patient['id'] ?>"><?= htmlspecialchars($patient['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="recall_type">Recall Type</label>
                            <select name="recall_type" id="recall_type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Type</option>
                                <option value="Checkup">Checkup</option>
                                <option value="Cleaning">Cleaning</option>
                                <option value="Follow-up">Follow-up</option>
                                <option value="Treatment">Treatment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="recall_date">Recall Date</label>
                            <input type="date" name="recall_date" id="recall_date" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="due_date">Due Date</label>
                            <input type="date" name="due_date" id="due_date" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 mb-2" for="notes">Notes</label>
                            <textarea name="notes" id="notes" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="send_notification" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                            <span class="ml-2">Send email notification to patient</span>
                        </label>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="submit" name="add_recall" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg shadow transition">
                            Save Recall
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set recall date to today by default
        document.getElementById('recall_date').valueAsDate = new Date();
        
        // Set due date to 6 months from now by default
        const dueDate = new Date();
        dueDate.setMonth(dueDate.getMonth() + 6);
        document.getElementById('due_date').valueAsDate = dueDate;
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>