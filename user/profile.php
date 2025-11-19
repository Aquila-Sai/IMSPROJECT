<?php
// user/profile.php - User Profile (Read & Update)
require_once '../config.php';
requireLogin();

$conn = getConnection();
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $name, $phone, $address, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['name'] = $name;
        $success = "Profile updated successfully!";
    } else {
        $error = "Update failed: " . $conn->error;
    }
    $stmt->close();
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get order history
$orders = $conn->query("SELECT o.*, COUNT(oi.item_id) as item_count 
                        FROM orders o 
                        LEFT JOIN order_items oi ON o.order_id = oi.order_id 
                        WHERE o.user_id = {$_SESSION['user_id']} 
                        GROUP BY o.order_id 
                        ORDER BY o.order_date DESC");

include '../header.php';
?>

<section class="py-16">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-8 logo-glow">My Profile</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Information -->
            <div class="lg:col-span-2 futuristic-card p-6 rounded-xl">
                <h2 class="text-2xl font-bold mb-6 text-neon-blue">Account Information</h2>
                
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
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-slate-400 mb-2">Full Name</label>
                        <input type="text" name="name" value="<?= $user['name'] ?>" required
                               class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 mb-2">Email</label>
                        <input type="email" value="<?= $user['email'] ?>" disabled
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-gray-500 cursor-not-allowed">
                        <p class="text-xs text-slate-500 mt-1">Email cannot be changed</p>
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 mb-2">Phone</label>
                        <input type="tel" name="phone" value="<?= $user['phone'] ?>" required
                               class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 mb-2">Address</label>
                        <textarea name="address" rows="3" required
                                  class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue"><?= $user['address'] ?></textarea>
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200">
                        Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Quick Actions -->
            <div class="space-y-4">
                <div class="futuristic-card p-6 rounded-xl text-center">
                    <h3 class="text-xl font-bold mb-4 text-neon-blue">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="/cart/view.php" class="block px-4 py-2 bg-neon-blue/20 border border-neon-blue text-neon-blue rounded-lg hover:bg-neon-blue hover:text-white transition">
                            View Cart
                        </a>
                        <a href="/user/orders.php" class="block px-4 py-2 bg-neon-blue/20 border border-neon-blue text-neon-blue rounded-lg hover:bg-neon-blue hover:text-white transition">
                            My Orders
                        </a>
                        <a href="/index.php" class="block px-4 py-2 bg-neon-blue/20 border border-neon-blue text-neon-blue rounded-lg hover:bg-neon-blue hover:text-white transition">
                            Browse Products
                        </a>
                        <a href="/user/logout.php" class="block px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order History -->
        <div class="mt-8 futuristic-card p-6 rounded-xl">
            <h2 class="text-2xl font-bold mb-6 text-neon-blue">Order History</h2>
            
            <?php if($orders->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-neon-blue/30">
                                <th class="text-left py-3 px-4">Order ID</th>
                                <th class="text-left py-3 px-4">Date</th>
                                <th class="text-left py-3 px-4">Items</th>
                                <th class="text-left py-3 px-4">Total</th>
                                <th class="text-left py-3 px-4">Status</th>
                                <th class="text-left py-3 px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = $orders->fetch_assoc()): ?>
                                <tr class="border-b border-slate-700 hover:bg-primary-panel/50">
                                    <td class="py-3 px-4">#<?= $order['order_id'] ?></td>
                                    <td class="py-3 px-4"><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    <td class="py-3 px-4"><?= $order['item_count'] ?> items</td>
                                    <td class="py-3 px-4"><?= formatCurrency($order['total_amount']) ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-3 py-1 rounded-full text-sm
                                            <?php 
                                                if($order['status'] == 'Delivered') echo 'bg-green-500/20 text-green-300';
                                                elseif($order['status'] == 'Shipped') echo 'bg-blue-500/20 text-blue-300';
                                                elseif($order['status'] == 'Processing') echo 'bg-yellow-500/20 text-yellow-300';
                                                else echo 'bg-gray-500/20 text-gray-300';
                                            ?>">
                                            <?= $order['status'] ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="/orders/view.php?id=<?= $order['order_id'] ?>" 
                                           class="text-neon-blue hover:underline">View Details</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-slate-400 py-8">No orders yet. <a href="/index.php" class="text-neon-blue hover:underline">Start shopping!</a></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
include '../footer.php';
$conn->close();
?>