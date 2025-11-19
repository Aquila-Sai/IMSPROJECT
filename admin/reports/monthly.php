<?php
// admin/reports/monthly.php - Monthly Sales Report
require_once '../../config.php';
requireAdmin();

$conn = getConnection();

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');

// Get monthly sales data
$monthly_sales = $conn->query("SELECT 
    DATE(order_date) as sale_date,
    COUNT(DISTINCT order_id) as total_orders,
    SUM(total_amount) as daily_revenue
    FROM orders
    WHERE YEAR(order_date) = $year 
    AND MONTH(order_date) = $month
    AND status != 'Cancelled'
    GROUP BY DATE(order_date)
    ORDER BY sale_date ASC");

$total_month_revenue = 0;
$total_month_orders = 0;

// Brand sales for the month
$brand_sales = $conn->query("SELECT 
    p.brand,
    COUNT(DISTINCT o.order_id) as orders,
    SUM(oi.quantity) as units_sold,
    SUM(oi.quantity * oi.price) as revenue
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE YEAR(o.order_date) = $year 
    AND MONTH(o.order_date) = $month
    AND o.status != 'Cancelled'
    GROUP BY p.brand
    ORDER BY revenue DESC");

include '../../header.php';
?>

<section class="py-16">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold logo-glow">Monthly Sales Report</h1>
            <a href="/admin/dashboard.php" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition">
                ← Dashboard
            </a>
        </div>
        
        <!-- Month/Year Selector -->
        <div class="futuristic-card p-6 rounded-xl mb-6">
            <form method="GET" class="flex gap-4">
                <select name="month" class="px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $month == $m ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
                
                <select name="year" class="px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                    <?php for($y = 2020; $y <= date('Y'); $y++): ?>
                        <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                
                <button type="submit" class="px-6 py-2 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition">
                    View Report
                </button>
            </form>
        </div>
        
        <!-- Daily Sales Table -->
        <div class="futuristic-card p-6 rounded-xl mb-8">
            <h2 class="text-2xl font-bold mb-4 text-neon-blue">Daily Sales - <?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neon-blue/30">
                            <th class="text-left py-3 px-4">Date</th>
                            <th class="text-left py-3 px-4">Orders</th>
                            <th class="text-left py-3 px-4">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($monthly_sales->num_rows > 0): ?>
                            <?php while($day = $monthly_sales->fetch_assoc()): 
                                $total_month_revenue += $day['daily_revenue'];
                                $total_month_orders += $day['total_orders'];
                            ?>
                                <tr class="border-b border-slate-700 hover:bg-primary-panel/50">
                                    <td class="py-3 px-4"><?= date('D, M d', strtotime($day['sale_date'])) ?></td>
                                    <td class="py-3 px-4"><?= $day['total_orders'] ?></td>
                                    <td class="py-3 px-4 font-bold"><?= formatCurrency($day['daily_revenue']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                            <tr class="border-t-2 border-neon-blue">
                                <td class="py-4 px-4 font-bold">TOTAL</td>
                                <td class="py-4 px-4 font-bold"><?= $total_month_orders ?></td>
                                <td class="py-4 px-4 font-bold text-neon-blue"><?= formatCurrency($total_month_revenue) ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-8 text-slate-400">No sales data for this month</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Brand Sales Breakdown -->
        <div class="futuristic-card p-6 rounded-xl">
            <h2 class="text-2xl font-bold mb-4 text-neon-blue">Sales by Brand</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neon-blue/30">
                            <th class="text-left py-3 px-4">Brand</th>
                            <th class="text-left py-3 px-4">Orders</th>
                            <th class="text-left py-3 px-4">Units Sold</th>
                            <th class="text-left py-3 px-4">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($brand_sales->num_rows > 0): ?>
                            <?php while($brand = $brand_sales->fetch_assoc()): ?>
                                <tr class="border-b border-slate-700 hover:bg-primary-panel/50">
                                    <td class="py-3 px-4 font-bold"><?= $brand['brand'] ?></td>
                                    <td class="py-3 px-4"><?= $brand['orders'] ?></td>
                                    <td class="py-3 px-4"><?= $brand['units_sold'] ?></td>
                                    <td class="py-3 px-4 font-bold text-neon-blue"><?= formatCurrency($brand['revenue']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-8 text-slate-400">No brand sales data</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include '../../footer.php'; $conn->close(); ?>

<!-- ============================================ -->
<!-- admin/reports/inventory.php - Inventory Report -->
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
            <a href="/admin/dashboard.php" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition">
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