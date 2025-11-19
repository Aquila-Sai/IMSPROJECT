<?php
// index.php - Main Landing Page with Product Search
require_once 'config.php';
$conn = getConnection();

// Get search parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$brand = isset($_GET['brand']) ? sanitize($_GET['brand']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 999999;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'name';

// Build query
$sql = "SELECT p.*, AVG(r.rating) as avg_rating, COUNT(r.review_id) as review_count 
        FROM products p 
        LEFT JOIN reviews r ON p.product_id = r.product_id 
        WHERE p.is_active = 1";

if ($search) {
    $sql .= " AND (p.name LIKE '%$search%' OR p.brand LIKE '%$search%' OR p.description LIKE '%$search%')";
}
if ($brand) {
    $sql .= " AND p.brand = '$brand'";
}
$sql .= " AND p.price BETWEEN $min_price AND $max_price";
$sql .= " GROUP BY p.product_id";

// Sorting
switch($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY avg_rating DESC";
        break;
    default:
        $sql .= " ORDER BY p.name ASC";
}

$products = $conn->query($sql);

// Get all brands for filter
$brands_result = $conn->query("SELECT DISTINCT brand FROM products WHERE is_active = 1 ORDER BY brand");

include 'header.php';
?>

<!-- Hero Section -->
<section class="text-center py-20 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute top-20 left-10 w-64 h-64 bg-neon-blue rounded-full filter blur-3xl"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-600 rounded-full filter blur-3xl"></div>
    </div>
    
    <div class="relative z-10">
        <h1 class="text-6xl font-extrabold mb-6 logo-glow">Welcome to Lumen</h1>
        <p class="text-2xl text-slate-300 mb-12">Your Premium Laptop Destination</p>
        
        <a href="#products" class="inline-block px-8 py-4 bg-neon-blue text-white rounded-lg text-lg font-semibold hover:bg-blue-600 transition duration-300 shadow-lg shadow-neon-blue/50">
            Explore Products
        </a>
    </div>
</section>

<!-- Interactive Login Section -->
<section id="login-section" class="py-16">
    <div class="flex justify-center items-center">
        <div id="laptop-login-container">
            <div id="laptop-body" class="laptop-body">
                <!-- Closed State: "Tap to Open" -->
                <div class="laptop-screen">
                    <div class="closed-prompt text-center p-8">
                        <div class="text-4xl font-bold mb-2 logo-glow">Lumen</div>
                        <p class="text-slate-400">Click to Access Portal</p>
                    </div>
                    
                    <!-- Login Form (visible when open) -->
                    <div class="login-content p-8 w-full">
                        <h2 class="text-2xl font-bold mb-6 text-neon-blue">Client Login</h2>
                        <form action="/user/login.php" method="POST" class="space-y-4">
                            <input type="email" name="email" id="email" placeholder="Email" required
                                   class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                            <input type="password" name="password" placeholder="Password" required
                                   class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                            <button type="submit" class="w-full py-2 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200">
                                Login
                            </button>
                        </form>
                        <p class="mt-4 text-center text-slate-400 text-sm">
                            Don't have an account? <a href="/user/register.php" class="text-neon-blue hover:underline">Register</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <button id="close-btn" onclick="toggleLogin()" class="hidden ml-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
            Close
        </button>
    </div>
</section>

<!-- Product Search and Filter Section -->
<section id="products" class="py-16">
    <h2 class="text-4xl font-bold text-center mb-12 logo-glow">Browse Our Collection</h2>
    
    <!-- Search and Filter Form -->
    <div class="futuristic-card p-6 rounded-xl mb-8">
        <form method="GET" action="index.php#products" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>"
                   class="px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
            
            <select name="brand" class="px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                <option value="">All Brands</option>
                <?php while($brand_row = $brands_result->fetch_assoc()): ?>
                    <option value="<?= $brand_row['brand'] ?>" <?= $brand == $brand_row['brand'] ? 'selected' : '' ?>>
                        <?= $brand_row['brand'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <div class="flex gap-2">
                <input type="number" name="min_price" placeholder="Min Price" value="<?= $min_price > 0 ? $min_price : '' ?>"
                       class="w-1/2 px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                <input type="number" name="max_price" placeholder="Max Price" value="<?= $max_price < 999999 ? $max_price : '' ?>"
                       class="w-1/2 px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
            </div>
            
            <select name="sort" class="px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Name (A-Z)</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="rating" <?= $sort == 'rating' ? 'selected' : '' ?>>Top Rated</option>
            </select>
            
            <button type="submit" class="md:col-span-2 lg:col-span-4 px-6 py-2 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200">
                Apply Filters
            </button>
        </form>
    </div>
    
    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if($products->num_rows > 0): ?>
            <?php while($product = $products->fetch_assoc()): ?>
                <div class="futuristic-card p-6 rounded-xl">
                    <img src="<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>" class="w-full h-48 object-cover rounded-lg mb-4">
                    
                    <h3 class="text-xl font-bold mb-2 text-neon-blue"><?= $product['name'] ?></h3>
                    <p class="text-slate-400 text-sm mb-2"><?= $product['brand'] ?></p>
                    
                    <div class="flex items-center mb-2">
                        <?php 
                        $rating = round($product['avg_rating'] ?? 0);
                        for($i = 1; $i <= 5; $i++): 
                        ?>
                            <span class="<?= $i <= $rating ? 'text-yellow-400' : 'text-gray-600' ?>">★</span>
                        <?php endfor; ?>
                        <span class="ml-2 text-sm text-slate-400">(<?= $product['review_count'] ?> reviews)</span>
                    </div>
                    
                    <p class="text-2xl font-bold mb-4"><?= formatCurrency($product['price']) ?></p>
                    
                    <div class="mb-4">
                        <p class="text-sm text-slate-400">CPU: <?= $product['cpu'] ?></p>
                        <p class="text-sm text-slate-400">RAM: <?= $product['ram'] ?></p>
                        <p class="text-sm text-slate-400">Storage: <?= $product['storage'] ?></p>
                    </div>
                    
                    <div class="flex gap-2">
                        <a href="/products/view.php?id=<?= $product['product_id'] ?>" 
                           class="flex-1 text-center px-4 py-2 bg-neon-blue/20 border border-neon-blue text-neon-blue rounded-lg hover:bg-neon-blue hover:text-white transition duration-200">
                            View Details
                        </a>
                        
                        <?php if($product['stock'] > 0): ?>
                            <form action="/cart/add.php" method="POST" class="flex-1">
                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="w-full px-4 py-2 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200">
                                    Add to Cart
                                </button>
                            </form>
                        <?php else: ?>
                            <button disabled class="flex-1 px-4 py-2 bg-gray-600 text-gray-400 rounded-lg cursor-not-allowed">
                                Out of Stock
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-12">
                <p class="text-2xl text-slate-400">No products found. Try adjusting your filters.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    // Laptop login toggle is in footer.php
    
    // Smooth scroll to products
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
    
    // Click laptop to open
    document.getElementById('laptop-login-container').addEventListener('click', function(e) {
        if (!e.target.closest('form') && !e.target.closest('button')) {
            toggleLogin();
        }
    });
</script>

<?php
include 'footer.php';
$conn->close();
?>