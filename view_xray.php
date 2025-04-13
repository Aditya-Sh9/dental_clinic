<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

$xray_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get X-ray record details
$stmt = $conn->prepare("SELECT xr.*, p.name as patient_name, 
                       a.appointment_date, tp.title as treatment_title
                       FROM xray_records xr
                       JOIN patients p ON xr.patient_id = p.id
                       LEFT JOIN appointments a ON xr.appointment_id = a.id
                       LEFT JOIN treatment_plans tp ON xr.treatment_plan_id = tp.id
                       WHERE xr.id = ?");
$stmt->bind_param("i", $xray_id);
$stmt->execute();
$xray = $stmt->get_result()->fetch_assoc();

if(!$xray) {
    $_SESSION['error'] = "X-ray record not found";
    header("Location: xrays.php");
    exit();
}

// Get X-ray images
$images = $conn->query("SELECT * FROM xray_images WHERE xray_id = $xray_id ORDER BY uploaded_at DESC");

// Handle note updates
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_notes'])) {
    $image_id = intval($_POST['image_id']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    $stmt = $conn->prepare("UPDATE xray_images SET notes = ? WHERE id = ?");
    $stmt->bind_param("si", $notes, $image_id);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Notes updated successfully";
    } else {
        $_SESSION['error'] = "Error updating notes";
    }
    
    header("Location: view_xray.php?id=$xray_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View X-Ray - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">X-Ray Record</h1>
                <div class="flex space-x-2">
                    <a href="edit_xray.php?id=<?= $xray_id ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </a>
                    <a href="xrays.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to X-Rays
                    </a>
                </div>
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
            
            <!-- X-Ray Overview -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-blue-800 mb-2">Patient Information</h2>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Patient:</span> <?= htmlspecialchars($xray['patient_name']) ?></p>
                        <?php if($xray['appointment_date']): ?>
                        <p class="text-gray-700"><span class="font-medium">Linked Appointment:</span> <?= date('m/d/Y', strtotime($xray['appointment_date'])) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <h2 class="text-lg font-semibold text-blue-800 mb-2">Record Details</h2>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Title:</span> <?= htmlspecialchars($xray['title']) ?></p>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Date Taken:</span> <?= date('m/d/Y', strtotime($xray['taken_date'])) ?></p>
                        <?php if($xray['treatment_title']): ?>
                        <p class="text-gray-700"><span class="font-medium">Linked Treatment:</span> <?= htmlspecialchars($xray['treatment_title']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-blue-800 mb-2">Description</h2>
                    <div class="text-gray-700 bg-gray-50 p-4 rounded-lg">
                        <?= nl2br(htmlspecialchars($xray['description'])) ?>
                    </div>
                </div>
            </div>
            
            <!-- X-Ray Images -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-blue-800">X-Ray Images</h2>
                </div>
                
                <?php if($images->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php while($image = $images->fetch_assoc()): ?>
                        <div class="border rounded-lg p-4 hover:shadow-md transition">
                            <div class="mb-3">
                                <img src="<?= $image['file_path'] ?>" 
                                     alt="<?= htmlspecialchars($image['file_name']) ?>" 
                                     class="w-full h-48 object-contain">
                            </div>
                            <div class="mb-2">
                                <p class="font-medium truncate"><?= htmlspecialchars($image['file_name']) ?></p>
                                <p class="text-xs text-gray-500">
                                    Uploaded on <?= date('m/d/Y', strtotime($image['uploaded_at'])) ?>
                                </p>
                            </div>
                            
                            <form method="POST" class="mb-3">
                                <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                                <label class="block text-gray-700 text-sm font-medium mb-1">Notes</label>
                                <textarea name="notes" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="2"><?= htmlspecialchars($image['notes']) ?></textarea>
                                <button type="submit" name="update_notes" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-1 px-3 rounded-lg shadow transition text-sm">
                                    Update Notes
                                </button>
                            </form>
                            
                            <div class="flex space-x-2">
                                <a href="<?= $image['file_path'] ?>" download class="text-green-600 hover:text-green-800 text-sm">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                                <a href="delete_xray_image.php?image_id=<?= $image['id'] ?>&xray_id=<?= $xray_id ?>" 
                                   class="text-red-600 hover:text-red-800 text-sm"
                                   onclick="return confirm('Are you sure you want to delete this image?')">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 py-4">No X-ray images uploaded yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>