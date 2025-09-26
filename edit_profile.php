<?php
include 'includes/session_check.php';
require 'config.php';

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = $_SESSION['user_id'];
$csrf_token = $_SESSION['csrf_token'];

// Fetch user details with prepared statement
$sql = "SELECT id, username, email, course, phone_number, profile_photo FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle empty user case
if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: profile.php");
    exit();
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $error = "Security validation failed. Please try again.";
    } else {
        // Handle profile photo upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $upload_result = handleProfilePhotoUpload($_FILES['profile_photo'], $user_id, $conn);
            if ($upload_result['success']) {
                $message = $upload_result['message'];
                $user['profile_photo'] = $upload_result['file_path'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        // Handle other profile updates if no error occurred with photo upload
        if (empty($error)) {
            $update_result = handleProfileUpdate($_POST, $user_id, $conn);
            if ($update_result['success']) {
                $message = empty($message) ? $update_result['message'] : $message . " " . $update_result['message'];
                // Update local user data
                $user['username'] = $update_result['username'];
                $user['course'] = $update_result['course'];
                $user['phone_number'] = $update_result['phone_number'];
            } else {
                $error = $update_result['message'];
            }
        }
    }
}

/**
 * Handle profile photo upload with validation
 */
function handleProfilePhotoUpload($file, $user_id, $conn) {
    $target_dir = "uploads/profile_photos/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            return ['success' => false, 'message' => 'Failed to create upload directory.'];
        }
    }
    
    // Validate file
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }
    
    // Check file size (max 2MB)
    if ($file["size"] > 2000000) {
        return ['success' => false, 'message' => 'File size exceeds 2MB limit.'];
    }
    
    // Allow certain file formats
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
    }
    
    // Generate unique filename
    $new_filename = "user_" . $user_id . "_" . time() . "." . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Delete old profile photo if it exists
        $old_photo_sql = "SELECT profile_photo FROM users WHERE id = ?";
        $old_stmt = $conn->prepare($old_photo_sql);
        $old_stmt->bind_param('i', $user_id);
        $old_stmt->execute();
        $old_result = $old_stmt->get_result();
        $old_user = $old_result->fetch_assoc();
        $old_stmt->close();
        
        if (!empty($old_user['profile_photo']) && file_exists($old_user['profile_photo'])) {
            unlink($old_user['profile_photo']);
        }
        
        // Update database
        $update_sql = "UPDATE users SET profile_photo = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('si', $target_file, $user_id);
        
        if ($update_stmt->execute()) {
            $update_stmt->close();
            return ['success' => true, 'message' => 'Profile photo updated successfully.', 'file_path' => $target_file];
        } else {
            // Delete the uploaded file if database update fails
            unlink($target_file);
            return ['success' => false, 'message' => 'Error updating database.'];
        }
    } else {
        return ['success' => false, 'message' => 'Error uploading file.'];
    }
}

/**
 * Handle profile information update
 */
