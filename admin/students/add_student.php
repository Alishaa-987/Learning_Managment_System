<?php

require_once '../config.php';
requireLogin();

$pdo = getDBConnection();

$error = '';

$success = '';


function generateStudentRegNo() {
    global $pdo;
    $current_year = date('Y');
    
    // 1. Find the highest existing number to start with a good guess
    $stmt = $pdo->prepare("SELECT ag_no FROM students WHERE ag_no LIKE ? ORDER BY LENGTH(ag_no) DESC, ag_no DESC LIMIT 1");
    $stmt->execute([$current_year . '-AG-%']);
    $result = $stmt->fetch();

    $next_num = 10001; 
    if ($result) {
        // Try to parse the number
        if (preg_match('/-(\d+)$/', $result['ag_no'], $matches)) {
            $next_num = (int)$matches[1] + 1;
        }
    }

    // 2. Loop to ensure uniqueness (handling gaps or non-sequential inserts)
    while (true) {
        $candidate = $current_year . '-AG-' . str_pad($next_num, 5, '0', STR_PAD_LEFT);
        
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM students WHERE ag_no = ?");
        $stmtCheck->execute([$candidate]);
        if ($stmtCheck->fetchColumn() == 0) {
            return $candidate;
        }
        $next_num++;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../debug_helper.php';
    logDebug("POST received: " . print_r($_POST, true));

    $name = sanitizeInput($_POST['name'] ?? '');

    $father_name = sanitizeInput($_POST['father_name'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $cnic = sanitizeInput($_POST['cnic'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $email = sanitizeInput($_POST['email'] ?? '');
    $adm_date = $_POST['adm_date'] ?? '';
    // Use manually entered AG No, or fallback to auto-generated if empty (though it should be required)
    $ag_no = sanitizeInput($_POST['ag_no'] ?? generateStudentRegNo());
    $department = sanitizeInput($_POST['department'] ?? '');

    $class = sanitizeInput($_POST['class'] ?? '');
    $section = sanitizeInput($_POST['section'] ?? '');
    $degree = sanitizeInput($_POST['degree'] ?? '');
    $phone_no = sanitizeInput($_POST['phone_no'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $stu_status = $_POST['stu_status'] ?? 'Active';
    $password = $_POST['password'] ?? '';
    
    if (empty($name) || empty($father_name) || empty($dob) || empty($cnic) ||
        empty($gender) || empty($email) || empty($adm_date) ||
        empty($department) || empty($degree) || empty($phone_no) || empty($address) ||
        empty($password)) {
        $error = 'All required fields must be filled!';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email format!';
    } elseif (!validateCNIC($cnic)) {
        $error = 'Invalid CNIC format! Format: 12345-1234567-1';
    } elseif (!validatePhone($phone_no)) {
        $error = 'Invalid phone number! Format: 03001234567';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } else {
        try {
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("SELECT email FROM students WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Error: The email '$email' is already registered!";
            } else {
                $stmt = $pdo->prepare("SELECT cnic FROM students WHERE cnic = ?");
                $stmt->execute([$cnic]);
                if ($stmt->fetch()) {
                    $error = "Error: The CNIC '$cnic' is already registered!";
                } else {
                    // Check AG No
                    $stmt = $pdo->prepare("SELECT ag_no FROM students WHERE ag_no = ?");
                    $stmt->execute([$ag_no]);
                    if ($stmt->fetch()) {
                        $error = "Error: The Registration Number '$ag_no' is already registered!";
                    } else {
                        $profile_picture = null;

                    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['profile_picture'];
                        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        
                        if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
                            $error = 'Invalid file type! Allowed: jpg, jpeg, png, gif';
                        } elseif ($file['size'] > MAX_FILE_SIZE) {
                            $error = 'File size too large! Maximum 5MB allowed.';
                        } else {
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
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $role = $_POST['role'] ?? 'student';
                        $is_focal_person = isset($_POST['is_focal_person']) ? 1 : 0;
                        
                        $stmt = $pdo->prepare("INSERT INTO students (name, father_name, dob, cnic, gender, email, adm_date, ag_no, department, class, section, degree, phone_no, address, stu_status, password, profile_picture, role, is_focal_person) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        
                        if ($stmt->execute([$name, $father_name, $dob, $cnic, $gender, $email, $adm_date, $ag_no, $department, $class, $section, $degree, $phone_no, $address, $stu_status, $hashed_password, $profile_picture, $role, $is_focal_person])) {
                            $success = "User added successfully! Role: $role";
                            if ($is_focal_person) $success .= " (Focal Person)";
                            $_POST = [];
                        } else {
                            $error = 'Failed to add user! Please try again.';
                        }
                    }
                }
                }
            }
        } catch (PDOException $e) {

            if ($e->getCode() == 23000) { // Integrity constraint violation
                if (strpos($e->getMessage(), 'ag_no') !== false) {
                     $error = 'Error: Registration Number collision (Constraint violation on ag_no). The system has generated a NEW unique number below. Please submit again.';
                     unset($_POST['ag_no']); // Force regeneration in the form
                } elseif (strpos($e->getMessage(), 'email') !== false) {

                     $error = 'Error: Email already exists (Constraint violation on email).';
                } elseif (strpos($e->getMessage(), 'cnic') !== false) {
                     $error = 'Error: CNIC already exists (Constraint violation on cnic).';
                } else {
                     $error = 'Database error: Duplicate entry found. Details: ' . $e->getMessage();
                }
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
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
    <title>Add Student - Admin</title>
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
            <li><a href="add_student.php">Add Student</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="form-container">
        <h1 style="font-family: 'Cinzel', serif; color: #6c4040; text-align: center; margin-bottom: 2rem;">Add New User</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <div class="form-row">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Father Name *</label>
                    <input type="text" name="father_name" required value="<?php echo htmlspecialchars($_POST['father_name'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="dob" required value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>CNIC * (Format: 12345-1234567-1)</label>
                    <input type="text" name="cnic" required pattern="\d{5}-\d{7}-\d{1}" value="<?php echo htmlspecialchars($_POST['cnic'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" autocomplete="off">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="role_select" onchange="toggleFocalPerson()">
                        <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="faculty" <?php echo (isset($_POST['role']) && $_POST['role'] === 'faculty') ? 'selected' : ''; ?>>Faculty</option>
                        <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class="form-group" id="focal_person_wrapper" style="display: none; align-items: center; justify-content: start; margin-top: 1.8rem;">

                    <label style="margin-bottom: 0; cursor: pointer; display: flex; align-items: center;">
                        <input type="checkbox" name="is_focal_person" id="is_focal_person" value="1" <?php echo (isset($_POST['is_focal_person'])) ? 'checked' : ''; ?> style="width: 20px; height: 20px; margin-right: 10px; margin-top: 0;">
                        <span style="font-weight: bold; color: #6c4040; margin-top: 2px;">Is Focal Person?</span>
                    </label>
                </div>

            </div>


            <div class="form-row">
                <div class="form-group">
                    <label>Admission Date *</label>
                    <input type="date" name="adm_date" required value="<?php echo htmlspecialchars($_POST['adm_date'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Registration Number *</label>
                    <input type="text" name="ag_no" required value="<?php echo htmlspecialchars($_POST['ag_no'] ?? generateStudentRegNo()); ?>">
                    <small style="color: #666;">You can manually edit this if needed (e.g. FAC-001).</small>
                </div>

            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Department *</label>
                    <input type="text" name="department" required value="<?php echo htmlspecialchars($_POST['department'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Degree *</label>
                    <input type="text" name="degree" required value="<?php echo htmlspecialchars($_POST['degree'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Class</label>
                    <input type="text" name="class" value="<?php echo htmlspecialchars($_POST['class'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Section</label>
                    <input type="text" name="section" value="<?php echo htmlspecialchars($_POST['section'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Phone Number * (Format: 03001234567)</label>
                    <input type="text" name="phone_no" required pattern="0[0-9]{10}" value="<?php echo htmlspecialchars($_POST['phone_no'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Status *</label>
                    <select name="stu_status" required>
                        <option value="Active" <?php echo (isset($_POST['stu_status']) && $_POST['stu_status'] === 'Active') ? 'selected' : 'selected'; ?>>Active</option>
                        <option value="Inactive" <?php echo (isset($_POST['stu_status']) && $_POST['stu_status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="Graduated" <?php echo (isset($_POST['stu_status']) && $_POST['stu_status'] === 'Graduated') ? 'selected' : ''; ?>>Graduated</option>
                        <option value="Suspended" <?php echo (isset($_POST['stu_status']) && $_POST['stu_status'] === 'Suspended') ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/jpeg,image/jpg,image/png,image/gif">
                </div>
                <div class="form-group">
                    <label>Password * (Min 6 characters)</label>
                    <input type="password" name="password" required minlength="6" autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label>Address *</label>
                <textarea name="address" rows="3" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Add User</button>
                <a href="dashboard.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function toggleFocalPerson() {
            var role = document.getElementById('role_select').value;
            var wrapper = document.getElementById('focal_person_wrapper');
            
            if (role === 'faculty' || role === 'admin') {
                wrapper.style.display = 'flex';
            } else {
                wrapper.style.display = 'none';
                document.getElementById('is_focal_person').checked = false;
            }
        }
        
        // Run on load
        window.onload = toggleFocalPerson;
    </script>


</body>
</html>

