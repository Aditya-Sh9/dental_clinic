<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "dental_clinic";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch appointment data if ID is provided
$appointment = null;
$patients = [];
if(isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $result = $conn->query("SELECT * FROM appointments WHERE id = $id");
    if($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
    }
    
    // Get all patients for dropdown
    $patients_result = $conn->query("SELECT id, name FROM patients");
    while($row = $patients_result->fetch_assoc()) {
        $patients[] = $row;
    }
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $conn->real_escape_string($_POST['id']);
    $patient_id = $conn->real_escape_string($_POST['patient_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $appointment_time = $conn->real_escape_string($_POST['appointment_time']);
    $reason = $conn->real_escape_string($_POST['reason']);

    $doctor_id = $conn->real_escape_string($_POST['doctor_id']);
    $sql = "UPDATE appointments SET 
            patient_id = '$patient_id',
            doctor_id = '$doctor_id',
            appointment_date = '$appointment_date',
            appointment_time = '$appointment_time',
            reason = '$reason'
            WHERE id = $id";
    
    if($conn->query($sql)) {
        header("Location: appointments.php");
        exit();
    } else {
        $error = "Error updating appointment: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">Edit Appointment</h1>
                <a href="appointments.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Appointments
                </a>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($appointment): ?>
            <form method="POST" class="max-w-lg bg-white p-6 rounded-xl shadow-md">
                <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="doctor_id">Doctor</label>
                    <select name="doctor_id" id="doctor_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select Doctor</option>
                        <?php 
                        $doctors = $conn->query("SELECT * FROM doctors");
                        while($doctor = $doctors->fetch_assoc()): 
                        ?>
                        <option value="<?= $doctor['id'] ?>" <?= isset($appointment) && $appointment['doctor_id'] == $doctor['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($doctor['name']) ?> (<?= $doctor['specialty'] ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="patient_id">Patient</label>
                    <select name="patient_id" id="patient_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select Patient</option>
                        <?php foreach($patients as $patient): ?>
                        <option value="<?php echo $patient['id']; ?>" <?php echo $patient['id'] == $appointment['patient_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($patient['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="appointment_date">Date</label>
                    <input type="date" name="appointment_date" id="appointment_date" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="<?php echo htmlspecialchars($appointment['appointment_date']); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="appointment_time">Time</label>
                    <input type="time" name="appointment_time" id="appointment_time" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="<?php echo htmlspecialchars($appointment['appointment_time']); ?>" required>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="reason">Reason</label>
                    <textarea name="reason" id="reason" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($appointment['reason']); ?></textarea>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
            </form>
            <?php else: ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    Appointment not found or invalid ID provided.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>