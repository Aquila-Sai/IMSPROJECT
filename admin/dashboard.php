<?php
// admin/dashboard.php - Admin Dashboard
require_once '../config.php';
requireAdmin();

$conn = getConnection();

// Get statistics
$total_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_customers = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'Cancelled'")->fetch_assoc()['total'] ?? 0;

// Recent orders
$recent_orders = $conn->query("SELECT o.*, u.name 
                               FROM orders o 
                               JOIN users u ON o.user_id = u.user_id 
                               ORDER BY o.order_date DESC 
                               LIMIT 5");

// Low stock products
$low_stock = $conn->query("SELECT * FROM products WHERE stock <= 5 AND is_active = 1 ORDER BY stock ASC LIMIT 5");

// Top selling products
$top_products = $conn->query("SELECT p.name, p.brand, COUNT(oi.item_id) as sold, SUM(oi.quantity) as total_qty
                              FROM products p 
                              JOIN order_items oi ON p.product_id = oi.product_id 
                              GROUP BY p.product_id 
                              ORDER BY total_qty DESC 
                              LIMIT 5");

include '../header.php';
?>

<section class="py-16">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold logo-glow">Admin Dashboard</h1>
            <a href="/lumen/user/logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                Logout
            </a>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="futuristic-card p-6 rounded-xl">
                <p class="text-slate-400 mb-2">Total Revenue</p>
                <p class="text-3xl font-bold text-neon-blue"><?= formatCurrency($total_revenue) ?></p>
            </div>
            
            <div class="futuristic-card p-6 rounded-xl">
                <p class="text-slate-400 mb-2">Total Orders</p>
                <p class="text-3xl font-bold text-neon-blue"><?= $total_orders ?></p>
            </div>
            
            <div class="futuristic-card p-6 rounded-xl">
                <p class="text-slate-400 mb-2">Total Products</p>
                <p class="text-3xl font-bold text-neon-blue"><?= $total_products ?></p>
            </div>
            
            <div class="futuristic-card p-6 rounded-xl">
                <p class="text-slate-400 mb-2">Total Customers</p>
                <p class="text-3xl font-bold text-neon-blue"><?= $total_customers ?></p>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="futuristic-card p-6 rounded-xl mb-8">
            <h2 class="text-2xl font-bold mb-4 text-neon-blue">Quick Actions</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <a href="/lumen/admin/products/create.php" class="px-4 py-3 bg-neon-blue/20 border border-neon-blue text-neon-blue rounded-lg hover:bg-neon-blue hover:text-white transition text-center">
                    + Add Product
                </a>
                <a href="/lumen/admin/products/read.php" class="px-4 py-3 bg-neon-blue/20 border border-neon-blue text-neon-blue rounded-lg hover:bg-neon-blue hover:text-white transition text-center">
                    Manage Products
                </a>
                <a href="/lumen/admin/orders/read.php" class="px-4 py-3 bg-neon-blue/20 border border-neon-blue text-neon-blue rounded-lg hover:bg-neon-blue hover:text-white transition text-center">
                    View Orders
                </a>
                <a href="/lumen/admin/reports/sales.php" class="px-4 py-3 bg-neon-blue/20 border border-neon-blue text-neon-blue rounded-lg hover:bg-neon-blue hover:text-white transition text-center">
                    Sales Report
                </a>
                <a href="/lumen/admin/reports/inventory.php" class="px-4 py-3 bg-neon-blue/20 border border-neon-blue text-neon-blue rounded-lg hover:bg-neon-blue hover:text-white transition text-center">
                    Inventory Report
                </a>
                <a href="/lumen/admin/reports/monthly.php" class="px-4 py-3 bg-neon-blue/20 border border-neon-blue text-neon-blue rounded-lg hover:bg-neon-blue hover:text-white transition text-center">
                    Monthly Report
                </a>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Orders -->
            <div class="futuristic-card p-6 rounded-xl">
                <h2 class="text-2xl font-bold mb-4 text-neon-blue">Recent Orders</h2>
                <div class="space-y-3">
                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                        <div class="flex justify-between items-center p-3 bg-primary-panel/50 rounded-lg">
                            <div>
                                <p class="font-bold">#<?= $order['order_id'] ?> - <?= $order['name'] ?></p>
                                <p class="text-sm text-slate-400"><?= date('M d, Y', strtotime($order['order_date'])) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold"><?= formatCurrency($order['total_amount']) ?></p>
                                <span class="text-sm px-2 py-1 rounded-full
                                    <?php 
                                        if($order['status'] == 'Delivered') echo 'bg-green-500/20 text-green-300';
                                        elseif($order['status'] == 'Shipped') echo 'bg-blue-500/20 text-blue-300';
                                        else echo 'bg-yellow-500/20 text-yellow-300';
                                    ?>">
                                    <?= $order['status'] ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <!-- Low Stock Alert -->
            <div class="futuristic-card p-6 rounded-xl">
                <h2 class="text-2xl font-bold mb-4 text-red-400">⚠️ Low Stock Products</h2>
                <div class="space-y-3">
                    <?php if($low_stock->num_rows > 0): ?>
                        <?php while($product = $low_stock->fetch_assoc()): ?>
                            <div class="flex justify-between items-center p-3 bg-red-500/10 border border-red-500/30 rounded-lg">
                                <div>
                                    <p class="font-bold"><?= $product['name'] ?></p>
                                    <p class="text-sm text-slate-400"><?= $product['brand'] ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-red-400">Stock: <?= $product['stock'] ?></p>
                                    <a href="/lumen/admin/products/update.php?id=<?= $product['product_id'] ?>" 
                                       class="text-sm text-neon-blue hover:underline">Restock</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-center text-slate-400 py-4">All products are well stocked!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Top Selling Products -->
        <div class="futuristic-card p-6 rounded-xl mt-8">
            <h2 class="text-2xl font-bold mb-4 text-neon-blue">Top Selling Products</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neon-blue/30">
                            <th class="text-left py-3 px-4">Rank</th>
                            <th class="text-left py-3 px-4">Product Name</th>
                            <th class="text-left py-3 px-4">Brand</th>
                            <th class="text-left py-3 px-4">Units Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        while($product = $top_products->fetch_assoc()): 
                        ?>
                            <tr class="border-b border-slate-700">
                                <td class="py-3 px-4 font-bold">#<?= $rank++ ?></td>
                                <td class="py-3 px-4"><?= $product['name'] ?></td>
                                <td class="py-3 px-4"><?= $product['brand'] ?></td>
                                <td class="py-3 px-4"><?= $product['total_qty'] ?> units</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php
include '../footer.php';
$conn->close();
?>