<!-- cart/remove.php - Remove Item from Cart -->
<?php
// cart/remove.php
require_once '../config.php';
requireLogin();

$conn = getConnection();

$cart_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Verify cart item belongs to user before deleting
$stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
$stmt->bind_param("ii", $cart_id, $user_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Item removed from cart!";
}

$stmt->close();
$conn->close();
header('Location: /cart/view.php');
exit();
?>