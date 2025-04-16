<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get patients and appointments
$patients = $conn->query("SELECT id, name FROM patients ORDER BY name");
$appointments = $conn->query("SELECT a.id, a.appointment_date, p.name as patient_name, a.patient_id
                             FROM appointments a
                             JOIN patients p ON a.patient_id = p.id
                             ORDER BY a.appointment_date DESC");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = intval($_POST['patient_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $status = $conn->real_escape_string($_POST['status']);
    $user_id = $_SESSION['user']['id'];
    
    // Handle optional appointment_id - set to NULL if empty or invalid
    $appointment_id = null;
    if(!empty($_POST['appointment_id'])) {
        $appointment_id = intval($_POST['appointment_id']);
        
        // Verify the appointment exists and belongs to the selected patient
        $check_appointment = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND patient_id = ?");
        $check_appointment->bind_param("ii", $appointment_id, $patient_id);
        $check_appointment->execute();
        
        if($check_appointment->get_result()->num_rows === 0) {
            $error = "Selected appointment is invalid or doesn't belong to this patient";
            $appointment_id = null; // Reset to NULL if invalid
        }
    }

    if(!isset($error)) {
        // Insert treatment plan with proper NULL handling
        $stmt = $conn->prepare("INSERT INTO treatment_plans 
                              (patient_id, appointment_id, title, description, status, created_at)
                              VALUES (?, ?, ?, ?, ?, NOW())");
        
        // Use "i" for integer or NULL for appointment_id
        if($appointment_id !== null) {
            $stmt->bind_param("iisss", $patient_id, $appointment_id, $title, $description, $status);
        } else {
            $null_value = null;
            $stmt->bind_param("iisss", $patient_id, $null_value, $title, $description, $status);
        }
        
        if($stmt->execute()) {
            $treatment_plan_id = $conn->insert_id;
            
            // Handle file upload if present
            if(isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/treatment_plans/';
                if(!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = basename($_FILES['document']['name']);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_name = "treatment_plan_{$treatment_plan_id}_" . time() . ".$file_ext";
                $file_path = $upload_dir . $new_file_name;
                
                $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
                $max_file_size = 5 * 1024 * 1024; // 5MB
                
                if(in_array($file_ext, $allowed_types) && $_FILES['document']['size'] <= $max_file_size) {
                    if(move_uploaded_file($_FILES['document']['tmp_name'], $file_path)) {
                        $doc_stmt = $conn->prepare("INSERT INTO treatment_plan_documents 
                                                  (treatment_plan_id, file_name, file_path, file_type, uploaded_by)
                                                  VALUES (?, ?, ?, ?, ?)");
                        $doc_stmt->bind_param("isssi", $treatment_plan_id, $file_name, $file_path, $file_ext, $user_id);
                        $doc_stmt->execute();
                    }
                }
            }
            
            $_SESSION['success'] = "Treatment plan created successfully";
            header("Location: view_treatment_plan.php?id=$treatment_plan_id");
            exit();
        } else {
            $error = "Error creating treatment plan: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Treatment Plan - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-green-900">Add Treatment Plan</h1>
                <a href="treatment_plans.php" class="text-green-600 hover:text-green-800 font-medium flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Treatment Plans
                </a>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="max-w-3xl bg-white p-6 rounded-xl shadow-md">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="patient_id">Patient *</label>
                        <select name="patient_id" id="patient_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                            <option value="">Select Patient</option>
                            <?php while($patient = $patients->fetch_assoc()): ?>
                            <option value="<?= $patient['id'] ?>" <?= isset($_POST['patient_id']) && $_POST['patient_id'] == $patient['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($patient['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="appointment_id">Linked Appointment</label>
                        <select name="appointment_id" id="appointment_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Select Appointment (Optional)</option>
                            <?php 
                            $appointments->data_seek(0); // Reset pointer
                            while($appointment = $appointments->fetch_assoc()): 
                                $selected = isset($_POST['appointment_id']) && $_POST['appointment_id'] == $appointment['id'] ? 'selected' : '';
                            ?>
                            <option value="<?= $appointment['id'] ?>" data-patient-id="<?= $appointment['patient_id'] ?>" <?= $selected ?>>
                                <?= htmlspecialchars($appointment['patient_name']) ?> - <?= date('m/d/Y', strtotime($appointment['appointment_date'])) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="title">Title *</label>
                        <input type="text" name="title" id="title" value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    </div>
                    
                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="description">Description</label>
                        <textarea name="description" id="description" rows="4" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="status">Status *</label>
                        <select name="status" id="status" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                            <option value="pending" <?= isset($_POST['status']) && $_POST['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="in_progress" <?= isset($_POST['status']) && $_POST['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= isset($_POST['status']) && $_POST['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= isset($_POST['status']) && $_POST['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="document">Initial Document (Optional)</label>
                        <div class="relative">
                            <input type="file" name="document" id="document" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 opacity-0 absolute z-10">
                            <div class="relative z-0 bg-white border border-gray-300 rounded-lg px-3 py-2 flex items-center justify-between">
                                <span class="text-gray-500 truncate">Choose file...</span>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Browse</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Allowed file types: PDF, JPG, PNG (Max 5MB)</p>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition mt-6 flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i> Create Treatment Plan
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Update appointments dropdown when patient is selected
        document.getElementById('patient_id').addEventListener('change', function() {
            const patientId = this.value;
            const appointmentSelect = document.getElementById('appointment_id');
            
            Array.from(appointmentSelect.options).forEach(option => {
                if(option.value !== '') {
                    const optionPatientId = option.getAttribute('data-patient-id');
                    option.style.display = (optionPatientId === patientId) ? '' : 'none';
                }
            });
            
            // Reset appointment selection when changing patient
            if(!patientId) {
                Array.from(appointmentSelect.options).forEach(option => {
                    option.style.display = '';
                });
            }
            appointmentSelect.value = '';
        });
        
        // Initialize the dropdown based on current selection
        document.addEventListener('DOMContentLoaded', function() {
            const patientSelect = document.getElementById('patient_id');
            if(patientSelect.value) {
                patientSelect.dispatchEvent(new Event('change'));
            }

            // File input display
            const fileInput = document.getElementById('document');
            const fileDisplay = fileInput.nextElementSibling.querySelector('span:first-child');
            
            fileInput.addEventListener('change', function() {
                if(this.files.length > 0) {
                    fileDisplay.textContent = this.files[0].name;
                    fileDisplay.classList.remove('text-gray-500');
                    fileDisplay.classList.add('text-gray-800');
                } else {
                    fileDisplay.textContent = 'Choose file...';
                    fileDisplay.classList.remove('text-gray-800');
                    fileDisplay.classList.add('text-gray-500');
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>