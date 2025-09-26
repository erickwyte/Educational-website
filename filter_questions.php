<?php
require 'config.php';

$search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";

$stmt = $conn->prepare("SELECT * FROM questions WHERE title LIKE ?");
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='question-item'><a href='view_questions.php?id=" . $row['id'] . "'>" . htmlspecialchars($row['title']) . "</a></div>";
    }
} else {
    echo "<p>No questions found.</p>";
}
?>
