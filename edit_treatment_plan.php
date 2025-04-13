<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

$treatment_plan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get treatment plan details
$stmt = $conn->prepare("SELECT tp.*, p.name as patient_name, 
                       a.id as appointment_id, a.appointment_date
                       FROM treatment_plans tp
                       JOIN patients p ON tp.patient_id = p.id
                       LEFT JOIN appointments a ON tp.appointment_id = a.id
                       WHERE tp.id = ?");
$stmt->bind_param("i", $treatment_plan_id);
$stmt->execute();
$treatment_plan = $stmt->get_result()->fetch_assoc();

if(!$treatment_plan) {
    $_SESSION['error'] = "Treatment plan not found";
    header("Location: treatment_plans.php");
    exit();
}

// Get patients and appointments for dropdowns
$patients = $conn->query("SELECT id, name FROM patients ORDER BY name");
$appointments = $conn->query("SELECT a.id, a.appointment_date, p.name as patient_name 
                             FROM appointments a
                             JOIN patients p ON a.patient_id = p.id
                             ORDER BY a.appointment_date DESC");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $conn->real_escape_string($_POST['patient_id']);
    $appointment_id = $conn->real_escape_string($_POST['appointment_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE treatment_plans SET
                           patient_id = ?,
                           appointment_id = ?,
                           title = ?,
                           description = ?,
                           status = ?
                           WHERE id = ?");
    $stmt->bind_param("iisssi", $patient_id, $appointment_id, $title, $description, $status, $treatment_plan_id);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Treatment plan updated successfully";
        header("Location: view_treatment_plan.php?id=$treatment_plan_id");
        exit();
    } else {
        $error = "Error updating treatment plan: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Treatment Plan - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">Edit Treatment Plan</h1>
                <a href="view_treatment_plan.php?id=<?= $treatment_plan_id ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Plan
                </a>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="max-w-3xl bg-white p-6 rounded-xl shadow-md">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="patient_id">Patient *</label>
                        <select name="patient_id" id="patient_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Patient</option>
                            <?php while($patient = $patients->fetch_assoc()): ?>
                            <option value="<?= $patient['id'] ?>" <?= $patient['id'] == $treatment_plan['patient_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($patient['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="appointment_id">Linked Appointment</label>
                        <select name="appointment_id" id="appointment_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Appointment (Optional)</option>
                            <?php while($appointment = $appointments->fetch_assoc()): ?>
                            <option value="<?= $appointment['id'] ?>" <?= $appointment['id'] == $treatment_plan['appointment_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($appointment['patient_name']) ?> - <?= date('m/d/Y', strtotime($appointment['appointment_date'])) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="title">Title *</label>
                        <input type="text" name="title" id="title" 
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="<?= htmlspecialchars($treatment_plan['title']) ?>" required>
                    </div>
                    
                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="description">Description</label>
                        <textarea name="description" id="description" rows="4" 
                                  class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($treatment_plan['description']) ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="status">Status *</label>
                        <select name="status" id="status" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="pending" <?= $treatment_plan['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="in_progress" <?= $treatment_plan['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= $treatment_plan['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $treatment_plan['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition mt-6">
                    <i class="fas fa-save mr-2"></i> Update Treatment Plan
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Update appointments dropdown when patient is selected
        document.getElementById('patient_id').addEventListener('change', function() {
            const patientId = this.value;
            const appointmentSelect = document.getElementById('appointment_id');
            
            if(patientId) {
                // In a real implementation, you would fetch appointments for this patient via AJAX
                // This is a simplified version that just filters the existing options
                Array.from(appointmentSelect.options).forEach(option => {
                    if(option.value !== '') {
                        option.style.display = option.textContent.includes(patientId) ? '' : 'none';
                    }
                });
            } else {
                Array.from(appointmentSelect.options).forEach(option => {
                    option.style.display = '';
                });
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>