<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Fetch patient data if ID is provided
$patient = null;
if(isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $result = $conn->query("SELECT * FROM patients WHERE id = $id");
    if($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
    }
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $conn->real_escape_string($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $gender = $conn->real_escape_string($_POST['gender']);

    $sql = "UPDATE patients SET 
            name = '$name',
            email = '$email',
            phone = '$phone',
            address = '$address',
            dob = " . ($dob ? "'$dob'" : "NULL") . ",
            gender = " . ($gender ? "'$gender'" : "NULL") . "
            WHERE id = $id";

    if($conn->query($sql)) {
        $_SESSION['success'] = "Patient updated successfully";
        header("Location: patients.php");
        exit();
    } else {
        $error = "Error updating patient: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient - Toothly</title>
    <link rel="icon" type="image/png" href="images/teeth.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F5F5;
        }
    </style>
</head>
<body>
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-green-900">Edit Patient</h1>
                <a href="patients.php" class="text-green-600 hover:text-green-800 font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Patients
                </a>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($patient): ?>
            <form method="POST" class="max-w-lg bg-white p-6 rounded-xl shadow-md">
                <input type="hidden" name="id" value="<?php echo $patient['id']; ?>">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="name">Full Name *</label>
                    <input type="text" name="name" id="name" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           value="<?php echo htmlspecialchars($patient['name']); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="email">Email</label>
                    <input type="email" name="email" id="email" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           value="<?php echo htmlspecialchars($patient['email']); ?>">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="phone">Phone</label>
                    <input type="tel" name="phone" id="phone" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           value="<?php echo htmlspecialchars($patient['phone']); ?>">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="dob">Date of Birth</label>
                    <input type="date" name="dob" id="dob" 
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           max="<?php echo date('Y-m-d'); ?>"
                           value="<?php echo $patient['dob'] ? htmlspecialchars($patient['dob']) : ''; ?>">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="gender">Gender</label>
                    <select name="gender" id="gender" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">-- Select Gender --</option>
                        <option value="Male" <?php echo ($patient['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($patient['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($patient['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="address">Address</label>
                    <textarea name="address" id="address" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"><?php echo htmlspecialchars($patient['address']); ?></textarea>
                </div>
                
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
            </form>
            <?php else: ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    Patient not found or invalid ID provided.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            
            if(name === '') {
                Swal.fire({
                    title: 'Error',
                    text: 'Please enter patient name',
                    icon: 'error',
                    confirmButtonColor: '#2E7D32'
                });
                e.preventDefault();
                return;
            }
            
            if(phone && !/^[\d\s\-()+]{10,}$/.test(phone)) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please enter a valid phone number',
                    icon: 'error',
                    confirmButtonColor: '#2E7D32'
                });
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>