<?php
/**
 * Edit Faculty Profile
 * Allows faculty to edit their own information
 */

require_once '../config.php';
requireLogin();

$error = '';
$success = '';

// Get faculty ID
$stu_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Faculty can only edit their own profile
if ($stu_id !== $_SESSION['user_id']) {
    header('Location: dashboard.php');
    exit();
}

if (!$stu_id) {
    header('Location: dashboard.php');
    exit();
}

$pdo = getDBConnection();

// Fetch faculty data
$stmt = $pdo->prepare("SELECT * FROM students WHERE stu_id = ?");
$stmt->execute([$stu_id]);
$faculty = $stmt->fetch();

if (!$faculty) {
    header('Location: dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate all inputs
    $name = sanitizeInput($_POST['name'] ?? '');
    $father_name = sanitizeInput($_POST['father_name'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $cnic = sanitizeInput($_POST['cnic'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $email = sanitizeInput($_POST['email'] ?? '');
    $adm_date = $_POST['adm_date'] ?? '';
    $ag_no = sanitizeInput($_POST['ag_no'] ?? '');
    $department = sanitizeInput($_POST['department'] ?? '');
    $degree = sanitizeInput($_POST['degree'] ?? '');
    $phone_no = sanitizeInput($_POST['phone_no'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($name) || empty($father_name) || empty($dob) || empty($cnic) ||
        empty($gender) || empty($email) || empty($adm_date) || empty($ag_no) ||
        empty($department) || empty($degree) || empty($phone_no) || empty($address)) {
        $error = 'All required fields must be filled!';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email format!';
    } elseif (!validateCNIC($cnic)) {
        $error = 'Invalid CNIC format! Format: 12345-1234567-1';
    } elseif (!validatePhone($phone_no)) {
        $error = 'Invalid phone number! Format: 03001234567';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } else {
        try {
            // Check if email already exists (excluding current faculty)
            $stmt = $pdo->prepare("SELECT email FROM students WHERE email = ? AND stu_id != ?");
            $stmt->execute([$email, $stu_id]);
            if ($stmt->fetch()) {
                $error = 'Email already registered to another user!';
            } else {
                // Check if CNIC already exists (excluding current faculty)
                $stmt = $pdo->prepare("SELECT cnic FROM students WHERE cnic = ? AND stu_id != ?");
                $stmt->execute([$cnic, $stu_id]);
                if ($stmt->fetch()) {
                    $error = 'CNIC already registered to another user!';
                } else {
                    // Check if AG No already exists (excluding current faculty)
                    $stmt = $pdo->prepare("SELECT ag_no FROM students WHERE ag_no = ? AND stu_id != ?");
                    $stmt->execute([$ag_no, $stu_id]);
                    if ($stmt->fetch()) {
                        $error = 'AG No already registered to another user!';
                    } else {
                        // Handle profile picture upload
                        $profile_picture = $faculty['profile_picture']; // Keep existing if not changed
                        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['profile_picture'];
                            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                            if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
                                $error = 'Invalid file type! Allowed: jpg, jpeg, png, gif';
                            } elseif ($file['size'] > MAX_FILE_SIZE) {
                                $error = 'File size too large! Maximum 5MB allowed.';
                            } else {
                                // Delete old profile picture if exists
                                if ($faculty['profile_picture']) {
                                    $old_file = '../' . UPLOAD_DIR . $faculty['profile_picture'];
                                    if (file_exists($old_file)) {
                                        unlink($old_file);
                                    }
                                }

                                // Create uploads directory if it doesn't exist
                                if (!file_exists('../' . UPLOAD_DIR)) {
                                    mkdir('../' . UPLOAD_DIR, 0777, true);
                                }

                                $new_filename = uniqid('profile_', true) . '.' . $file_ext;
                                $upload_path = '../' . UPLOAD_DIR . $new_filename;

                                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                                    $profile_picture = $new_filename;
                                } else {
                                    $error = 'Failed to upload profile picture!';
                                }
                            }
                        }

                        if (empty($error)) {
                            // Update faculty in database
                            if (!empty($password)) {
                                // Update with new password
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $stmt = $pdo->prepare("UPDATE students SET name = ?, father_name = ?, dob = ?, cnic = ?, gender = ?, email = ?, adm_date = ?, ag_no = ?, department = ?, degree = ?, phone_no = ?, address = ?, password = ?, profile_picture = ? WHERE stu_id = ?");
                                $stmt->execute([$name, $father_name, $dob, $cnic, $gender, $email, $adm_date, $ag_no, $department, $degree, $phone_no, $address, $hashed_password, $profile_picture, $stu_id]);
                            } else {
                                // Update without changing password
                                $stmt = $pdo->prepare("UPDATE students SET name = ?, father_name = ?, dob = ?, cnic = ?, gender = ?, email = ?, adm_date = ?, ag_no = ?, department = ?, degree = ?, phone_no = ?, address = ?, profile_picture = ? WHERE stu_id = ?");
                                $stmt->execute([$name, $father_name, $dob, $cnic, $gender, $email, $adm_date, $ag_no, $department, $degree, $phone_no, $address, $profile_picture, $stu_id]);
                            }

                            $success = 'Profile updated successfully!';
                            // Refresh faculty data
                            $stmt = $pdo->prepare("SELECT * FROM students WHERE stu_id = ?");
                            $stmt->execute([$stu_id]);
                            $faculty = $stmt->fetch();
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Edit Profile - Faculty</title>
    <style>
        .form-container {
            max-width: 900px;
            margin: 3rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(108, 64, 64, 0.2);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #6c4040;
            font-weight: 600;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #6c4040;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Source Sans Pro', sans-serif;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .alert-error {
            background: #fee;
            color: #c33;
        }
        .alert-success {
            background: #efe;
            color: #3c3;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="../ChatGPT Image Oct 1, 2025, 10_03_37 AM.png" class="logo" alt="University Logo">
        <ul>
            <li><a href="../index.html">Home</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="form-container">
        <h1 style="font-family: 'Cinzel', serif; color: #6c4040; text-align: center; margin-bottom: 2rem;">Edit Profile</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($faculty['name']); ?>">
                </div>
                <div class="form-group">
                    <label>Father Name *</label>
                    <input type="text" name="father_name" required value="<?php echo htmlspecialchars($faculty['father_name']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="dob" required value="<?php echo htmlspecialchars($faculty['dob']); ?>">
                </div>
                <div class="form-group">
                    <label>CNIC * (Format: 12345-1234567-1)</label>
                    <input type="text" name="cnic" required pattern="\d{5}-\d{7}-\d{1}" value="<?php echo htmlspecialchars($faculty['cnic']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" required>
                        <option value="Male" <?php echo $faculty['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $faculty['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo $faculty['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($faculty['email']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Joining Date *</label>
                    <input type="date" name="adm_date" required value="<?php echo htmlspecialchars($faculty['adm_date']); ?>">
                </div>
                <div class="form-group">
                    <label>Employee ID *</label>
                    <input type="text" name="ag_no" required value="<?php echo htmlspecialchars($faculty['ag_no']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Department *</label>
                    <input type="text" name="department" required value="<?php echo htmlspecialchars($faculty['department']); ?>">
                </div>
                <div class="form-group">
                    <label>Qualification *</label>
                    <input type="text" name="degree" required value="<?php echo htmlspecialchars($faculty['degree']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Phone Number * (Format: 03001234567)</label>
                    <input type="text" name="phone_no" required pattern="0[0-9]{10}" value="<?php echo htmlspecialchars($faculty['phone_no']); ?>">
                </div>
                <div class="form-group">
                    <label>Profile Picture</label>
                    <?php if ($faculty['profile_picture']): ?>
                        <p style="margin-bottom: 0.5rem; color: #666;">Current: <img src="../uploads/<?php echo htmlspecialchars($faculty['profile_picture']); ?>" alt="Profile" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"></p>
                    <?php endif; ?>
                    <input type="file" name="profile_picture" accept="image/jpeg,image/jpg,image/png,image/gif">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>New Password (Leave blank to keep current)</label>
                    <input type="password" name="password" minlength="6" placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <label>Address *</label>
                    <textarea name="address" rows="3" required><?php echo htmlspecialchars($faculty['address']); ?></textarea>
                </div>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Update Profile</button>
                <a href="dashboard.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>