<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

$treatment_plan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get treatment plan details
$stmt = $conn->prepare("SELECT tp.*, p.name as patient_name, 
                        a.appointment_date, u.name as created_by
                        FROM treatment_plans tp
                        JOIN patients p ON tp.patient_id = p.id
                        LEFT JOIN appointments a ON tp.appointment_id = a.id
                        JOIN users u ON p.id = u.id
                        WHERE tp.id = ?");
$stmt->bind_param("i", $treatment_plan_id);
$stmt->execute();
$treatment_plan = $stmt->get_result()->fetch_assoc();

if(!$treatment_plan) {
    $_SESSION['error'] = "Treatment plan not found";
    header("Location: treatment_plans.php");
    exit();
}

// Get notes for this treatment plan
$notes = $conn->query("SELECT n.*, u.name as author 
                      FROM treatment_plan_notes n
                      JOIN users u ON n.user_id = u.id
                      WHERE n.treatment_plan_id = $treatment_plan_id
                      ORDER BY n.created_at DESC");

// Get documents for this treatment plan
$documents = $conn->query("SELECT d.*, u.name as uploaded_by 
                          FROM treatment_plan_documents d
                          JOIN users u ON d.uploaded_by = u.id
                          WHERE d.treatment_plan_id = $treatment_plan_id
                          ORDER BY d.uploaded_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Treatment Plan - Toothly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include('sidebar.php'); ?>
        
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-blue-800">Treatment Plan</h1>
                <div class="flex space-x-2">
                    <a href="edit_treatment_plan.php?id=<?= $treatment_plan_id ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </a>
                    <a href="treatment_plans.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Plans
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
            
            <!-- Treatment Plan Overview -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-blue-800 mb-2">Patient Information</h2>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Patient:</span> <?= htmlspecialchars($treatment_plan['patient_name']) ?></p>
                        <?php if($treatment_plan['appointment_date']): ?>
                        <p class="text-gray-700"><span class="font-medium">Linked Appointment:</span> <?= date('m/d/Y', strtotime($treatment_plan['appointment_date'])) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <h2 class="text-lg font-semibold text-blue-800 mb-2">Plan Details</h2>
                        <p class="text-gray-700 mb-1"><span class="font-medium">Status:</span> 
                            <?php
                            $statusColors = [
                                'pending' => 'bg-gray-200 text-gray-800',
                                'in_progress' => 'bg-blue-200 text-blue-800',
                                'completed' => 'bg-green-200 text-green-800',
                                'cancelled' => 'bg-red-200 text-red-800'
                            ];
                            $statusText = ucwords(str_replace('_', ' ', $treatment_plan['status']));
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?= $statusColors[$treatment_plan['status']] ?>">
                                <?= $statusText ?>
                            </span>
                        </p>
                        <p class="text-gray-700"><span class="font-medium">Created:</span> <?= date('m/d/Y', strtotime($treatment_plan['created_at'])) ?></p>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-blue-800 mb-2">Description</h2>
                    <div class="text-gray-700 bg-gray-50 p-4 rounded-lg">
                        <?= nl2br(htmlspecialchars($treatment_plan['description'])) ?>
                    </div>
                </div>
            </div>
            
            <!-- Notes and Documents Tabs -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button id="notes-tab" class="py-4 px-6 text-center border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                            <i class="fas fa-sticky-note mr-2"></i> Notes
                        </button>
                        <button id="documents-tab" class="py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-file-alt mr-2"></i> Documents
                        </button>
                    </nav>
                </div>
                
                <!-- Notes Content -->
                <div id="notes-content" class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-blue-800">Notes</h2>
                        <button onclick="openNoteModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Note
                        </button>
                    </div>
                    
                    <?php if($notes->num_rows > 0): ?>
                        <div class="space-y-4">
                            <?php while($note = $notes->fetch_assoc()): ?>
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-medium text-blue-800"><?= htmlspecialchars($note['author']) ?></p>
                                        <p class="text-xs text-gray-500"><?= date('m/d/Y H:i', strtotime($note['created_at'])) ?></p>
                                    </div>
                                </div>
                                <p class="text-gray-700"><?= nl2br(htmlspecialchars($note['note'])) ?></p>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 py-4">No notes added yet</p>
                    <?php endif; ?>
                </div>
                
                <!-- Documents Content -->
                <div id="documents-content" class="p-6 hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-blue-800">Documents</h2>
                        <button onclick="openDocumentModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Document
                        </button>
                    </div>
                    
                    <?php if($documents->num_rows > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php while($doc = $documents->fetch_assoc()): 
                                $icon = [
                                    'pdf' => 'fa-file-pdf',
                                    'jpg' => 'fa-file-image',
                                    'jpeg' => 'fa-file-image',
                                    'png' => 'fa-file-image'
                                ][$doc['file_type']] ?? 'fa-file';
                            ?>
                            <div class="border rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex items-center mb-2">
                                    <i class="fas <?= $icon ?> text-blue-600 text-xl mr-3"></i>
                                    <div>
                                        <h3 class="font-medium text-blue-800 truncate"><?= htmlspecialchars($doc['file_name']) ?></h3>
                                        <p class="text-xs text-gray-500">Uploaded by <?= htmlspecialchars($doc['uploaded_by']) ?> on <?= date('m/d/Y', strtotime($doc['uploaded_at'])) ?></p>
                                    </div>
                                </div>
                                <div class="flex space-x-2 mt-3">
                                    <a href="<?= $doc['file_path'] ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                    <a href="<?= $doc['file_path'] ?>" download class="text-green-600 hover:text-green-800 text-sm">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                    <a href="delete_treatment_document.php?doc_id=<?= $doc['id'] ?>&plan_id=<?= $treatment_plan_id ?>" 
                                       class="text-red-600 hover:text-red-800 text-sm"
                                       onclick="return confirm('Are you sure you want to delete this document?')">
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </a>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 py-4">No documents uploaded yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Note Modal -->
    <div id="note-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-blue-800 mb-4">Add New Note</h3>
            <form id="note-form" action="add_treatment_note.php" method="POST">
                <input type="hidden" name="treatment_plan_id" value="<?= $treatment_plan_id ?>">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="note">Note *</label>
                    <textarea name="note" id="note" rows="4" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeNoteModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition">
                        Save Note
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Document Modal -->
    <div id="document-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-blue-800 mb-4">Upload Document</h3>
            <form id="document-form" action="add_treatment_document.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="treatment_plan_id" value="<?= $treatment_plan_id ?>">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="document">Document *</label>
                    <input type="file" name="document" id="document" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <p class="text-xs text-gray-500 mt-1">Allowed file types: PDF, JPG, PNG (Max 5MB)</p>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDocumentModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Tab switching
        document.getElementById('notes-tab').addEventListener('click', function() {
            this.classList.add('border-blue-500', 'text-blue-600');
            this.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            document.getElementById('documents-tab').classList.remove('border-blue-500', 'text-blue-600');
            document.getElementById('documents-tab').classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            document.getElementById('notes-content').classList.remove('hidden');
            document.getElementById('documents-content').classList.add('hidden');
        });
        
        document.getElementById('documents-tab').addEventListener('click', function() {
            this.classList.add('border-blue-500', 'text-blue-600');
            this.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            document.getElementById('notes-tab').classList.remove('border-blue-500', 'text-blue-600');
            document.getElementById('notes-tab').classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            document.getElementById('documents-content').classList.remove('hidden');
            document.getElementById('notes-content').classList.add('hidden');
        });
        
        // Modal functions
        function openNoteModal() {
            document.getElementById('note-modal').classList.remove('hidden');
        }
        
        function closeNoteModal() {
            document.getElementById('note-modal').classList.add('hidden');
        }
        
        function openDocumentModal() {
            document.getElementById('document-modal').classList.remove('hidden');
        }
        
        function closeDocumentModal() {
            document.getElementById('document-modal').classList.add('hidden');
        }
        
        // Handle form submissions
        document.getElementById('note-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Error adding note');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
        
        document.getElementById('document-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Error uploading document');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>