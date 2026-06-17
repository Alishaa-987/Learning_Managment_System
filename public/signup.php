<?php
/**
 * Student Signup Page
 * Handles student registration with form validation and database insertion
 */

require_once 'config.php';
startSession();

$error = '';
$success = '';

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: user/dashboard.php');
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
    $class = sanitizeInput($_POST['class'] ?? '');
    $section = sanitizeInput($_POST['section'] ?? '');
    $degree = sanitizeInput($_POST['degree'] ?? '');
    $phone_no = sanitizeInput($_POST['phone_no'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    // Role removed as per user request
    
    // Validation
    if (empty($name) || empty($father_name) || empty($dob) || empty($cnic) || 
        empty($gender) || empty($email) || empty($adm_date) || empty($ag_no) || 
        empty($department) || empty($degree) || empty($phone_no) || empty($address) || 
        empty($password)) {
        $error = 'All fields are required!';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email format!';
    } elseif (!validateCNIC($cnic)) {
        $error = 'Invalid CNIC format! Format: 12345-1234567-1';
    } elseif (!validatePhone($phone_no)) {
        $error = 'Invalid phone number! Format: 03001234567';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT email FROM students WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered!';
            } else {
                // Check if CNIC already exists
                $stmt = $pdo->prepare("SELECT cnic FROM students WHERE cnic = ?");
                $stmt->execute([$cnic]);
                if ($stmt->fetch()) {
                    $error = 'CNIC already registered!';
                } else {
                    // Check if AG No already exists
                    $stmt = $pdo->prepare("SELECT ag_no FROM students WHERE ag_no = ?");
                    $stmt->execute([$ag_no]);
                    if ($stmt->fetch()) {
                        $error = 'AG No already registered!';
                    } else {
                        // Handle profile picture upload
                        $profile_picture = null;
                        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                            $file = $_FILES['profile_picture'];
                            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            
                            if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
                                $error = 'Invalid file type! Allowed: jpg, jpeg, png, gif';
                            } elseif ($file['size'] > MAX_FILE_SIZE) {
                                $error = 'File size too large! Maximum 5MB allowed.';
                            } else {
                                // Create uploads directory if it doesn't exist
                                if (!file_exists(UPLOAD_DIR)) {
                                    mkdir(UPLOAD_DIR, 0777, true);
                                }
                                
                                $new_filename = uniqid('profile_', true) . '.' . $file_ext;
                                $upload_path = UPLOAD_DIR . $new_filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                                    $profile_picture = $new_filename;
                                } else {
                                    $error = 'Failed to upload profile picture!';
                                }
                            }
                        }
                        
                        if (empty($error)) {
                            // Hash password
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            
                            // Insert student into database
                             $stmt = $pdo->prepare("INSERT INTO students (name, father_name, dob, cnic, gender, email, adm_date, ag_no, department, class, section, degree, phone_no, address, password, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                             
                             if ($stmt->execute([$name, $father_name, $dob, $cnic, $gender, $email, $adm_date, $ag_no, $department, $class, $section, $degree, $phone_no, $address, $hashed_password, $profile_picture])) {
                                // Auto login after successful registration
                                 $student_id = $pdo->lastInsertId();
                                 $_SESSION['user_id'] = $student_id;
                                 $_SESSION['user_name'] = $name;
                                 $_SESSION['user_email'] = $email;

                                header('Location: user/dashboard.php');
                                exit();
                            } else {
                                $error = 'Registration failed! Please try again.';
                            }
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
    <link rel="stylesheet" href="style.css">
    <title>Student Signup - University</title>
    <style>
        .signup-container {
            max-width: 800px;
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
            border: 1px solid #fcc;
        }
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
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
        <img src="./ChatGPT Image Oct 1, 2025, 10_03_37 AM.png" class="logo" alt="University Logo">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="program.html">Programs</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php">Signup</a></li>
        </ul>
    </div>

    <div class="signup-container">
        <h1 style="font-family: 'Cinzel', serif; color: #6c4040; text-align: center; margin-bottom: 2rem;">Student Registration</h1>
        
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

            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Admission Date *</label>
                    <input type="date" name="adm_date" required value="<?php echo htmlspecialchars($_POST['adm_date'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>AG No / Registration No *</label>
                    <input type="text" name="ag_no" required value="<?php echo htmlspecialchars($_POST['ag_no'] ?? ''); ?>">
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
                    <label>Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/jpeg,image/jpg,image/png,image/gif">
                </div>
            </div>

            <div class="form-group">
                <label>Address *</label>
                <textarea name="address" rows="3" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password * (Min 6 characters)</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Register</button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem;">
            Already have an account? <a href="login.php" style="color: #6c4040; font-weight: 600;">Login here</a>
        </p>
    </div>
</body>
</html>

