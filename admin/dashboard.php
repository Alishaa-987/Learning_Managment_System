<?php
require_once '../config.php';
requireLogin();

$pdo = getDBConnection();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stu_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("SELECT profile_picture FROM students WHERE stu_id = ?");
        $stmt->execute([$stu_id]);
        $student = $stmt->fetch();

        if ($student && $student['profile_picture']) {
            $file_path = '../uploads/' . $student['profile_picture'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM students WHERE stu_id = ?");
        $stmt->execute([$stu_id]);
        $success = 'Student deleted successfully!';
    } catch (PDOException $e) {
        $error = 'Failed to delete student!';
    }
}

$search = $_GET['search'] ?? '';
$department_filter = $_GET['department'] ?? '';

$where_conditions = ["role = 'student'"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ? OR ag_no LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($department_filter)) {
    $where_conditions[] = "department = ?";
    $params[] = $department_filter;
}

$where_clause = implode(' AND ', $where_conditions);

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE $where_clause");
$count_stmt->execute($params);
$total_students = $count_stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT stu_id, name, email, ag_no, degree, department, stu_status FROM students WHERE $where_clause ORDER BY created_at DESC");
$stmt->execute($params);
$students = $stmt->fetchAll();

$dept_stmt = $pdo->query("SELECT DISTINCT department FROM students ORDER BY department");
$departments = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);

$dept_count_stmt = $pdo->query("SELECT COUNT(*) FROM departments");
$total_departments = $dept_count_stmt->fetchColumn();

$faculty_count_stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE role = 'faculty'");
$total_faculty = $faculty_count_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Admin Dashboard - University</title>
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
        .stats-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(108, 64, 64, 0.1);
            text-align: center;
        }
        .stats-card h3 {
            color: #6c4040;
            margin-bottom: 0.5rem;
        }
        .stats-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #6c4040;
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
        .filters input,
        .filters select {
            padding: 0.8rem;
            border: 2px solid #6c4040;
            border-radius: 8px;
            font-size: 1rem;
        }
        .filters input {
            flex: 1;
            min-width: 200px;
        }
        .students-table {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(108, 64, 64, 0.1);
            overflow: hidden;
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
            <li><a href="bookings.php">Manage Bookings</a></li>
            <li><a href="add_student.php">Add User</a></li>
            <li><a href="logout.php">Logout</a></li>


        </ul>
    </div>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 style="margin: 0;">Admin Dashboard</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <div class="stats-card">
                <h3>Total Registered Students</h3>
                <div class="number"><?php echo $total_students; ?></div>
            </div>
            <div class="stats-card">
                <h3>Total Departments</h3>
                <div class="number"><?php echo $total_departments; ?></div>
            </div>
            <div class="stats-card">
                <h3>Total Faculty Members</h3>
                <div class="number"><?php echo $total_faculty; ?></div>
            </div>
        </div>

        <div class="filters">
            <form method="GET" style="display: flex; gap: 1rem; width: 100%; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Search by name, email, or AG No..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; min-width: 200px;">
                <select name="department">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo $department_filter === $dept ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="dashboard.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>

        <div class="students-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>AG No</th>
                        <th>Degree</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">No students found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['ag_no']); ?></td>
                                <td><?php echo htmlspecialchars($student['degree']); ?></td>
                                <td><?php echo htmlspecialchars($student['department']); ?></td>
                                <td><?php echo htmlspecialchars($student['stu_status']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_student.php?id=<?php echo $student['stu_id']; ?>" class="btn-small btn-edit">Edit</a>
                                        <a href="?delete=<?php echo $student['stu_id']; ?>" class="btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
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

