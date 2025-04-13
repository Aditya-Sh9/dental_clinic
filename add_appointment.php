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

// Get all patients for dropdown
$patients_query = "SELECT id, name FROM patients ORDER BY name ASC";
$patients_result = $conn->query($patients_query);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $conn->real_escape_string($_POST['patient_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $appointment_time = $conn->real_escape_string($_POST['appointment_time']);
    $reason = $conn->real_escape_string($_POST['reason']);

    // Check if time slot is available
    $check_sql = "SELECT id FROM appointments 
                 WHERE appointment_date = '$appointment_date' 
                 AND appointment_time = '$appointment_time'";
    $check_result = $conn->query($check_sql);
    
    if($check_result->num_rows > 0) {
        $error = "This time slot is already booked. Please choose another time.";
    } else {
        $insert_sql = "INSERT INTO appointments (patient_id, appointment_date, appointment_time, reason) 
                      VALUES ('$patient_id', '$appointment_date', '$appointment_time', '$reason')";

        if ($conn->query($insert_sql)) {
            $_SESSION['success'] = "Appointment booked successfully";
            header("Location: appointments.php");
            exit();
        } else {
            $error = "Error booking appointment: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Appointment - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">Schedule New Appointment</h1>
                <a href="appointments.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Appointments
                </a>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="max-w-lg bg-white p-6 rounded-xl shadow-md">
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
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="patient_id">Patient *</label>
                    <select name="patient_id" id="patient_id" 
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select Patient</option>
                        <?php while($patient = $patients_result->fetch_assoc()): ?>
                        <option value="<?php echo $patient['id']; ?>">
                            <?php echo htmlspecialchars($patient['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="appointment_date">Date *</label>
                        <input type="date" name="appointment_date" id="appointment_date" 
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="appointment_time">Time *</label>
                        <input type="time" name="appointment_time" id="appointment_time" 
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               min="09:00" max="17:00" step="900" required>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="reason">Reason for Visit</label>
                    <textarea name="reason" id="reason" rows="3" 
                              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Brief description of the appointment reason"></textarea>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center justify-center">
                    <i class="fas fa-calendar-plus mr-2"></i> Book Appointment
                </button>
            </form>
        </div>
    </div>

    <script>
        // Client-side validation and time slot suggestions
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('appointment_date');
            const timeInput = document.getElementById('appointment_time');
            const form = document.querySelector('form');
            
            // Set default time to next available hour
            const now = new Date();
            const nextHour = new Date(now.getTime() + 60 * 60 * 1000);
            const hours = nextHour.getHours().toString().padStart(2, '0');
            const minutes = Math.ceil(nextHour.getMinutes() / 15) * 15;
            timeInput.value = `${hours}:${minutes.toString().padStart(2, '0')}`;
            
            // Set default date to today
            dateInput.valueAsDate = new Date();
            
            form.addEventListener('submit', function(e) {
                const selectedDate = new Date(dateInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if(selectedDate < today) {
                    alert('Please select a current or future date');
                    e.preventDefault();
                    return;
                }
                
                const selectedTime = timeInput.value;
                if(!selectedTime) {
                    alert('Please select an appointment time');
                    e.preventDefault();
                    return;
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>