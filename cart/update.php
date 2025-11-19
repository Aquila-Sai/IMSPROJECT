<?php
// cart/update.php - Update Cart Item Quantity
require_once '../config.php';
requireLogin();

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart_id = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);
    $user_id = $_SESSION['user_id'];
    
    // Verify cart item belongs to user and check stock
    $stmt = $conn->prepare("SELECT c.cart_id, p.stock 
                           FROM cart c 
                           JOIN products p ON c.product_id = p.product_id 
                           WHERE c.cart_id = ? AND c.user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $cart = $result->fetch_assoc();
        
        if ($quantity <= $cart['stock'] && $quantity > 0) {
            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            $update->bind_param("ii", $quantity, $cart_id);
            $update->execute();
            $update->close();
            $_SESSION['message'] = "Cart updated!";
        } else {
            $_SESSION['error'] = "Invalid quantity or not enough stock!";
        }
    }
    $stmt->close();
}

$conn->close();
header('Location: /cart/view.php');
exit();
?>