<?php
// orders/view.php - View Order Details
require_once '../config.php';
requireLogin();

$conn = getConnection();
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.name, u.email, u.phone 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.user_id 
                        WHERE o.order_id = ? AND (o.user_id = ? OR ? = 1)");
$is_admin = isAdmin() ? 1 : 0;
$stmt->bind_param("iii", $order_id, $user_id, $is_admin);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: /user/profile.php');
    exit();
}

// Get order items
$items = $conn->query("SELECT oi.*, p.name, p.brand, p.image_url 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.product_id 
                       WHERE oi.order_id = $order_id");

include '../header.php';
?>

<section class="py-16">
    <div class="max-w-6xl mx-auto">
        <?php if(isset($_SESSION['message'])): ?>
            <div class="bg-green-500/20 border border-green-500 text-green-200 px-4 py-3 rounded mb-4">
                <?= $_SESSION['message'] ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold logo-glow">Order #<?= $order['order_id'] ?></h1>
            <?php if(isAdmin()): ?>
                <a href="/admin/orders/read.php" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition">
                    ← Back to Orders
                </a>
            <?php else: ?>
                <a href="/user/profile.php" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition">
                    ← Back to Profile
                </a>
            <?php endif; ?>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Items -->
            <div class="lg:col-span-2 space-y-6">
                <div class="futuristic-card p-6 rounded-xl">
                    <h2 class="text-2xl font-bold mb-6 text-neon-blue">Order Items</h2>
                    
                    <div class="space-y-4">
                        <?php while($item = $items->fetch_assoc()): ?>
                            <div class="flex gap-4 pb-4 border-b border-slate-700 last:border-0">
                                <img src="<?= $item['image_url'] ?>" alt="<?= $item['name'] ?>" 
                                     class="w-24 h-24 object-cover rounded-lg">
                                
                                <div class="flex-1">
                                    <h3 class="font-bold text-lg"><?= $item['name'] ?></h3>
                                    <p class="text-slate-400"><?= $item['brand'] ?></p>
                                    <p class="text-sm text-slate-400 mt-2">
                                        <?= formatCurrency($item['price']) ?> × <?= $item['quantity'] ?>
                                    </p>
                                </div>
                                
                                <div class="text-right">
                                    <p class="text-xl font-bold"><?= formatCurrency($item['price'] * $item['quantity']) ?></p>
                                    
                                    <?php if($order['status'] == 'Delivered' && !isAdmin()): 
                                        // Check if already reviewed
                                        $check_review = $conn->query("SELECT review_id FROM reviews 
                                                                     WHERE user_id = $user_id 
                                                                     AND product_id = {$item['product_id']}");
                                        if($check_review->num_rows == 0):
                                    ?>
                                        <a href="/reviews/create.php?order_id=<?= $order_id ?>&product_id=<?= $item['product_id'] ?>" 
                                           class="inline-block mt-2 text-sm text-neon-blue hover:underline">
                                            Write a Review
                                        </a>
                                    <?php else: ?>
                                        <span class="inline-block mt-2 text-sm text-green-400">✓ Reviewed</span>
                                    <?php endif; endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <!-- Order Timeline -->
                <div class="futuristic-card p-6 rounded-xl">
                    <h2 class="text-2xl font-bold mb-6 text-neon-blue">Order Status</h2>
                    
                    <div class="relative pl-8">
                        <?php 
                        $statuses = ['Processing', 'Shipped', 'Delivered'];
                        $current_status = $order['status'];
                        $current_index = array_search($current_status, $statuses);
                        
                        foreach($statuses as $index => $status):
                            $is_completed = $index <= $current_index;
                        ?>
                            <div class="relative mb-8 last:mb-0">
                                <div class="absolute left-[-2rem] top-0 w-4 h-4 rounded-full <?= $is_completed ? 'bg-neon-blue' : 'bg-gray-600' ?>"></div>
                                <?php if($index < count($statuses) - 1): ?>
                                    <div class="absolute left-[-1.5rem] top-4 w-0.5 h-full <?= $is_completed ? 'bg-neon-blue' : 'bg-gray-600' ?>"></div>
                                <?php endif; ?>
                                
                                <h3 class="font-bold <?= $is_completed ? 'text-neon-blue' : 'text-gray-400' ?>"><?= $status ?></h3>
                                <?php if($status == $current_status): ?>
                                    <p class="text-sm text-slate-400">Current Status</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Order Information -->
            <div class="lg:col-span-1 space-y-6">
                <div class="futuristic-card p-6 rounded-xl">
                    <h2 class="text-2xl font-bold mb-4 text-neon-blue">Order Info</h2>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-slate-400 text-sm">Order Date</p>
                            <p class="font-semibold"><?= date('M d, Y', strtotime($order['order_date'])) ?></p>
                        </div>
                        
                        <div>
                            <p class="text-slate-400 text-sm">Payment Method</p>
                            <p class="font-semibold"><?= $order['payment_method'] ?></p>
                        </div>
                        
                        <div>
                            <p class="text-slate-400 text-sm">Total Amount</p>
                            <p class="text-2xl font-bold text-neon-blue"><?= formatCurrency($order['total_amount']) ?></p>
                        </div>
                        
                        <?php if($order['tracking_number']): ?>
                            <div>
                                <p class="text-slate-400 text-sm">Tracking Number</p>
                                <p class="font-semibold"><?= $order['tracking_number'] ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="futuristic-card p-6 rounded-xl">
                    <h2 class="text-2xl font-bold mb-4 text-neon-blue">Shipping Info</h2>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-slate-400 text-sm">Customer</p>
                            <p class="font-semibold"><?= $order['name'] ?></p>
                        </div>
                        
                        <div>
                            <p class="text-slate-400 text-sm">Contact</p>
                            <p class="font-semibold"><?= $order['phone'] ?></p>
                            <p class="text-sm"><?= $order['email'] ?></p>
                        </div>
                        
                        <div>
                            <p class="text-slate-400 text-sm">Address</p>
                            <p class="font-semibold"><?= nl2br($order['shipping_address']) ?></p>
                        </div>
                    </div>
                </div>
                
                <?php if(isAdmin()): ?>
                    <div class="futuristic-card p-6 rounded-xl">
                        <h2 class="text-xl font-bold mb-4 text-neon-blue">Admin Actions</h2>
                        <a href="/admin/orders/update.php?id=<?= $order['order_id'] ?>" 
                           class="block w-full py-2 text-center bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition">
                            Update Order
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
include '../footer.php';
$conn->close();
?>