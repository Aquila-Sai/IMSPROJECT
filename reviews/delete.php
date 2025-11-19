<?php
require_once '../config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$review_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get product_id before deleting
$stmt = $conn->prepare("SELECT product_id FROM reviews WHERE review_id = ? AND user_id = ?");
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product_id = $result->fetch_assoc()['product_id'];
    
    // Delete review
    $delete = $conn->prepare("DELETE FROM reviews WHERE review_id = ? AND user_id = ?");
    $delete->bind_param("ii", $review_id, $user_id);
    
    if ($delete->execute()) {
        $_SESSION['message'] = "Review deleted successfully!";
        header('Location: /products/view.php?id=' . $product_id);
    } else {
        $_SESSION['error'] = "Failed to delete review!";
        header('Location: /user/profile.php');
    }
    $delete->close();
} else {
    header('Location: /user/profile.php');
}

$stmt->close();
$conn->close();
exit();
?>