<?php
require_once 'config.php';
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT stu_id, name, email, ag_no, cnic, role FROM students");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "ID | Name | Email | AG_NO | CNIC | Role\n";
echo "---|---|---|---|---|---\n";
foreach ($users as $u) {
    echo "{$u['stu_id']} | {$u['name']} | {$u['email']} | {$u['ag_no']} | {$u['cnic']} | {$u['role']}\n";
}
