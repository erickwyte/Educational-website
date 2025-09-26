<?php
include 'config.php'; // Database connection

$sql = "SELECT course_name FROM courses ORDER BY course_name ASC";
$result = $conn->query($sql);

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row['course_name'];
}

echo json_encode($courses);
?>
