<?php
require_once '../../config.php';
requireAdmin();

$conn = getConnection();

// Get inventory data with stock value
$inventory = $conn->query("SELECT 
    p.*,
    (p.stock * p.price) as stock_value,
    COALESCE(SUM(oi.quantity), 0) as total_sold
    FROM products p
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    WHERE p.is_active = 1
    GROUP BY p.product_id
    ORDER BY stock_value DESC");

$total_inventory_value = 0;
$total_products = 0;
$low_stock_count = 0;

include '../../header.php';
?>

<section class="py-16">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold logo-glow">Inventory Report</h1>
            <a href="/lumen/admin/dashboard.php" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition">
                ← Dashboard
            </a>
        </div>
        
        <!-- Inventory Summary -->
        <?php 
        $inventory->data_seek(0);
        while($product = $inventory->fetch_assoc()) {
            $total_inventory_value += $product['stock_value'];
            $total_products++;
            if($product['stock'] <= 5) $low_stock_count++;
        }
        ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="futuristic-card p-6 rounded-xl">
                <p class="text-slate-400 mb-2">Total Inventory Value</p>
                <p class="text-3xl font-bold text-neon-blue"><?= formatCurrency($total_inventory_value) ?></p>
            </div>
            
            <div class="futuristic-card p-6 rounded-xl">
                <p class="text-slate-400 mb-2">Total Products</p>
                <p class="text-3xl font-bold text-neon-blue"><?= $total_products ?></p>
            </div>
            
            <div class="futuristic-card p-6 rounded-xl">
                <p class="text-slate-400 mb-2">Low Stock Items</p>
                <p class="text-3xl font-bold text-red-400"><?= $low_stock_count ?></p>
            </div>
        </div>
        
        <!-- Inventory Table -->
        <div class="futuristic-card p-6 rounded-xl">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-neon-blue">Product Inventory</h2>
                <button onclick="window.print()" class="px-4 py-2 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition">
                    Print Report
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neon-blue/30">
                            <th class="text-left py-3 px-4">Product</th>
                            <th class="text-left py-3 px-4">Brand</th>
                            <th class="text-left py-3 px-4">Stock</th>
                            <th class="text-left py-3 px-4">Price</th>
                            <th class="text-left py-3 px-4">Stock Value</th>
                            <th class="text-left py-3 px-4">Total Sold</th>
                            <th class="text-left py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $inventory->data_seek(0);
                        while($product = $inventory->fetch_assoc()): 
                        ?>
                            <tr class="border-b border-slate-700 hover:bg-primary-panel/50">
                                <td class="py-3 px-4"><?= $product['name'] ?></td>
                                <td class="py-3 px-4"><?= $product['brand'] ?></td>
                                <td class="py-3 px-4">
                                    <span class="<?= $product['stock'] <= 5 ? 'text-red-400 font-bold' : '' ?>">
                                        <?= $product['stock'] ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4"><?= formatCurrency($product['price']) ?></td>
                                <td class="py-3 px-4 font-bold"><?= formatCurrency($product['stock_value']) ?></td>
                                <td class="py-3 px-4"><?= $product['total_sold'] ?></td>
                                <td class="py-3 px-4">
                                    <?php if($product['stock'] <= 5): ?>
                                        <span class="px-2 py-1 rounded text-sm bg-red-500/20 text-red-300">Low Stock</span>
                                    <?php elseif($product['stock'] > 20): ?>
                                        <span class="px-2 py-1 rounded text-sm bg-green-500/20 text-green-300">In Stock</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 rounded text-sm bg-yellow-500/20 text-yellow-300">Normal</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include '../../footer.php'; $conn->close(); ?>