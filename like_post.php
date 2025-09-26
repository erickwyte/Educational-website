<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'You must be logged in to like a post.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id']);

// Check if user already liked the post
$stmt = $conn->prepare("SELECT id FROM blog_likes WHERE user_id = ? AND post_id = ?");
$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['error' => 'You have already liked this post.']);
    exit();
}

// If not liked yet, insert like record
$stmt = $conn->prepare("INSERT INTO blog_likes (user_id, post_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();

// Increment likes in `blog_posts` table
$stmt = $conn->prepare("UPDATE blog_posts SET likes = likes + 1 WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();

// Fetch updated like count
$stmt = $conn->prepare("SELECT likes FROM blog_posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['likes' => $row['likes']]);
exit();
?>
