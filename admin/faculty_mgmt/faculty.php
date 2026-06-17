<?php
require_once '../config.php';
requireAdmin();
$pdo = getDBConnection();

function generateFacultyRegNo() {
    global $pdo;

    $stmt = $pdo->prepare("SELECT ag_no FROM students WHERE role = 'faculty' AND ag_no LIKE 'FACULTY%' ORDER BY CAST(SUBSTRING(ag_no, 8) AS UNSIGNED) DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result) {
        $last_num = (int)substr($result['ag_no'], 7);
        $new_num = $last_num + 1;
    } else {
        $new_num = 1;
    }

    return 'FACULTY' . str_pad($new_num, 3, '0', STR_PAD_LEFT);
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stu_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE stu_id = ? AND role = 'faculty'");
        $stmt->execute([$stu_id]);
        $success = 'Faculty member deleted successfully!';
    } catch (PDOException $e) {
        $error = 'Failed to delete faculty member!';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $father_name = sanitizeInput($_POST['father_name'] ?? '');
    $cnic = sanitizeInput($_POST['cnic'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $department = sanitizeInput($_POST['department'] ?? '');
    $is_focal_person = isset($_POST['is_focal_person']) ? 1 : 0;
    $stu_id = isset($_POST['stu_id']) ? (int)$_POST['stu_id'] : null;

    if (!$stu_id) {
        $ag_no = generateFacultyRegNo();
    } else {
        $existing_stmt = $pdo->prepare("SELECT ag_no FROM students WHERE stu_id = ?");
        $existing_stmt->execute([$stu_id]);
        $existing = $existing_stmt->fetch();
        $ag_no = $existing['ag_no'];
    }

    if (empty($name) || empty($cnic) || empty($ag_no) || empty($email) || empty($phone)) {
        $error = 'Name, CNIC, Registration Number, email, and phone are required!';
    } elseif (!validateCNIC($cnic)) {
        $error = 'Invalid CNIC format! Format: 12345-1234567-1';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email format!';
    } elseif (!validatePhone($phone)) {
        $error = 'Invalid phone number! Format: 03001234567';
    } else {
        try {
            if ($stu_id) {
                $stmt = $pdo->prepare("UPDATE students SET name = ?, father_name = ?, cnic = ?, ag_no = ?, email = ?, phone_no = ?, department = ?, is_focal_person = ? WHERE stu_id = ? AND role = 'faculty'");
                $stmt->execute([$name, $father_name, $cnic, $ag_no, $email, $phone, $department, $is_focal_person, $stu_id]);
                $success = 'Faculty member updated successfully!';
            } else {
                $stmt = $pdo->prepare("SELECT stu_id FROM students WHERE email = ? OR cnic = ? OR ag_no = ?");
                $stmt->execute([$email, $cnic, $ag_no]);
                if ($stmt->fetch()) {
                    $error = 'Email, CNIC, or Registration Number already registered!';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO students (name, father_name, cnic, ag_no, email, phone_no, department, password, role, is_focal_person) VALUES (?, ?, ?, ?, ?, ?, ?, '', 'faculty', ?)");
                    $stmt->execute([$name, $father_name, $cnic, $ag_no, $email, $phone, $department, $is_focal_person]);
                    $success = 'Faculty member added successfully!';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$search = $_GET['search'] ?? '';
$department_filter = $_GET['department'] ?? '';

$where_conditions = ["role = 'faculty'"];
$params = [];
if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ? OR phone_no LIKE ? OR ag_no LIKE ? OR cnic LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}
if (!empty($department_filter)) {
    $where_conditions[] = "department = ?";
    $params[] = $department_filter;
}
$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

$query = "SELECT * FROM students $where_clause ORDER BY name";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$faculty_members = $stmt->fetchAll();

// Get unique departments from students
$dept_stmt = $pdo->query("SELECT DISTINCT department FROM students WHERE department IS NOT NULL AND department != '' ORDER BY department");
$departments = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);

$edit_faculty = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM students WHERE stu_id = ? AND role = 'faculty'");
    $stmt->execute([$edit_id]);
    $edit_faculty = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../style.css">
<title>Faculty Management - Admin</title>
<style>
.dashboard-container {
    max-width: 1400px;
    margin: 3rem auto;
    padding: 2rem;
}
.dashboard-header {
    background: linear-gradient(135deg, #6c4040 0%, #8b5555 100%);
    color: #fff;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
}
.faculty-table {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(108, 64, 64, 0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    background: #6c4040;
    color: #fff;
    padding: 1rem;
    text-align: left;
}
td {
    padding: 1rem;
    border-bottom: 1px solid #eee;
}
tr:hover {
    background: #f9f9f9;
}
.action-buttons {
    display: flex;
    gap: 0.5rem;
}
.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    border-radius: 5px;
    text-decoration: none;
    display: inline-block;
}
.btn-edit { background: #6c4040; color: #fff; }
.btn-delete { background: #c33; color: #fff; }
.form-container {
    background: #fff;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(108, 64, 64, 0.1);
    margin-bottom: 2rem;
}
.form-group { margin-bottom: 1.5rem; }
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #6c4040;
    font-weight: 600;
}
.form-group input, .form-group select {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid #6c4040;
    border-radius: 8px;
    font-size: 1rem;
    font-family: 'Source Sans Pro', sans-serif;
}
.filters {
    background: #fff;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(108, 64, 64, 0.1);
    margin-bottom: 2rem;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}
.filters input, .filters select {
    padding: 0.8rem;
    border: 2px solid #6c4040;
    border-radius: 8px;
    font-size: 1rem;
}
.filters input { flex: 1; min-width: 200px; }
.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}
.alert-success { background: #efe; color: #3c3; }
.alert-error { background: #fee; color: #c33; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="navbar">
    <img src="../ChatGPT Image Oct 1, 2025, 10_03_37 AM.png" class="logo" alt="University Logo">
    <ul>
        <li><a href="../index.html">Home</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="departments.php">Departments</a></li>
        <li><a href="faculty.php">Faculty</a></li>
        <li><a href="add_student.php">Add Student</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 style="margin:0;">Faculty Management</h1>
        <p style="margin:0.5rem 0 0 0; opacity:0.9;">Manage university faculty members</p>
    </div>

    <?php if(isset($success)): ?><div class="alert alert-success"><?=htmlspecialchars($success)?></div><?php endif; ?>
    <?php if(isset($error)): ?><div class="alert alert-error"><?=htmlspecialchars($error)?></div><?php endif; ?>

    <div class="form-container">
        <h2 style="font-family:'Cinzel', serif; color:#6c4040; margin-bottom:1.5rem;"><?= $edit_faculty ? 'Edit Faculty Member' : 'Add New Faculty Member'; ?></h2>
        <form method="POST">
            <?php if($edit_faculty): ?><input type="hidden" name="stu_id" value="<?=$edit_faculty['stu_id']?>"><?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" required value="<?=htmlspecialchars($edit_faculty['name'] ?? '')?>">
                </div>
                <div class="form-group">
                    <label>Father Name</label>
                    <input type="text" name="father_name" value="<?=htmlspecialchars($edit_faculty['father_name'] ?? '')?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>CNIC *</label>
                    <input type="text" name="cnic" required pattern="\d{5}-\d{7}-\d{1}" placeholder="12345-1234567-1" value="<?=htmlspecialchars($edit_faculty['cnic'] ?? '')?>">
                </div>
                <div class="form-group">
                    <label>Registration Number</label>
                    <input type="text" readonly value="<?=htmlspecialchars($edit_faculty['ag_no'] ?? generateFacultyRegNo())?>">
                    <small style="color: #666;">Auto-generated for new faculty members</small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required value="<?=htmlspecialchars($edit_faculty['email'] ?? '')?>">
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="text" name="phone" required pattern="0[0-9]{10}" value="<?=htmlspecialchars($edit_faculty['phone_no'] ?? '')?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" value="<?=htmlspecialchars($edit_faculty['department'] ?? '')?>">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_focal_person" value="1" <?= (isset($edit_faculty['is_focal_person']) && $edit_faculty['is_focal_person']) ? 'checked' : ''?>>
                        Is Focal Person
                    </label>
                </div>
            </div>
            <div class="form-row">
               
              
                </div>
            </div>
            <div style="display:flex; gap:1rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;"><?= $edit_faculty?'Update Faculty':'Add Faculty';?></button>
                <?php if($edit_faculty): ?><a href="faculty.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancel</a><?php endif;?>
            </div>
        </form>
    </div>

    <div class="filters">
        <form method="GET" style="display:flex; gap:1rem; width:100%; flex-wrap:wrap;">
            <input type="text" name="search" placeholder="Search by name, email, phone, CNIC, or registration number..." value="<?=htmlspecialchars($search)?>" style="flex:1; min-width:200px;">
            <select name="department">
                <option value="">All Departments</option>
                <?php foreach($departments as $dept): ?>
                    <option value="<?=$dept?>" <?= $_GET['department']??''==$dept?'selected':''?>><?=htmlspecialchars($dept)?></option>
                <?php endforeach;?>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="faculty.php" class="btn btn-secondary">Clear</a>
        </form>
    </div>

    <div class="faculty-table">
        <table>
            <thead>
                <tr>
                    <th>Student ID</th><th>Name</th><th>CNIC</th><th>Registration Number</th><th>Department</th><th>Email</th><th>Phone</th><th>Focal Person</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($faculty_members)): ?>
                    <tr><td colspan="9" style="text-align:center; padding:2rem;">No faculty members found.</td></tr>
                <?php else: ?>
                    <?php foreach($faculty_members as $member): ?>
                        <tr>
                            <td><?=htmlspecialchars($member['stu_id'])?></td>
                            <td><?=htmlspecialchars($member['name'])?></td>
                            <td><?=htmlspecialchars($member['cnic'])?></td>
                            <td><?=htmlspecialchars($member['ag_no'])?></td>
                            <td><?=htmlspecialchars($member['department'] ?? 'Not assigned')?></td>
                            <td><?=htmlspecialchars($member['email'])?></td>
                            <td><?=htmlspecialchars($member['phone_no'])?></td>
                            <td><?= $member['is_focal_person'] ? 'Yes' : 'No' ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?=$member['stu_id']?>" class="btn-small btn-edit">Edit</a>
                                    <a href="?delete=<?=$member['stu_id']?>" class="btn-small btn-delete" onclick="return confirm('Are you sure?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach;?>
                <?php endif;?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
