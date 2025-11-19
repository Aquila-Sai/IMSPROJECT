<?php
// cart/add.php - Add Product to Cart
require_once '../config.php';
requireLogin();

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $user_id = $_SESSION['user_id'];
    
    // Check if product exists and has stock
    $stmt = $conn->prepare("SELECT stock, price FROM products WHERE product_id = ? AND is_active = 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        if ($product['stock'] >= $quantity) {
            // Check if item already in cart
            $check = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $check->bind_param("ii", $user_id, $product_id);
            $check->execute();
            $cart_result = $check->get_result();
            
            if ($cart_result->num_rows > 0) {
                // Update quantity
                $cart = $cart_result->fetch_assoc();
                $new_quantity = $cart['quantity'] + $quantity;
                
                if ($new_quantity <= $product['stock']) {
                    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
                    $update->bind_param("ii", $new_quantity, $cart['cart_id']);
                    $update->execute();
                    $update->close();
                    $_SESSION['message'] = "Cart updated successfully!";
                } else {
                    $_SESSION['error'] = "Not enough stock available!";
                }
            } else {
                // Add new item to cart
                $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $insert->bind_param("iii", $user_id, $product_id, $quantity);
                $insert->execute();
                $insert->close();
                $_SESSION['message'] = "Product added to cart!";
            }
            $check->close();
        } else {
            $_SESSION['error'] = "Not enough stock available!";
        }
    } else {
        $_SESSION['error'] = "Product not found or unavailable!";
    }
    $stmt->close();
}

$conn->close();
header('Location: /cart/view.php');
exit();
?>