function handleProfileUpdate($post_data, $user_id, $conn) {
    // Sanitize inputs
    $username = trim($post_data['username'] ?? '');
    $course = trim($post_data['course'] ?? '');
    $phone_number = trim($post_data['phone_number'] ?? '');
    
    // Validate inputs
    if (empty($username)) {
        return ['success' => false, 'message' => 'Username is required.'];
    }
    
    if (empty($course)) {
        return ['success' => false, 'message' => 'Course is required.'];
    }
    
    if (empty($phone_number)) {
        return ['success' => false, 'message' => 'Phone number is required.'];
    }
    
    // Validate username length
    if (strlen($username) < 3 || strlen($username) > 50) {
        return ['success' => false, 'message' => 'Username must be between 3 and 50 characters.'];
    }
    
    // Validate phone number format
    if (!preg_match('/^\+?[0-9]{10,15}$/', $phone_number)) {
        return ['success' => false, 'message' => 'Please enter a valid phone number.'];
    }
    
    // Check if username already exists (excluding current user)
    $check_sql = "SELECT id FROM users WHERE username = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('si', $username, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $check_stmt->close();
        return ['success' => false, 'message' => 'Username already taken.'];
    }
    $check_stmt->close();
    
    // Update database
    $update_sql = "UPDATE users SET username = ?, course = ?, phone_number = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('sssi', $username, $course, $phone_number, $user_id);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();
        return [
            'success' => true, 
            'message' => 'Profile updated successfully.',
            'username' => $username,
            'course' => $course,
            'phone_number' => $phone_number
        ];
    } else {
        $update_stmt->close();
        return ['success' => false, 'message' => 'Error updating profile: ' . $conn->error];
    }
}
?>
<?php
// When a user updates their profile
if (isset($_POST['update_profile'])) {
    // Update profile in database...
    
    // Track profile update
    track_activity($_SESSION['user_id'], ACTIVITY_PROFILE_UPDATE, "Updated profile information");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Dasaplus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/edit_profile.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="edit-profile-container">
        <a href="profile.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>

        <div class="edit-profile-header">
            <h1>Edit Your Profile</h1>
            <p>Update your personal information and profile photo</p>
        </div>

        <!-- Display Messages -->
        <?php if (!empty($message)) : ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)) : ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form class="edit-profile-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <!-- Photo Section -->
            <div class="photo-section">
                <div class="profile-photo-container">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" 
                             alt="Profile Photo" class="profile-photo"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="profile-photo-placeholder" style="<?php echo !empty($user['profile_photo']) ? 'display:none;' : ''; ?>">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    <?php else: ?>
                        <div class="profile-photo-placeholder">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="photo-upload">
                    <label for="profile_photo" class="photo-upload-label">
                        <i class="fas fa-camera"></i> Choose New Photo
                    </label>
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                    <small>Max size: 2MB (JPG, PNG, GIF, JPEG)</small>
                </div>
            </div>

            <!-- Details Section -->
            <div class="details-section">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($user['username']); ?>" 
                           required minlength="3" maxlength="50">
                    <small class="form-help">3-50 characters</small>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    <small class="form-help">Contact admin to change email</small>
                </div>

                <div class="form-group">
                    <label for="course">Course *</label>
                    <input type="text" id="course" name="course" 
                           value="<?php echo htmlspecialchars($user['course']); ?>" 
                           required maxlength="100">
                </div>

                <div class="form-group">
                    <label for="phone_number">Phone Number *</label>
                    <input type="tel" id="phone_number" name="phone_number" 
                           value="<?php echo htmlspecialchars($user['phone_number']); ?>" 
                           required pattern="\+?[0-9]{10,15}" 
                           title="10-15 digit phone number with optional country code">
                    <small class="form-help">Format: +254712345678 or 0712345678</small>
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="form-buttons">
                <button type="submit" class="save-btn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="profile.php" class="cancel-btn">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        // Preview profile photo before upload
        document.getElementById('profile_photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.querySelector('.profile-photo-container');
            const placeholder = preview.querySelector('.profile-photo-placeholder');
            const existingImg = preview.querySelector('.profile-photo');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Remove existing image if any
                    if (existingImg) {
                        existingImg.remove();
                    }
                    
                    // Hide placeholder
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    
                    // Create and show new image
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = "Profile Preview";
                    img.className = "profile-photo";
                    img.onerror = function() {
                        this.remove();
                        if (placeholder) {
                            placeholder.style.display = 'flex';
                        }
                    };
                    
                    preview.insertBefore(img, preview.firstChild);
                };
                reader.readAsDataURL(file);
            }
        });

        // File size validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('profile_photo');
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
                if (fileSize > 2) {
                    e.preventDefault();
                    alert('File size exceeds 2MB. Please choose a smaller file.');
                    fileInput.value = '';
                }
            }
        });

        // Input validation
        document.getElementById('username').addEventListener('input', function(e) {
            if (this.value.length < 3) {
                this.setCustomValidity('Username must be at least 3 characters long.');
            } else {
                this.setCustomValidity('');
            }
        });

        document.getElementById('phone_number').addEventListener('input', function(e) {
            const phonePattern = /^\+?[0-9]{10,15}$/;
            if (!phonePattern.test(this.value)) {
                this.setCustomValidity('Please enter a valid phone number (10-15 digits with optional + prefix).');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>