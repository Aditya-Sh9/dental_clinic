<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get patients, appointments, and treatment plans
$patients = $conn->query("SELECT id, name FROM patients ORDER BY name");
$appointments = $conn->query("SELECT a.id, a.appointment_date, p.name as patient_name, a.patient_id 
                             FROM appointments a
                             JOIN patients p ON a.patient_id = p.id
                             ORDER BY a.appointment_date DESC");
$treatment_plans = $conn->query("SELECT tp.id, tp.title, p.name as patient_name, tp.patient_id 
                                FROM treatment_plans tp
                                JOIN patients p ON tp.patient_id = p.id
                                ORDER BY tp.created_at DESC");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $conn->real_escape_string($_POST['patient_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $taken_date = $conn->real_escape_string($_POST['taken_date']);
    $user_id = $_SESSION['user']['id'];
    
    // Handle optional fields - convert empty strings to NULL
    $appointment_id = !empty($_POST['appointment_id']) ? $conn->real_escape_string($_POST['appointment_id']) : NULL;
    $treatment_plan_id = !empty($_POST['treatment_plan_id']) ? $conn->real_escape_string($_POST['treatment_plan_id']) : NULL;

    // Insert X-ray record
    $stmt = $conn->prepare("INSERT INTO xray_records 
                          (patient_id, appointment_id, treatment_plan_id, title, description, taken_date)
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisss", $patient_id, $appointment_id, $treatment_plan_id, $title, $description, $taken_date);
    
    if($stmt->execute()) {
        $xray_id = $conn->insert_id;
        
        // Handle file uploads if present
        if(!empty($_FILES['xray_images']['name'][0])) {
            $upload_dir = 'uploads/xrays/';
            if(!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            foreach($_FILES['xray_images']['name'] as $key => $name) {
                $file_name = basename($name);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_name = "xray_{$xray_id}_" . time() . "_{$key}.$file_ext";
                $file_path = $upload_dir . $new_file_name;
                
                $allowed_types = ['jpg', 'jpeg', 'png', 'dicom'];
                $max_file_size = 10 * 1024 * 1024; // 10MB
                
                if(in_array($file_ext, $allowed_types) && $_FILES['xray_images']['size'][$key] <= $max_file_size) {
                    if(move_uploaded_file($_FILES['xray_images']['tmp_name'][$key], $file_path)) {
                        $notes = $conn->real_escape_string($_POST['image_notes'][$key] ?? '');
                        
                        $img_stmt = $conn->prepare("INSERT INTO xray_images 
                                                  (xray_id, file_name, file_path, file_type, notes, uploaded_by)
                                                  VALUES (?, ?, ?, ?, ?, ?)");
                        $img_stmt->bind_param("issssi", $xray_id, $file_name, $file_path, $file_ext, $notes, $user_id);
                        $img_stmt->execute();
                    }
                }
            }
        }
        
        $_SESSION['success'] = "X-ray record created successfully";
        header("Location: view_xray.php?id=$xray_id");
        exit();
    } else {
        $error = "Error creating X-ray record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add X-Ray Record - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.css">

    <style>
        . {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .dropzone {
            border: 2px dashed #9CA3AF;
            border-radius: 0.5rem;
            background: #F9FAFB;
            transition: all 0.3s ease;
        }
        .dropzone:hover {
            border-color: #4B5563;
            background: #F3F4F6;
        }
        .dz-message {
            color: #6B7280;
            font-size: 1.125rem;
        }
        .dz-preview {
            border-radius: 0.5rem;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .dz-success-mark, .dz-error-mark {
            display: none;
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
    </style>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F5F5;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-green-900">Add X-Ray Record</h1>
                <a href="xrays.php" class="text-green-600 hover:text-green-800 font-medium flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to X-Rays
                </a>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="max-w-4xl bg-white p-6 rounded-xl shadow-md">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="patient_id">Patient *</label>
                        <select name="patient_id" id="patient_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 " required>
                            <option value="">Select Patient</option>
                            <?php while($patient = $patients->fetch_assoc()): ?>
                            <option value="<?= $patient['id'] ?>"><?= htmlspecialchars($patient['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="appointment_id">Linked Appointment</label>
                        <select name="appointment_id" id="appointment_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 ">
                            <option value="">Select Appointment (Optional)</option>
                            <?php 
                            // Reset the pointer to the beginning of the result set
                            $appointments->data_seek(0);
                            while($appointment = $appointments->fetch_assoc()): 
                            ?>
                            <option value="<?= $appointment['id'] ?>" data-patient-id="<?= $appointment['patient_id'] ?>">
                                <?= htmlspecialchars($appointment['patient_name']) ?> - <?= date('m/d/Y', strtotime($appointment['appointment_date'])) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="treatment_plan_id">Linked Treatment Plan</label>
                        <select name="treatment_plan_id" id="treatment_plan_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 ">
                            <option value="">Select Treatment Plan (Optional)</option>
                            <?php while($plan = $treatment_plans->fetch_assoc()): ?>
                            <option value="<?= $plan['id'] ?>" data-patient-id="<?= $plan['patient_id'] ?>">
                                <?= htmlspecialchars($plan['patient_name']) ?> - <?= htmlspecialchars($plan['title']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="taken_date">Date Taken *</label>
                        <input type="date" name="taken_date" id="taken_date" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    </div>
                    
                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="title">Title *</label>
                        <input type="text" name="title" id="title" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    </div>
                    
                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="description">Description</label>
                        <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>
                </div>
                
                <!-- X-Ray Images Upload -->
                <div class="mt-8 border-t pt-6">
                    <h2 class="text-xl font-semibold text-green-900 mb-4">X-Ray Images</h2>
                    
                    <div id="xray-dropzone" class="dropzone border-2 border-dashed border-gray-300 rounded-lg p-6 mb-4">
                        <div class="dz-message text-center text-gray-500">
                            <i class="fas fa-cloud-upload-alt text-4xl mb-2"></i>
                            <p>Drop X-ray images here or click to upload</p>
                            <p class="text-xs mt-2">Allowed file types: JPG, PNG, DICOM (Max 10MB each)</p>
                        </div>
                    </div>
                    
                    <div id="xray-preview" class="hidden">
                        <h3 class="text-lg font-medium text-green-900 mb-3">Uploaded Images</h3>
                        <div id="xray-files" class="space-y-4"></div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition mt-6 flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i> Save X-Ray Record
                </button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize Dropzone
        Dropzone.autoDiscover = false;
        
        const myDropzone = new Dropzone("#xray-dropzone", {
            url: "#",
            autoProcessQueue: false,
            uploadMultiple: true,
            parallelUploads: 10,
            maxFiles: 10,
            maxFilesize: 10, // MB
            acceptedFiles: "image/jpeg,image/png,image/dicom",
            addRemoveLinks: true,
            dictDefaultMessage: "Drop X-ray images here",
            dictFallbackMessage: "Your browser doesn't support file uploads.",
            dictFileTooBig: "File is too big ({{filesize}}MB). Max filesize: {{maxFilesize}}MB.",
            dictInvalidFileType: "Invalid file type. Only JPG, PNG, DICOM allowed.",
            dictRemoveFile: "Remove",
            dictMaxFilesExceeded: "You can only upload up to 10 images",
            
            init: function() {
                this.on("addedfile", function(file) {
                    // Show preview section when files are added
                    document.getElementById('xray-preview').classList.remove('hidden');
                    
                    // Create preview element with note field
                    const preview = document.createElement('div');
                    preview.className = 'border rounded-lg p-4 bg-gray-50';
                    preview.innerHTML = `
                        <div class="flex items-start mb-3">
                            <div class="flex-1">
                                <p class="font-medium">${file.name}</p>
                                <p class="text-xs text-gray-500">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                            </div>
                            <button type="button" data-dz-remove class="text-red-600 hover:text-red-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="mb-2">
                            <label class="block text-gray-700 text-sm font-medium mb-1">Notes</label>
                            <textarea name="image_notes[]" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" rows="2"></textarea>
                            <input type="hidden" name="xray_images[]" value="${file.name}">
                        </div>
                    `;
                    
                    file.previewElement = preview;
                    document.getElementById('xray-files').appendChild(preview);
                });
                
                this.on("removedfile", function(file) {
                    if(this.files.length === 0) {
                        document.getElementById('xray-preview').classList.add('hidden');
                    }
                });
            }
        });
        
        // Update dropdowns when patient is selected
        document.getElementById('patient_id').addEventListener('change', function() {
            const patientId = this.value;
            const appointmentSelect = document.getElementById('appointment_id');
            const treatmentPlanSelect = document.getElementById('treatment_plan_id');
            
            // Filter appointments
            Array.from(appointmentSelect.options).forEach(option => {
                if (option.value === '') {
                    // Keep the "Select Appointment" option visible
                    option.style.display = '';
                    return;
                }
                
                const optionPatientId = option.getAttribute('data-patient-id');
                if (optionPatientId === patientId) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                    // Unselect if previously selected but now hidden
                    if (option.selected) {
                        appointmentSelect.value = '';
                    }
                }
            });
            
            // Filter treatment plans
            Array.from(treatmentPlanSelect.options).forEach(option => {
                if (option.value === '') {
                    // Keep the "Select Treatment Plan" option visible
                    option.style.display = '';
                    return;
                }
                
                const optionPatientId = option.getAttribute('data-patient-id');
                if (optionPatientId === patientId) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                    // Unselect if previously selected but now hidden
                    if (option.selected) {
                        treatmentPlanSelect.value = '';
                    }
                }
            });
            
            // Reset values when changing patient if no matching options
            if (appointmentSelect.querySelector('option:not([style*="display: none"])').value === '') {
                appointmentSelect.value = '';
            }
            if (treatmentPlanSelect.querySelector('option:not([style*="display: none"])').value === '') {
                treatmentPlanSelect.value = '';
            }
        });
        
        // Form submission handler
        document.querySelector('form').addEventListener('submit', function(e) {
            // Convert Dropzone files to regular file inputs
            if(myDropzone.files.length > 0) {
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.name = 'xray_images[]';
                fileInput.multiple = true;
                fileInput.style.display = 'none';
                
                const dataTransfer = new DataTransfer();
                myDropzone.files.forEach(file => {
                    dataTransfer.items.add(file);
                });
                
                fileInput.files = dataTransfer.files;
                this.appendChild(fileInput);
            }
            
            return true;
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>