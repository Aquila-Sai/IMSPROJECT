<?php
// admin/products/read.php - View All Products
require_once '../../config.php';
requireAdmin();

$conn = getConnection();

// Get search parameter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$sql = "SELECT p.*, COUNT(oi.item_id) as total_sold 
        FROM products p 
        LEFT JOIN order_items oi ON p.product_id = oi.product_id 
        WHERE 1=1";

if ($search) {
    $sql .= " AND (p.name LIKE '%$search%' OR p.brand LIKE '%$search%')";
}

$sql .= " GROUP BY p.product_id ORDER BY p.product_id DESC";
$products = $conn->query($sql);

include '../../header.php';
?>

<section class="py-16">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold logo-glow">Manage Products</h1>
            <div class="flex gap-4">
                <a href="/admin/dashboard.php" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition">
                    ← Dashboard
                </a>
                <a href="/admin/products/create.php" class="px-4 py-2 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition">
                    + Add Product
                </a>
            </div>
        </div>
        
        <!-- Search Bar -->
        <div class="futuristic-card p-4 rounded-xl mb-6">
            <form method="GET" class="flex gap-4">
                <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>"
                       class="flex-1 px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                <button type="submit" class="px-6 py-2 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition">
                    Search
                </button>
            </form>
        </div>
        
        <!-- Products Table -->
        <div class="futuristic-card p-6 rounded-xl overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-neon-blue/30">
                        <th class="text-left py-3 px-4">ID</th>
                        <th class="text-left py-3 px-4">Image</th>
                        <th class="text-left py-3 px-4">Name</th>
                        <th class="text-left py-3 px-4">Brand</th>
                        <th class="text-left py-3 px-4">Price</th>
                        <th class="text-left py-3 px-4">Stock</th>
                        <th class="text-left py-3 px-4">Sold</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-left py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($products->num_rows > 0): ?>
                        <?php while($product = $products->fetch_assoc()): ?>
                            <tr class="border-b border-slate-700 hover:bg-primary-panel/50">
                                <td class="py-3 px-4"><?= $product['product_id'] ?></td>
                                <td class="py-3 px-4">
                                    <img src="<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>" 
                                         class="w-16 h-16 object-cover rounded">
                                </td>
                                <td class="py-3 px-4"><?= $product['name'] ?></td>
                                <td class="py-3 px-4"><?= $product['brand'] ?></td>
                                <td class="py-3 px-4"><?= formatCurrency($product['price']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="<?= $product['stock'] <= 5 ? 'text-red-400 font-bold' : '' ?>">
                                        <?= $product['stock'] ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4"><?= $product['total_sold'] ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-3 py-1 rounded-full text-sm <?= $product['is_active'] ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300' ?>">
                                        <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <a href="/admin/products/update.php?id=<?= $product['product_id'] ?>" 
                                           class="text-neon-blue hover:underline">Edit</a>
                                        <a href="/admin/products/delete.php?id=<?= $product['product_id'] ?>" 
                                           onclick="return confirm('Are you sure you want to deactivate this product?')"
                                           class="text-red-400 hover:underline">
                                           <?= $product['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-8 text-slate-400">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php
include '../../footer.php';
$conn->close();
?>