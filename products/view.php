<?php
// products/view.php - View Product Details with Reviews
require_once '../config.php';
$conn = getConnection();

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? AND is_active = 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: /index.php');
    exit();
}

// Get reviews
$reviews = $conn->query("SELECT r.*, u.name 
                         FROM reviews r 
                         JOIN users u ON r.user_id = u.user_id 
                         WHERE r.product_id = $product_id 
                         ORDER BY r.review_date DESC");

// Calculate average rating
$rating_result = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                               FROM reviews WHERE product_id = $product_id");
$rating_data = $rating_result->fetch_assoc();

include '../header.php';
?>

<section class="py-16">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Product Image -->
            <div class="futuristic-card p-6 rounded-xl">
                <img src="<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>" 
                     class="w-full h-96 object-cover rounded-lg">
            </div>
            
            <!-- Product Details -->
            <div>
                <h1 class="text-4xl font-bold mb-4 logo-glow"><?= $product['name'] ?></h1>
                <p class="text-xl text-slate-400 mb-4"><?= $product['brand'] ?></p>
                
                <!-- Rating -->
                <div class="flex items-center mb-6">
                    <?php 
                    $avg_rating = round($rating_data['avg_rating'] ?? 0);
                    for($i = 1; $i <= 5; $i++): 
                    ?>
                        <span class="text-2xl <?= $i <= $avg_rating ? 'text-yellow-400' : 'text-gray-600' ?>">★</span>
                    <?php endfor; ?>
                    <span class="ml-3 text-lg text-slate-400">
                        <?= number_format($rating_data['avg_rating'], 1) ?> 
                        (<?= $rating_data['total_reviews'] ?> reviews)
                    </span>
                </div>
                
                <div class="futuristic-card p-6 rounded-xl mb-6">
                    <p class="text-4xl font-bold mb-4 text-neon-blue"><?= formatCurrency($product['price']) ?></p>
                    
                    <div class="space-y-2 mb-6">
                        <p class="flex justify-between">
                            <span class="text-slate-400">CPU:</span>
                            <span class="font-semibold"><?= $product['cpu'] ?></span>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400">RAM:</span>
                            <span class="font-semibold"><?= $product['ram'] ?></span>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400">Storage:</span>
                            <span class="font-semibold"><?= $product['storage'] ?></span>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400">Screen:</span>
                            <span class="font-semibold"><?= $product['screen_size'] ?></span>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400">Stock:</span>
                            <span class="font-semibold <?= $product['stock'] > 0 ? 'text-green-400' : 'text-red-400' ?>">
                                <?= $product['stock'] > 0 ? $product['stock'] . ' Available' : 'Out of Stock' ?>
                            </span>
                        </p>
                    </div>
                    
                    <?php if($product['stock'] > 0): ?>
                        <form action="/cart/add.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                            <div class="flex gap-4 mb-4">
                                <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>"
                                       class="w-24 px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base text-center focus:outline-none focus:border-neon-blue">
                                <button type="submit" class="flex-1 py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200 text-lg font-semibold">
                                    Add to Cart
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <button disabled class="w-full py-3 bg-gray-600 text-gray-400 rounded-lg cursor-not-allowed text-lg font-semibold">
                            Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="futuristic-card p-6 rounded-xl">
                    <h3 class="text-xl font-bold mb-3 text-neon-blue">Description</h3>
                    <p class="text-slate-300 leading-relaxed"><?= nl2br($product['description']) ?></p>
                </div>
            </div>
        </div>
        
        <!-- Customer Reviews -->
        <div class="mt-12 futuristic-card p-6 rounded-xl">
            <h2 class="text-3xl font-bold mb-6 text-neon-blue">Customer Reviews</h2>
            
            <?php if($reviews->num_rows > 0): ?>
                <div class="space-y-6">
                    <?php while($review = $reviews->fetch_assoc()): ?>
                        <div class="border-b border-slate-700 pb-6 last:border-0">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="font-bold text-lg"><?= $review['name'] ?></h4>
                                    <div class="flex items-center">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <span class="<?= $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-600' ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <span class="text-sm text-slate-400">
                                    <?= date('M d, Y', strtotime($review['review_date'])) ?>
                                </span>
                            </div>
                            <p class="text-slate-300"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                            
                            <?php if(isLoggedIn() && $_SESSION['user_id'] == $review['user_id']): ?>
                                <div class="mt-3 flex gap-3">
                                    <a href="/lumen/reviews/update.php?id=<?= $review['review_id'] ?>" 
                                       class="text-sm text-neon-blue hover:underline">Edit Review</a>
                                    <a href="/lumen/reviews/delete.php?id=<?= $review['review_id'] ?>" 
                                       onclick="return confirm('Delete this review?')"
                                       class="text-sm text-red-400 hover:underline">Delete Review</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-slate-400 py-8">No reviews yet. Be the first to review this product!</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
include '../footer.php';
$conn->close();
?>