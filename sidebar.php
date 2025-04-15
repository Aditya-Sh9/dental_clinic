<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .swal2-popup {
        font-family: 'Inter', sans-serif;
        border-radius: 0.75rem !important;
    }
    .swal2-confirm {
        background-color: #2E7D32 !important;
        border-radius: 0.5rem !important;
    }
    .swal2-cancel {
        border-radius: 0.5rem !important;
    }
</style>
<div class="w-64 bg-gradient-to-t from-green-800 to-green-600 text-white shadow-lg">
    <div class="p-4 border-b border-green-600">
        <div class="flex items-center space-x-2">
            <i class="fas fa-tooth text-white text-2xl"></i>
            <h1 class="text-xl font-bold">Toothly</h1>
        </div>
        <p class="text-sm text-green-200 mt-1">Welcome, <?php echo $_SESSION['user']['name']; ?></p>
    </div>
    <nav class="p-4">
        <ul class="space-y-2">
            <li>
                <a href="dashboard.php" class="flex items-center space-x-2 py-2 px-4 <?php echo $current_page == 'dashboard.php' ? 'bg-green-600' : 'hover:bg-green-600' ?> rounded-lg transition duration-300">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="patients.php" class="flex items-center space-x-2 py-2 px-4 <?php echo $current_page == 'patients.php' ? 'bg-green-600' : 'hover:bg-green-600' ?> rounded-lg transition duration-300">
                    <i class="fas fa-users"></i>
                    <span>Patients</span>
                </a>
            </li>
            <li>
                <a href="patient_records.php" class="flex items-center space-x-2 py-2 px-4 <?php echo $current_page == 'patient_records.php' ? 'bg-green-600' : 'hover:bg-green-600' ?> rounded-lg transition duration-300">
                    <i class="fas fa-notes-medical"></i>
                    <span>Patient Records</span>
                </a>
            </li>
            <li>
                <a href="appointments.php" class="flex items-center space-x-2 py-2 px-4 <?php echo $current_page == 'appointments.php' ? 'bg-green-600' : 'hover:bg-green-600' ?> rounded-lg transition duration-300">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointments</span>
                </a>
            </li>
            <li>
                <a href="calendar.php" class="flex items-center space-x-2 py-2 px-4 <?php echo $current_page == 'calendar.php' ? 'bg-green-600' : 'hover:bg-green-600' ?> rounded-lg transition duration-300">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Calendar</span>
                </a>
            </li>
            <li>
                <a href="treatment_plans.php" class="flex items-center space-x-2 py-2 px-4 <?php echo $current_page == 'treatment_plans.php' ? 'bg-green-600' : 'hover:bg-green-600' ?> rounded-lg transition duration-300">
                    <i class="fas fa-file-medical"></i>
                    <span>Treatment Plans</span>
                </a>
            </li>
            <li>
                <a href="xrays.php" class="flex items-center space-x-2 py-2 px-4 <?php echo $current_page == 'xrays.php' ? 'bg-green-600' : 'hover:bg-green-600' ?> rounded-lg transition duration-300">
                    <i class="fas fa-x-ray"></i>
                    <span>X-Rays</span>
                </a>
            </li>
            <li>
                <a href="insurance.php" class="flex items-center space-x-2 py-2 px-4 <?php echo $current_page == 'insurance.php' ? 'bg-green-600' : 'hover:bg-green-600' ?> rounded-lg transition duration-300">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Insurance</span>
                </a>
            </li>
            <li>
                <a href="recalls.php" class="flex items-center space-x-2 py-2 px-4 <?php echo $current_page == 'recalls.php' ? 'bg-green-600' : 'hover:bg-green-600' ?> rounded-lg transition duration-300">
                    <i class="fas fa-bell"></i>
                    <span>Recalls</span>
                </a>
            </li>
            <li class="pt-4 mt-4 border-t border-green-600">
                <a href="logout.php" class="flex items-center space-x-2 py-2 px-4 hover:bg-green-600 rounded-lg transition duration-300">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</div>