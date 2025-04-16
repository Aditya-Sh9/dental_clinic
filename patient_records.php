<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get patient ID from URL
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no patient ID provided, show patient selection
if($patient_id <= 0) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $query = "SELECT id, name, email FROM patients";
    if(!empty($search)) {
        $query .= " WHERE name LIKE '%$search%' OR email LIKE '%$search%'";
    }
    $query .= " ORDER BY name LIMIT 50";
    $patients_result = $conn->query($query);
    
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Select Patient - Toothly</title>
        <link rel="icon" type="image/png" href="images/teeth.png">
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
            body {
                font-family: 'Inter', sans-serif;
                background-color: #F5F5F5;
            }
            .bg-primary {
                background-color: #4CAF50;
            }
            .bg-primary-dark {
                background-color: #2E7D32;
            }
            .text-primary {
                color: #4CAF50;
            }
            .text-primary-dark {
                color: #2E7D32;
            }
            .border-primary {
                border-color: #4CAF50;
            }
            .hover\:bg-primary:hover {
                background-color: #4CAF50;
            }
            .hover\:bg-primary-dark:hover {
                background-color: #2E7D32;
            }
        </style>
    </head>
    <body class="bg-gray-50">
        <div class="flex min-h-screen">
            <?php include('sidebar.php'); ?>
            
            <div class="flex-1 p-8">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold text-green-900">Select Patient</h1>
                    <form method="GET" class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" name="search" placeholder="Search patients..." 
                                   class="w-full px-4 py-2 border rounded-lg pl-10 focus:outline-none focus:ring-2 focus:ring-primary"
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition">
                            Search
                        </button>
                    </form>
                </div>
                
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <?php if($patients_result->num_rows > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                            <?php while($patient = $patients_result->fetch_assoc()): ?>
                                <a href="patient_records.php?id=<?= $patient['id'] ?>" class="border rounded-lg p-4 hover:bg-green-50 transition">
                                    <div class="flex items-center space-x-4">
                                        <div class="h-12 w-12 rounded-full bg-green-600 bg-opacity-10 flex items-center justify-center text-green-700 text-xl font-bold">
                                            <?= substr($patient['name'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900"><?= htmlspecialchars($patient['name']) ?></h3>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars($patient['email']) ?></p>
                                        </div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-6 text-center text-gray-500">
                            No patients found
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Get patient basic info
$patient_query = "SELECT * FROM patients WHERE id = ?";
$stmt = $conn->prepare($patient_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient_result = $stmt->get_result();

if($patient_result->num_rows === 0) {
    $_SESSION['error'] = "Patient not found";
    header("Location: patients.php");
    exit();
}

$patient = $patient_result->fetch_assoc();

// Get patient appointments
$appointments_query = "SELECT * FROM appointments WHERE patient_id = ? ORDER BY appointment_date DESC";
$stmt = $conn->prepare($appointments_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments = $stmt->get_result();

// Get patient treatment plans (replacing treatments)
$treatment_plans_query = "SELECT tp.*, a.appointment_date, d.name as doctor_name 
                     FROM treatment_plans tp
                     LEFT JOIN appointments a ON tp.appointment_id = a.id
                     LEFT JOIN doctors d ON a.doctor_id = d.id
                     WHERE tp.patient_id = ? 
                     ORDER BY tp.created_at DESC";
$stmt = $conn->prepare($treatment_plans_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$treatment_plans = $stmt->get_result();

// Get patient x-rays (using xray_records table)
// Replace the xrays_query in patient_records.php with:
$xrays_query = "SELECT xr.*, xi.id as image_id, xi.file_path, 
                a.appointment_date, tp.title as treatment_plan_title
                FROM xray_records xr
                LEFT JOIN xray_images xi ON xr.id = xi.xray_id
                LEFT JOIN appointments a ON xr.appointment_id = a.id
                LEFT JOIN treatment_plans tp ON xr.treatment_plan_id = tp.id
                WHERE xr.patient_id = ?
                GROUP BY xr.id
                ORDER BY xr.taken_date DESC";
$stmt = $conn->prepare($xrays_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$xrays = $stmt->get_result();

// Get patient insurance (using patient_insurance table)
$insurance_query = "SELECT pi.*, ip.name as provider_name 
                    FROM patient_insurance pi
                    JOIN insurance_providers ip ON pi.provider_id = ip.id
                    WHERE pi.patient_id = ?";
$stmt = $conn->prepare($insurance_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$insurance = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($patient['name']) ?> - Patient Records - Toothly</title>
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F5F5;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tab-button.active {
            border-bottom: 3px solid #4CAF50;
            color: #2E7D32;
            font-weight: 600;
        }
        .bg-primary {
            background-color: #4CAF50;
        }
        .bg-primary-dark {
            background-color: #2E7D32;
        }
        .text-primary {
            color: #4CAF50;
        }
        .text-primary-dark {
            color: #2E7D32;
        }
        .border-primary {
            border-color: #4CAF50;
        }
        .hover\:bg-primary:hover {
            background-color: #4CAF50;
        }
        .hover\:bg-primary-dark:hover {
            background-color: #2E7D32;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-green-900">Patient Records</h1>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="patients.php" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                                    Patients
                                </a>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <span class="mx-2 text-gray-400">/</span>
                                    <span class="text-sm font-medium text-gray-500"><?= htmlspecialchars($patient['name']) ?></span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>
                <div class="flex space-x-3">
                    <a href="patients.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Patients
                    </a>
                    <a href="edit_patient.php?id=<?= $patient_id ?>" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-edit mr-2"></i> Edit Patient
                    </a>
                </div>
            </div>

            <!-- Patient Summary Card -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                    <div class="flex-shrink-0">
                        <div class="h-20 w-20 rounded-full bg-green-600 bg-opacity-10 flex items-center justify-center text-primary text-3xl font-bold">
                            <?= substr($patient['name'], 0, 1) ?>
                        </div>
                    </div>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($patient['name']) ?></h3>
                            <p class="text-gray-600"><?= $patient['gender'] ? htmlspecialchars($patient['gender']) : '--' ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600"><i class="fas fa-birthday-cake mr-2 text-primary"></i> <?= $patient['dob'] ? date('M j, Y', strtotime($patient['dob'])) : '--' ?></p>
                            <p class="text-gray-600"><i class="fas fa-phone mr-2 text-primary"></i> <?= $patient['phone'] ? htmlspecialchars($patient['phone']) : '--' ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600"><i class="fas fa-envelope mr-2 text-primary"></i> <?= $patient['email'] ? htmlspecialchars($patient['email']) : '--' ?></p>
                            <p class="text-gray-600"><i class="fas fa-map-marker-alt mr-2 text-primary"></i> <?= $patient['address'] ? htmlspecialchars($patient['address']) : '--' ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="flex space-x-8">
                    <button onclick="openTab('appointments')" class="tab-button py-4 px-1 text-sm font-medium active" id="appointments-tab">
                        Appointments
                    </button>
                    <button onclick="openTab('treatment-plans')" class="tab-button py-4 px-1 text-sm font-medium" id="treatment-plans-tab">
                        Treatment Plans
                    </button>
                    <button onclick="openTab('xrays')" class="tab-button py-4 px-1 text-sm font-medium" id="xrays-tab">
                        X-Rays
                    </button>
                    <button onclick="openTab('insurance')" class="tab-button py-4 px-1 text-sm font-medium" id="insurance-tab">
                        Insurance
                    </button>
                </nav>
            </div>

            <!-- Tab Contents -->
            <div class="tab-content active" id="appointments">
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                    <div class="p-6 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-primary-dark">Appointment History</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-green-50">
                                <tr>
                                    <th class="p-4 text-left text-primary-dark">Date</th>
                                    <th class="p-4 text-left text-primary-dark">Time</th>
                                    <th class="p-4 text-left text-primary-dark">Reason</th>
                                    <th class="p-4 text-left text-primary-dark">Status</th>
                                    <th class="p-4 text-left text-primary-dark">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if($appointments->num_rows > 0): ?>
                                    <?php while($appt = $appointments->fetch_assoc()): 
                                        $status = new DateTime($appt['appointment_date'] . ' ' . $appt['appointment_time']) < new DateTime() ? 'Completed' : 'Upcoming';
                                    ?>
                                    <tr class="hover:bg-green-50 transition">
                                        <td class="p-4"><?= date('M j, Y', strtotime($appt['appointment_date'])) ?></td>
                                        <td class="p-4"><?= date('g:i A', strtotime($appt['appointment_time'])) ?></td>
                                        <td class="p-4"><?= $appt['reason'] ? htmlspecialchars($appt['reason']) : '--' ?></td>
                                        <td class="p-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium <?= $status === 'Completed' ? 'bg-gray-100 text-gray-800' : 'bg-primary bg-opacity-10 text-primary-dark' ?>">
                                                <?= $status ?>
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex space-x-2">
                                                <a href="edit_appointment.php?id=<?= $appt['id'] ?>" class="text-primary hover:text-primary-dark px-2 py-1 rounded transition">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_appointment.php?id=<?= $appt['id'] ?>" class="text-red-600 hover:text-red-800 px-2 py-1 rounded transition" onclick="return confirm('Are you sure you want to delete this appointment?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="p-4 text-center text-gray-500">No appointments found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="treatment-plans">
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                    <div class="p-6 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-primary-dark">Treatment Plans</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-green-50">
                                <tr>
                                    <th class="p-4 text-left text-primary-dark">Created</th>
                                    <th class="p-4 text-left text-primary-dark">Appointment Date</th>
                                    <th class="p-4 text-left text-primary-dark">Title</th>
                                    <th class="p-4 text-left text-primary-dark">Doctor</th>
                                    <th class="p-4 text-left text-primary-dark">Status</th>
                                    <th class="p-4 text-left text-primary-dark">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if($treatment_plans->num_rows > 0): ?>
                                    <?php while($plan = $treatment_plans->fetch_assoc()): ?>
                                    <tr class="hover:bg-green-50 transition">
                                        <td class="p-4"><?= date('M j, Y', strtotime($plan['created_at'])) ?></td>
                                        <td class="p-4"><?= $plan['appointment_date'] ? date('M j, Y', strtotime($plan['appointment_date'])) : '--' ?></td>
                                        <td class="p-4"><?= htmlspecialchars($plan['title']) ?></td>
                                        <td class="p-4"><?= $plan['doctor_name'] ? htmlspecialchars($plan['doctor_name']) : '--' ?></td>
                                        <td class="p-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium 
                                                <?= $plan['status'] === 'Completed' ? 'bg-green-100 text-green-800' : 
                                                   ($plan['status'] === 'In Progress' ? 'bg-primary bg-opacity-10 text-primary-dark' : 'bg-gray-100 text-gray-800') ?>">
                                                <?= htmlspecialchars($plan['status']) ?>
                                            </span>
                                        </td>
                                        <td class="p-4"><?= $plan['description'] ? htmlspecialchars($plan['description']) : '--' ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="p-4 text-center text-gray-500">No treatment plans found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="xrays">
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                    <div class="p-6 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-primary-dark">X-Ray Records</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-green-50">
                                <tr>
                                    <th class="p-4 text-left text-primary-dark">Taken Date</th>
                                    <th class="p-4 text-left text-primary-dark">Title</th>
                                    <th class="p-4 text-left text-primary-dark">Related Treatment Plan</th>
                                    <th class="p-4 text-left text-primary-dark">Description</th>
                                    <th class="p-4 text-left text-primary-dark">View</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php 
                                $has_records = false;
                                if($xrays->num_rows > 0): 
                                    while($xray = $xrays->fetch_assoc()): 
                                        if($xray['id']): // Check if we have a valid xray record
                                            $has_records = true;
                                ?>
                                <tr class="hover:bg-green-50 transition">
                                    <td class="p-4"><?= date('M j, Y', strtotime($xray['taken_date'])) ?></td>
                                    <td class="p-4"><?= htmlspecialchars($xray['title']) ?></td>
                                    <td class="p-4"><?= $xray['treatment_plan_title'] ? htmlspecialchars($xray['treatment_plan_title']) : '--' ?></td>
                                    <td class="p-4"><?= $xray['description'] ? htmlspecialchars($xray['description']) : '--' ?></td>
                                    <td class="p-4">
                                        <?php if($xray['image_id']): ?>
                                        <a href="view_xray.php?id=<?= $xray['id'] ?>" class="text-primary hover:text-primary-dark px-2 py-1 rounded transition">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php else: ?>
                                        No Images
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php 
                                        endif;
                                    endwhile; 
                                endif; 
                                ?>
                                
                                <?php if(!$has_records): ?>
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-gray-500">No x-rays found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="insurance">
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-primary-dark">Insurance Information</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <?php if($insurance->num_rows > 0): 
                            $ins = $insurance->fetch_assoc(); ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Primary Insurance</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Provider</p>
                                            <p class="text-gray-900"><?= htmlspecialchars($ins['provider_name']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Policy Number</p>
                                            <p class="text-gray-900"><?= htmlspecialchars($ins['policy_number']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Group Number</p>
                                            <p class="text-gray-900"><?= $ins['group_number'] ? htmlspecialchars($ins['group_number']) : '--' ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Coverage Details</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Coverage Period</p>
                                            <p class="text-gray-900">
                                                <?= date('M j, Y', strtotime($ins['coverage_start'])) ?> - 
                                                <?= date('M j, Y', strtotime($ins['coverage_end'])) ?>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Annual Maximum</p>
                                            <p class="text-gray-900">$<?= number_format($ins['annual_max'], 2) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Remaining Balance</p>
                                            <p class="text-gray-900">$<?= number_format($ins['remaining_balance'], 2) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Deductible</p>
                                            <p class="text-gray-900">$<?= number_format($ins['deductible'], 2) ?> (<?= $ins['deductible_met'] ? 'Met' : 'Not Met' ?>)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="p-6 text-center">
                                <p class="text-gray-500 mb-4">No insurance information found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openTab(tabName) {
            // Hide all tab contents
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }

            // Remove active class from all tab buttons
            const tabButtons = document.getElementsByClassName('tab-button');
            for (let i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove('active');
            }

            // Show the selected tab content
            document.getElementById(tabName).classList.add('active');

            // Add active class to the clicked tab button
            document.getElementById(tabName + '-tab').classList.add('active');
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>