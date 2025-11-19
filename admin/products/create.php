<?php
// admin/products/create.php - Create New Product
require_once '../../config.php';
requireAdmin();

$conn = getConnection();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $brand = sanitize($_POST['brand']);
    $price = floatval($_POST['price']);
    $description = sanitize($_POST['description']);
    $cpu = sanitize($_POST['cpu']);
    $ram = sanitize($_POST['ram']);
    $storage = sanitize($_POST['storage']);
    $screen_size = sanitize($_POST['screen_size']);
    $stock = intval($_POST['stock']);
    $image_url = sanitize($_POST['image_url']);
    
    $stmt = $conn->prepare("INSERT INTO products (name, brand, price, description, cpu, ram, storage, screen_size, stock, image_url, is_active) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssdsssssis", $name, $brand, $price, $description, $cpu, $ram, $storage, $screen_size, $stock, $image_url);
    
    if ($stmt->execute()) {
        $success = "Product added successfully!";
        // Log admin action
        $admin_id = $_SESSION['user_id'];
        $action = "Added new product: $name";
        $conn->query("INSERT INTO admin_logs (admin_id, action, log_date) VALUES ($admin_id, '$action', NOW())");
    } else {
        $error = "Failed to add product: " . $conn->error;
    }
    $stmt->close();
}

include '../../header.php';
?>

<section class="py-16">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold logo-glow">Add New Product</h1>
            <a href="/lumen/admin/products/read.php" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition">
                ← Back to Products
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-400 mb-2">Product Name *</label>
                        <input type="text" name="name" required
                               class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 mb-2">Brand *</label>
                        <input type="text" name="brand" required
                               class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 mb-2">Price (₱) *</label>
                        <input type="number" name="price" step="0.01" min="0" required
                               class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 mb-2">Stock Quantity *</label>
                        <input type="number" name="stock" min="0" required
                               class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 mb-2">CPU *</label>
                        <input type="text" name="cpu" required placeholder="e.g., Intel i7-12700H"
                               class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 mb-2">RAM *</label>
                        <input type="text" name="ram" required placeholder="e.g., 16GB DDR4"
                               class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 mb-2">Storage *</label>
                        <input type="text" name="storage" required placeholder="e.g., 512GB SSD"
                               class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 mb-2">Screen Size *</label>
                        <input type="text" name="screen_size" required placeholder='e.g., 15.6"'
                               class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                    </div>
                </div>
                
                <div>
                    <label class="block text-slate-400 mb-2">Image URL *</label>
                    <input type="url" name="image_url" required placeholder="https://example.com/image.jpg"
                           class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                </div>
                
                <div>
                    <label class="block text-slate-400 mb-2">Description *</label>
                    <textarea name="description" rows="5" required
                              class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue"></textarea>
                </div>
                
                <button type="submit" class="w-full py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200 text-lg font-semibold">
                    Add Product
                </button>
            </form>
        </div>
    </div>
</section>

<?php
include '../../footer.php';
$conn->close();
?>