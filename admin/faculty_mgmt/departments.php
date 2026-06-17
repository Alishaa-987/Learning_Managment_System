<?php
require_once '../config.php';
requireAdmin();

$pdo = getDBConnection();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $dept_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM departments WHERE dept_id = ?");
        $stmt->execute([$dept_id]);
        $success = 'Department deleted successfully!';
    } catch (PDOException $e) {
        $error = 'Failed to delete department! It may be referenced by other records.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dept_name = sanitizeInput($_POST['dept_name'] ?? '');
    $focal_person_id = !empty($_POST['focal_person_id']) ? (int)$_POST['focal_person_id'] : null;
    $dept_id = isset($_POST['dept_id']) ? (int)$_POST['dept_id'] : null;

    if (empty($dept_name)) {
        $error = 'Department name is required!';
    } else {
        try {
            if ($dept_id) {
                $stmt = $pdo->prepare("UPDATE departments SET dept_name = ?, focal_person_id = ? WHERE dept_id = ?");
                $stmt->execute([$dept_name, $focal_person_id, $dept_id]);
                $success = 'Department updated successfully!';
            } else {
                $stmt = $pdo->prepare("SELECT dept_id FROM departments WHERE dept_name = ?");
                $stmt->execute([$dept_name]);
                if ($stmt->fetch()) {
                    $error = 'Department name already exists!';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO departments (dept_name, focal_person_id) VALUES (?, ?)");
                    $stmt->execute([$dept_name, $focal_person_id]);
                    $success = 'Department added successfully!';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$stmt = $pdo->query("
    SELECT d.*, f.name as focal_person_name
    FROM departments d
    LEFT JOIN faculty f ON d.focal_person_id = f.faculty_id
    ORDER BY d.dept_name
");
$departments = $stmt->fetchAll();

$stmt = $pdo->query("SELECT faculty_id, name FROM faculty ORDER BY name");
$faculty = $stmt->fetchAll();

$edit_dept = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE dept_id = ?");
    $stmt->execute([$edit_id]);
    $edit_dept = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Departments - Admin</title>
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
        .departments-table {
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
        .btn-edit {
            background: #6c4040;
            color: #fff;
        }
        .btn-delete {
            background: #c33;
            color: #fff;
        }
        .form-container {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(108, 64, 64, 0.1);
            margin-bottom: 2rem;
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
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #6c4040;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Source Sans Pro', sans-serif;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .alert-success {
            background: #efe;
            color: #3c3;
        }
        .alert-error {
            background: #fee;
            color: #c33;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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
            <li><a href="departments.php">Departments</a></li>
            <li><a href="faculty.php">Faculty</a></li>
            <li><a href="add_student.php">Add Student</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 style="margin: 0;">Department Management</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Manage university departments and their focal persons</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h2 style="font-family: 'Cinzel', serif; color: #6c4040; margin-bottom: 1.5rem;">
                <?php echo $edit_dept ? 'Edit Department' : 'Add New Department'; ?>
            </h2>

            <form method="POST">
                <?php if ($edit_dept): ?>
                    <input type="hidden" name="dept_id" value="<?php echo $edit_dept['dept_id']; ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label>Department Name *</label>
                        <input type="text" name="dept_name" required
                               value="<?php echo htmlspecialchars($edit_dept['dept_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Focal Person</label>
                        <select name="focal_person_id">
                            <option value="">Select Focal Person</option>
                            <?php foreach ($faculty as $member): ?>
                                <option value="<?php echo $member['stu_id']; ?>"
                                    <?php echo (isset($edit_dept['focal_person_id']) && $edit_dept['focal_person_id'] == $member['stu_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($member['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <?php echo $edit_dept ? 'Update Department' : 'Add Department'; ?>
                    </button>
                    <?php if ($edit_dept): ?>
                        <a href="departments.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="departments-table">
            <table>
                <thead>
                    <tr>
                        <th>Dept ID</th>
                        <th>Department Name</th>
                        <th>Focal Person</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($departments)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem;">No departments found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($departments as $dept): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dept['dept_id']); ?></td>
                                <td><?php echo htmlspecialchars($dept['dept_name']); ?></td>
                                <td><?php echo htmlspecialchars($dept['focal_person_name'] ?? 'Not assigned'); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?php echo $dept['dept_id']; ?>" class="btn-small btn-edit">Edit</a>
                                        <a href="?delete=<?php echo $dept['dept_id']; ?>" class="btn-small btn-delete"
                                           onclick="return confirm('Are you sure you want to delete this department?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>