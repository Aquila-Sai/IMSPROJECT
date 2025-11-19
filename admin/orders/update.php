<?php
// admin/orders/update.php - Update Order Status
require_once '../../config.php';
requireAdmin();

$conn = getConnection();
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success = '';
$error = '';

// Get order
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: /admin/orders/read.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = sanitize($_POST['status']);
    $tracking_number = sanitize($_POST['tracking_number']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ?, tracking_number = ? WHERE order_id = ?");
    $stmt->bind_param("ssi", $status, $tracking_number, $order_id);
    
    if ($stmt->execute()) {
        $success = "Order updated successfully!";
        
        // Log admin action
        $admin_id = $_SESSION['user_id'];
        $action = "Updated order #$order_id to status: $status";
        $conn->query("INSERT INTO admin_logs (admin_id, action, log_date) VALUES ($admin_id, '$action', NOW())");
        
        // Refresh order data
        $stmt2 = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
        $stmt2->bind_param("i", $order_id);
        $stmt2->execute();
        $order = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
    } else {
        $error = "Update failed: " . $conn->error;
    }
    $stmt->close();
}

include '../../header.php';
?>

<section class="py-16">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold logo-glow">Update Order #<?= $order['order_id'] ?></h1>
            <a href="/orders/view.php?id=<?= $order_id ?>" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition">
                ← View Order
            </a>
        </div>
        
        <?php if($success): ?>
            <div class="bg-green-500/20 border border-green-500 text-green-200 px-4 py-3 rounded mb-4">
                <?= $success ?>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <div class="futuristic-card p-8 rounded-xl">
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-slate-400 mb-2">Order Status *</label>
                    <select name="status" required
                            class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                        <option value="Processing" <?= $order['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="Shipped" <?= $order['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="Delivered" <?= $order['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-slate-400 mb-2">Tracking Number</label>
                    <input type="text" name="tracking_number" value="<?= $order['tracking_number'] ?? '' ?>"
                           placeholder="e.g., TRACK123456789"
                           class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                </div>
                
                <div class="bg-primary-panel/50 p-4 rounded-lg">
                    <h3 class="font-bold mb-2">Current Information</h3>
                    <p class="text-sm text-slate-400">Order Date: <?= date('M d, Y', strtotime($order['order_date'])) ?></p>
                    <p class="text-sm text-slate-400">Total: <?= formatCurrency($order['total_amount']) ?></p>
                    <p class="text-sm text-slate-400">Payment: <?= $order['payment_method'] ?></p>
                </div>
                
                <button type="submit" class="w-full py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200 text-lg font-semibold">
                    Update Order
                </button>
            </form>
        </div>
    </div>
</section>

<?php
include '../../footer.php';
$conn->close();
?>