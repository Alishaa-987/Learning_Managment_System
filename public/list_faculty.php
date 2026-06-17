<?php
require_once 'config.php';
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT stu_id, name, email, cnic, ag_no, is_focal_person FROM students WHERE role='faculty'");
$faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Current Faculty Members</h2>";
echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th><th>CNIC</th><th>AG No</th><th>Is Focal Person</th></tr>";
foreach ($faculty as $f) {
    echo "<tr>";
    echo "<td>{$f['stu_id']}</td>";
    echo "<td>{$f['name']}</td>";
    echo "<td>{$f['email']}</td>";
    echo "<td>{$f['cnic']}</td>";
    echo "<td>{$f['ag_no']}</td>";
    echo "<td>{$f['is_focal_person']}</td>";
    echo "</tr>";
}
echo "</table>";
?>
