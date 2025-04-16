<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get filter parameters
$filter_patient = isset($_GET['patient']) && $_GET['patient'] !== '' ? intval($_GET['patient']) : null;
$filter_treatment = isset($_GET['treatment']) && $_GET['treatment'] !== '' ? intval($_GET['treatment']) : null;
$filter_appointment = isset($_GET['appointment']) && $_GET['appointment'] !== '' ? intval($_GET['appointment']) : null;

// Build query with filters
$query = "SELECT xr.*, p.name as patient_name, 
          a.appointment_date, tp.title as treatment_title
          FROM xray_records xr
          JOIN patients p ON xr.patient_id = p.id
          LEFT JOIN appointments a ON xr.appointment_id = a.id
          LEFT JOIN treatment_plans tp ON xr.treatment_plan_id = tp.id
          WHERE 1=1";
          
$params = [];
$types = '';

if($filter_patient !== null) {
    $query .= " AND xr.patient_id = ?";
    $params[] = $filter_patient;
    $types .= 'i';
}

if($filter_treatment !== null) {
    $query .= " AND xr.treatment_plan_id = ?";
    $params[] = $filter_treatment;
    $types .= 'i';
}

if($filter_appointment !== null) {
    $query .= " AND xr.appointment_id = ?";
    $params[] = $filter_appointment;
    $types .= 'i';
}

$query .= " ORDER BY xr.taken_date DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);

if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$xrays = $stmt->get_result();

// Get patients, appointments, and treatment plans for filters
$patients = $conn->query("SELECT id, name FROM patients ORDER BY name");
$appointments = $conn->query("SELECT a.id, a.appointment_date, p.name as patient_name 
                             FROM appointments a
                             JOIN patients p ON a.patient_id = p.id
                             ORDER BY a.appointment_date DESC");
$treatment_plans = $conn->query("SELECT tp.id, tp.title, p.name as patient_name 
                                FROM treatment_plans tp
                                JOIN patients p ON tp.patient_id = p.id
                                ORDER BY tp.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>X-Ray Records - Toothly</title>
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F5F5;
        }
    </style>
    <style>
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .file-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin-right: 12px;
        }
        .pdf-icon {
            background-color: #FEE2E2;
            color: #DC2626;
        }
        .image-icon {
            background-color: #E0F2FE;
            color: #0369A1;
        }
        .default-icon {
            background-color: #ECFDF5;
            color: #059669;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-green-900">X-Ray Records</h1>
                <a href="add_xray.php" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                    <i class="fas fa-plus mr-2"></i> New X-Ray
                </a>
            </div>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <h2 class="text-lg font-semibold text-green-900 mb-4">Filters</h2>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="patient">Patient</label>
                        <select name="patient" id="patient" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 ">
                            <option value="">All Patients</option>
                            <?php while($patient = $patients->fetch_assoc()): ?>
                            <option value="<?= $patient['id'] ?>" <?= $filter_patient == $patient['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($patient['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="treatment">Treatment Plan</label>
                        <select name="treatment" id="treatment" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 ">
                            <option value="">All Treatment Plans</option>
                            <?php while($plan = $treatment_plans->fetch_assoc()): ?>
                            <option value="<?= $plan['id'] ?>" <?= $filter_treatment == $plan['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($plan['patient_name']) ?> - <?= htmlspecialchars($plan['title']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="appointment">Appointment</label>
                        <select name="appointment" id="appointment" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 ">
                            <option value="">All Appointments</option>
                            <?php while($appointment = $appointments->fetch_assoc()): ?>
                            <option value="<?= $appointment['id'] ?>" <?= $filter_appointment == $appointment['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($appointment['patient_name']) ?> - <?= date('m/d/Y', strtotime($appointment['appointment_date'])) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="md:col-span-3 flex space-x-3">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition">
                            Apply Filters
                        </button>
                        <a href="xrays.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow transition">
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- X-Ray Records -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="p-3 text-left text-green-900">Patient</th>
                                <th class="p-3 text-left text-green-900">Title</th>
                                <th class="p-3 text-left text-green-900">Date Taken</th>
                                <th class="p-3 text-left text-green-900">Linked To</th>
                                <th class="p-3 text-left text-green-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if($xrays->num_rows > 0): ?>
                                <?php while($xray = $xrays->fetch_assoc()): ?>
                                <tr class="hover:bg-green-50 transition">
                                    <td class="p-3"><?= htmlspecialchars($xray['patient_name']) ?></td>
                                    <td class="p-3"><?= htmlspecialchars($xray['title']) ?></td>
                                    <td class="p-3"><?= date('m/d/Y', strtotime($xray['taken_date'])) ?></td>
                                    <td class="p-3">
                                        <?php if($xray['appointment_date']): ?>
                                            Appointment: <?= date('m/d/Y', strtotime($xray['appointment_date'])) ?>
                                        <?php elseif($xray['treatment_title']): ?>
                                            Treatment: <?= htmlspecialchars($xray['treatment_title']) ?>
                                        <?php else: ?>
                                            --
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3">
                                        <div class="flex space-x-2">
                                            <a href="view_xray.php?id=<?= $xray['id'] ?>" class="text-green-600 hover:text-green-800 px-2 py-1 rounded transition" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_xray.php?id=<?= $xray['id'] ?>" class="text-yellow-600 hover:text-yellow-800 px-2 py-1 rounded transition" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_xray.php?id=<?= $xray['id'] ?>" class="text-red-600 hover:text-red-800 px-2 py-1 rounded transition delete-btn" title="Delete" data-name="X-ray record">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-gray-500">No X-ray records found matching your filters</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle all delete buttons
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const itemName = this.getAttribute('data-name');
                const deleteUrl = this.getAttribute('href');
                
                Swal.fire({
                    title: `Delete ${itemName}?`,
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2E7D32',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'shadow-lg rounded-xl'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });
        });
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>