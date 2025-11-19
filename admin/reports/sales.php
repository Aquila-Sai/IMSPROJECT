<?php
// admin/reports/sales.php - Sales Report by Date Range, Product, and Brand
require_once '../../config.php';
requireAdmin();

$conn = getConnection();

// Get filter parameters
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');
$product_filter = isset($_GET['product']) ? sanitize($_GET['product']) : '';
$brand_filter = isset($_GET['brand']) ? sanitize($_GET['brand']) : '';

// Build query
$sql = "SELECT o.order_date, p.name, p.brand, oi.quantity, oi.price, 
               (oi.quantity * oi.price) as subtotal, o.status
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        WHERE o.order_date BETWEEN '$start_date' AND '$end_date'
        AND o.status != 'Cancelled'";

if ($product_filter) {
    $sql .= " AND p.name LIKE '%$product_filter%'";
}
if ($brand_filter) {
    $sql .= " AND p.brand = '$brand_filter'";
}

$sql .= " ORDER BY o.order_date DESC";
$sales = $conn->query($sql);

// Calculate totals
$total_revenue = 0;
$total_units = 0;

// Get brands for filter
$brands = $conn->query("SELECT DISTINCT brand FROM products ORDER BY brand");

include '../../header.php';
?>

<section class="py-16">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold logo-glow">Sales Report</h1>
            <a href="/admin/dashboard.php" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition">
                ← Dashboard
            </a>
        </div>
        
        <!-- Filters -->
        <div class="futuristic-card p-6 rounded-xl mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-slate-400 mb-2 text-sm">Start Date</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>"
                           class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                </div>
                
                <div>
                    <label class="block text-slate-400 mb-2 text-sm">End Date</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>"
                           class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                </div>
                
                <div>
                    <label class="block text-slate-400 mb-2 text-sm">Product</label>
                    <input type="text" name="product" placeholder="Search product..." value="<?= htmlspecialchars($product_filter) ?>"
                           class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                </div>
                
                <div>
                    <label class="block text-slate-400 mb-2 text-sm">Brand</label>
                    <select name="brand"
                            class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                        <option value="">All Brands</option>
                        <?php while($brand = $brands->fetch_assoc()): ?>
                            <option value="<?= $brand['brand'] ?>" <?= $brand_filter == $brand['brand'] ? 'selected' : '' ?>>
                                <?= $brand['brand'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="w-full px-6 py-2 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition">
                        Generate Report
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Summary Cards -->
        <?php 
        $sales->data_seek(0);
        while($sale = $sales->fetch_assoc()) {
            $total_revenue += $sale['subtotal'];
            $total_units += $sale['quantity'];
        }
        ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="futuristic-card p-6 rounded-xl">
                <p class="text-slate-400 mb-2">Total Revenue</p>
                <p class="text-3xl font-bold text-neon-blue"><?= formatCurrency($total_revenue) ?></p>
            </div>
            
            <div class="futuristic-card p-6 rounded-xl">
                <p class="text-slate-400 mb-2">Total Units Sold</p>
                <p class="text-3xl font-bold text-neon-blue"><?= $total_units ?></p>
            </div>
            
            <div class="futuristic-card p-6 rounded-xl">
                <p class="text-slate-400 mb-2">Total Orders</p>
                <p class="text-3xl font-bold text-neon-blue"><?= $sales->num_rows ?></p>
            </div>
        </div>
        
        <!-- Sales Table -->
        <div class="futuristic-card p-6 rounded-xl">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-neon-blue">Sales Details</h2>
                <button onclick="window.print()" class="px-4 py-2 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition">
                    Print Report
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neon-blue/30">
                            <th class="text-left py-3 px-4">Date</th>
                            <th class="text-left py-3 px-4">Product</th>
                            <th class="text-left py-3 px-4">Brand</th>
                            <th class="text-left py-3 px-4">Quantity</th>
                            <th class="text-left py-3 px-4">Price</th>
                            <th class="text-left py-3 px-4">Subtotal</th>
                            <th class="text-left py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sales->data_seek(0);
                        if($sales->num_rows > 0): 
                        ?>
                            <?php while($sale = $sales->fetch_assoc()): ?>
                                <tr class="border-b border-slate-700 hover:bg-primary-panel/50">
                                    <td class="py-3 px-4"><?= date('M d, Y', strtotime($sale['order_date'])) ?></td>
                                    <td class="py-3 px-4"><?= $sale['name'] ?></td>
                                    <td class="py-3 px-4"><?= $sale['brand'] ?></td>
                                    <td class="py-3 px-4"><?= $sale['quantity'] ?></td>
                                    <td class="py-3 px-4"><?= formatCurrency($sale['price']) ?></td>
                                    <td class="py-3 px-4 font-bold"><?= formatCurrency($sale['subtotal']) ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded text-sm bg-green-500/20 text-green-300">
                                            <?= $sale['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-8 text-slate-400">No sales data found for the selected criteria</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="border-t-2 border-neon-blue">
                        <tr>
                            <td colspan="5" class="py-4 px-4 text-right font-bold text-lg">TOTAL:</td>
                            <td class="py-4 px-4 font-bold text-lg text-neon-blue"><?= formatCurrency($total_revenue) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</section>

<?php
include '../../footer.php';
$conn->close();
?>