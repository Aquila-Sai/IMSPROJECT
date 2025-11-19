<?php
// admin/products/delete.php - Toggle Product Active Status
require_once '../../config.php';
requireAdmin();

$conn = getConnection();
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id > 0) {
    // Get current status
    $stmt = $conn->prepare("SELECT is_active, name FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $new_status = $product['is_active'] == 1 ? 0 : 1;
        $product_name = $product['name'];
        
        // Toggle status
        $update = $conn->prepare("UPDATE products SET is_active = ? WHERE product_id = ?");
        $update->bind_param("ii", $new_status, $product_id);
        
        if ($update->execute()) {
            // Log admin action
            $admin_id = $_SESSION['user_id'];
            $action_text = $new_status == 1 ? "Activated" : "Deactivated";
            $action = "$action_text product: $product_name (ID: $product_id)";
            $conn->query("INSERT INTO admin_logs (admin_id, action, log_date) VALUES ($admin_id, '$action', NOW())");
        }
        $update->close();
    }
    $stmt->close();
}

$conn->close();
header('Location: /admin/products/read.php');
exit();
?>