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

// Get view type (week/month)
$view = isset($_GET['view']) ? $_GET['view'] : 'week';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Calculate start and end dates based on view
if ($view === 'month') {
    $start_date = date('Y-m-01', strtotime($date));
    $end_date = date('Y-m-t', strtotime($date));
} else {
    $start_date = date('Y-m-d', strtotime('monday this week', strtotime($date)));
    $end_date = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
}

// Get appointments
$query = "SELECT a.*, p.name as patient_name, d.name as doctor_name, d.color as doctor_color 
          FROM appointments a
          JOIN patients p ON a.patient_id = p.id
          JOIN doctors d ON a.doctor_id = d.id
          WHERE a.appointment_date BETWEEN '$start_date' AND '$end_date'
          ORDER BY a.appointment_date, a.appointment_time";
$appointments_result = $conn->query($query);

// Get all doctors for filter
$doctors = $conn->query("SELECT * FROM doctors");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Calendar - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        .fc-event {
            cursor: pointer;
            border-radius: 0.375rem;
            padding: 0.2rem 0.4rem;
            font-size: 0.875rem;
            border: none;
        }
        .fc-daygrid-event-dot {
            display: none;
        }
        .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e40af;
        }
        .fc-button {
            background-color: #3b82f6 !important;
            border: none !important;
            color: white !important;
            border-radius: 0.375rem !important;
            padding: 0.375rem 0.75rem !important;
            font-weight: 500 !important;
        }
        .fc-button:hover {
            background-color: #2563eb !important;
        }
        .fc-button-active {
            background-color: #1e40af !important;
        }
        .fc-daygrid-day-number {
            color: #1e40af;
            font-weight: 600;
        }
        .fc-col-header-cell {
            background-color: #eff6ff;
        }
        .fc-day-today {
            background-color: #dbeafe !important;
        }
        /* Modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            max-width: 28rem;
            width: 100%;
        }
    </style>
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(219, 234, 254, 0.5);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.7);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(37, 99, 235, 0.9);
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Translucent effects */
        .translucent-card {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">Appointment Calendar</h1>
                <div class="flex space-x-4">
                    <a href="add_appointment.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-plus mr-2"></i> New Appointment
                    </a>
                    <div class="flex space-x-2">
                        <a href="calendar.php?view=week&date=<?= $date ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition <?= $view === 'week' ? 'bg-blue-800' : '' ?>">
                            Week
                        </a>
                        <a href="calendar.php?view=month&date=<?= $date ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition <?= $view === 'month' ? 'bg-blue-800' : '' ?>">
                            Month
                        </a>
                    </div>
                </div>
            </div>

            <!-- Calendar View -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <?php if ($view === 'week'): ?>
                    <!-- Weekly View -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th class="p-3 text-left text-blue-800">Time</th>
                                    <?php 
                                    $current = strtotime($start_date);
                                    while ($current <= strtotime($end_date)): 
                                        $day = date('Y-m-d', $current);
                                        $isToday = $day == date('Y-m-d');
                                    ?>
                                        <th class="p-3 text-center text-blue-800 <?= $isToday ? 'bg-blue-100' : '' ?>">
                                            <?= date('D, M j', $current) ?>
                                        </th>
                                    <?php 
                                        $current = strtotime('+1 day', $current);
                                    endwhile; 
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($h = 9; $h < 18; $h++): ?>
                                    <tr class="border-b">
                                        <td class="p-3 text-gray-600"><?= sprintf("%02d:00", $h) ?></td>
                                        <?php 
                                        $current = strtotime($start_date);
                                        while ($current <= strtotime($end_date)): 
                                            $day = date('Y-m-d', $current);
                                        ?>
                                            <td class="p-2 h-24 border-l">
                                                <?php
                                                $appointments_result->data_seek(0);
                                                while ($appointment = $appointments_result->fetch_assoc()):
                                                    $apptTime = strtotime($appointment['appointment_time']);
                                                    $apptHour = date('H', $apptTime);
                                                    $apptDay = $appointment['appointment_date'];
                                                    
                                                    if ($apptDay == $day && $apptHour == $h):
                                                ?>
                                                    <div class="mb-1 p-2 rounded-lg text-white text-sm" 
                                                         style="background-color: <?= $appointment['doctor_color'] ?>">
                                                        <div class="font-medium"><?= $appointment['patient_name'] ?></div>
                                                        <div class="text-xs"><?= date('g:i A', $apptTime) ?></div>
                                                        <div class="text-xs"><?= $appointment['doctor_name'] ?></div>
                                                    </div>
                                                <?php
                                                    endif;
                                                endwhile;
                                                ?>
                                            </td>
                                        <?php 
                                            $current = strtotime('+1 day', $current);
                                        endwhile; 
                                        ?>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Monthly View -->
                    <div id="calendar" class="min-h-[600px]"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    initialDate: '<?= $date ?>',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek'
                    },
                    events: [
                        <?php 
                        $appointments_result->data_seek(0);
                        while ($appointment = $appointments_result->fetch_assoc()): 
                        ?>
                        {
                            title: '<?= addslashes($appointment['patient_name']) ?> - <?= addslashes($appointment['doctor_name']) ?>',
                            start: '<?= $appointment['appointment_date'] ?>T<?= $appointment['appointment_time'] ?>',
                            color: '<?= $appointment['doctor_color'] ?>',
                            extendedProps: {
                                reason: '<?= addslashes($appointment['reason']) ?>',
                                doctor: '<?= addslashes($appointment['doctor_name']) ?>',
                                id: '<?= $appointment['id'] ?>'
                            }
                        },
                        <?php endwhile; ?>
                    ],
                    eventClick: function(info) {
                        info.jsEvent.preventDefault();
                        
                        const event = info.event;
                        const reason = event.extendedProps.reason || 'No reason provided';
                        const doctor = event.extendedProps.doctor || 'Unknown doctor';
                        
                        // Create modal overlay
                        const modalOverlay = document.createElement('div');
                        modalOverlay.className = 'modal-overlay';
                        
                        // Create modal content
                        const modalContent = document.createElement('div');
                        modalContent.className = 'modal-content';
                        modalContent.innerHTML = `
                            <h3 class="text-xl font-bold text-blue-800 mb-2">${event.title}</h3>
                            <p class="text-gray-700 mb-1"><strong>Date:</strong> ${event.start.toLocaleString()}</p>
                            <p class="text-gray-700 mb-1"><strong>Doctor:</strong> ${doctor}</p>
                            <p class="text-gray-700 mb-4"><strong>Reason:</strong> ${reason}</p>
                            <div class="flex justify-end space-x-3">
                                <a href="edit_appointment.php?id=${event.extendedProps.id}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                    Edit
                                </a>
                                <button class="close-modal bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                                    Close
                                </button>
                            </div>
                        `;
                        
                        // Add click handler for close button
                        const closeBtn = modalContent.querySelector('.close-modal');
                        closeBtn.addEventListener('click', function() {
                            document.body.removeChild(modalOverlay);
                        });
                        
                        // Close when clicking outside modal
                        modalOverlay.addEventListener('click', function(e) {
                            if (e.target === modalOverlay) {
                                document.body.removeChild(modalOverlay);
                            }
                        });
                        
                        // Add elements to DOM
                        modalOverlay.appendChild(modalContent);
                        document.body.appendChild(modalOverlay);
                    }
                });
                calendar.render();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>