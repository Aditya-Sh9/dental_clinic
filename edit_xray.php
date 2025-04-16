<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get the xray record to edit
$xray_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$xray = null;

if($xray_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM xray_records WHERE id = ?");
    $stmt->bind_param("i", $xray_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $xray = $result->fetch_assoc();
}

if(!$xray) {
    $_SESSION['error'] = "X-ray record not found";
    header("Location: xrays.php");
    exit();
}

// Get patients, appointments, and treatment plans
$patients = $conn->query("SELECT id, name FROM patients ORDER BY name");
$appointments = $conn->query("SELECT a.id, a.appointment_date, p.name as patient_name 
                             FROM appointments a
                             JOIN patients p ON a.patient_id = p.id
                             ORDER BY a.appointment_date DESC");
// Get treatment plans
$treatment_plans = $conn->query("SELECT tp.id, tp.title, p.name as patient_name, tp.patient_id 
                                FROM treatment_plans tp
                                JOIN patients p ON tp.patient_id = p.id
                                ORDER BY tp.created_at DESC");

// Get existing images for this xray
$images = $conn->query("SELECT * FROM xray_images WHERE xray_id = $xray_id");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $conn->real_escape_string($_POST['patient_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $taken_date = $conn->real_escape_string($_POST['taken_date']);
    $user_id = $_SESSION['user']['id'];
    
    // Handle optional fields - convert empty strings to NULL
    $appointment_id = !empty($_POST['appointment_id']) ? $conn->real_escape_string($_POST['appointment_id']) : NULL;
    $treatment_plan_id = !empty($_POST['treatment_plan_id']) ? $conn->real_escape_string($_POST['treatment_plan_id']) : NULL;

    // Update X-ray record
    $stmt = $conn->prepare("UPDATE xray_records 
                          SET patient_id = ?, 
                              appointment_id = ?, 
                              treatment_plan_id = ?, 
                              title = ?, 
                              description = ?, 
                              taken_date = ?
                          WHERE id = ?");
    $stmt->bind_param("iiisssi", 
        $patient_id, 
        $appointment_id, 
        $treatment_plan_id, 
        $title, 
        $description, 
        $taken_date,
        $xray_id);
    
    if($stmt->execute()) {
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
        
        // Handle image deletions
        if(!empty($_POST['delete_images'])) {
            foreach($_POST['delete_images'] as $image_id) {
                $image_id = intval($image_id);
                // First get the file path to delete the physical file
                $img_stmt = $conn->prepare("SELECT file_path FROM xray_images WHERE id = ?");
                $img_stmt->bind_param("i", $image_id);
                $img_stmt->execute();
                $img_result = $img_stmt->get_result();
                if($img_row = $img_result->fetch_assoc()) {
                    if(file_exists($img_row['file_path'])) {
                        unlink($img_row['file_path']);
                    }
                }
                
                // Then delete the database record
                $del_stmt = $conn->prepare("DELETE FROM xray_images WHERE id = ?");
                $del_stmt->bind_param("i", $image_id);
                $del_stmt->execute();
            }
        }
        
        $_SESSION['success'] = "X-ray record updated successfully";
        header("Location: view_xray.php?id=$xray_id");
        exit();
    } else {
        $error = "Error updating X-ray record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit X-Ray Record - Toothly</title>
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.css">
    <style>
        .dropzone {
            border: 2px dashed #4CAF50;
            background: #E8F5E9;
        }
        .dropzone .dz-message {
            color: #2E7D32;
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
                <h1 class="text-2xl font-bold text-green-900">Edit X-Ray Record</h1>
                <a href="xrays.php" class="text-green-600 hover:text-green-800 font-medium flex items-center">
                    <i class="fas fa-arrow-left mr-1"></i> Back to X-Rays
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
                        <select name="patient_id" id="patient_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                            <option value="">Select Patient</option>
                            <?php while($patient = $patients->fetch_assoc()): ?>
                            <option value="<?= $patient['id'] ?>" <?= $xray['patient_id'] == $patient['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($patient['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="appointment_id">Linked Appointment</label>
                        <select name="appointment_id" id="appointment_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Select Appointment (Optional)</option>
                            <?php 
                            if ($appointments && $appointments->num_rows > 0) {
                                while($appointment = $appointments->fetch_assoc()): 
                                    $selected = (isset($xray['appointment_id']) && $xray['appointment_id'] == $appointment['id']) ? 'selected' : '';
                                    $patientName = isset($appointment['patient_name']) ? htmlspecialchars($appointment['patient_name']) : 'Unknown Patient';
                                    $appointmentDate = isset($appointment['appointment_date']) ? date('m/d/Y', strtotime($appointment['appointment_date'])) : 'Unknown Date';
                                    $patientId = isset($appointment['patient_id']) ? $appointment['patient_id'] : '';
                            ?>
                                <option value="<?= $appointment['id'] ?>" 
                                    data-patient-id="<?= $patientId ?>"
                                    <?= $selected ?>>
                                    <?= $patientName ?> - <?= $appointmentDate ?>
                                </option>
                            <?php 
                                endwhile;
                            } else {
                                echo '<option value="" disabled>No appointments available</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="treatment_plan_id">Linked Treatment Plan</label>
                        <select name="treatment_plan_id" id="treatment_plan_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Select Treatment Plan (Optional)</option>
                            <?php while($plan = $treatment_plans->fetch_assoc()): ?>
                            <option value="<?= $plan['id'] ?>" 
                                data-patient-id="<?= $plan['patient_id'] ?>"
                                <?= isset($xray['treatment_plan_id']) && $xray['treatment_plan_id'] == $plan['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($plan['patient_name']) ?> - <?= htmlspecialchars($plan['title']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="taken_date">Date Taken *</label>
                        <input type="date" name="taken_date" id="taken_date" 
                               value="<?= date('Y-m-d', strtotime($xray['taken_date'])) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    </div>
                    
                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="title">Title *</label>
                        <input type="text" name="title" id="title" 
                               value="<?= htmlspecialchars($xray['title']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    </div>
                    
                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="description">Description</label>
                        <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"><?= htmlspecialchars($xray['description']) ?></textarea>
                    </div>
                </div>
                
                <!-- Existing X-Ray Images -->
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h2 class="text-xl font-semibold text-green-900 mb-4">Existing Images</h2>
                    
                    <?php if($images->num_rows > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <?php while($image = $images->fetch_assoc()): ?>
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 flex items-start">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <a href="<?= $image['file_path'] ?>" target="_blank" class="text-green-600 hover:text-green-800 mr-3">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <span class="text-sm text-gray-500"><?= $image['file_name'] ?></span>
                                    </div>
                                    <?php if($image['notes']): ?>
                                        <p class="text-sm"><?= htmlspecialchars($image['notes']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="delete_images[]" value="<?= $image['id'] ?>" id="delete_<?= $image['id'] ?>" class="mr-2 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                    <label for="delete_<?= $image['id'] ?>" class="text-red-600 hover:text-red-800 cursor-pointer">
                                        <i class="fas fa-trash"></i>
                                    </label>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 mb-6">No images uploaded for this X-ray record</p>
                    <?php endif; ?>
                </div>
                
                <!-- New X-Ray Images Upload -->
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h2 class="text-xl font-semibold text-green-900 mb-4">Add New X-Ray Images</h2>
                    
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
                
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition mt-6">
                    <i class="fas fa-save mr-2"></i> Update X-Ray Record
                </button>
            </form>
        </div>
    </div>
    
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
                    document.getElementById('xray-preview').classList.remove('hidden');
                    
                    const preview = document.createElement('div');
                    preview.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50';
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
                            <textarea name="image_notes[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" rows="2"></textarea>
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
        
        document.getElementById('patient_id').addEventListener('change', function() {
            const patientId = this.value;
            const appointmentSelect = document.getElementById('appointment_id');
            const treatmentPlanSelect = document.getElementById('treatment_plan_id');
            
            Array.from(appointmentSelect.options).forEach(option => {
                if(option.value !== '') {
                    const optionPatientId = option.getAttribute('data-patient-id');
                    option.style.display = (optionPatientId === patientId) ? '' : 'none';
                }
            });
            
            Array.from(treatmentPlanSelect.options).forEach(option => {
                if(option.value !== '') {
                    const optionPatientId = option.getAttribute('data-patient-id');
                    option.style.display = (optionPatientId === patientId) ? '' : 'none';
                }
            });
        });
        
        document.querySelector('form').addEventListener('submit', function(e) {
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