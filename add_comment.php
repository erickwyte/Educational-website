<?php
// add_comment.php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $topic_id = (int)$_POST['topic_id'];
    $content = htmlspecialchars(trim($_POST['content']));

    if ($content) {
        $sql = "INSERT INTO discussion_comments (user_id, topic_id, content) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iis', $user_id, $topic_id, $content);

        if ($stmt->execute()) {
            header("Location: comments.php?topic_id=$topic_id");
            exit;
        } else {
            echo "<div class='error-msg'>Error adding comment: " . $stmt->error . "</div>";
        }
    } else {
        echo "<div class='error-msg'>Please write a comment.</div>";
    }
}

$conn->close();
?>
