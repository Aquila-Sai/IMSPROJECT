<?php
// reviews/create.php - Create Product Review
require_once '../config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Verify user ordered this product
$verify = $conn->query("SELECT oi.* FROM order_items oi 
                        JOIN orders o ON oi.order_id = o.order_id 
                        WHERE o.user_id = $user_id 
                        AND oi.product_id = $product_id 
                        AND o.status = 'Delivered'
                        AND o.order_id = $order_id");

if ($verify->num_rows == 0) {
    header('Location: /user/profile.php');
    exit();
}

// Check if already reviewed
$check = $conn->query("SELECT review_id FROM reviews WHERE user_id = $user_id AND product_id = $product_id");
if ($check->num_rows > 0) {
    $_SESSION['error'] = "You have already reviewed this product!";
    header('Location: /orders/view.php?id=' . $order_id);
    exit();
}

// Get product details
$product = $conn->query("SELECT * FROM products WHERE product_id = $product_id")->fetch_assoc();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = intval($_POST['rating']);
    $comment = sanitize($_POST['comment']);
    
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment, review_date) 
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiis", $user_id, $product_id, $rating, $comment);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Review submitted successfully!";
        header('Location: /products/view.php?id=' . $product_id);
        exit();
    } else {
        $error = "Failed to submit review: " . $conn->error;
    }
    $stmt->close();
}

include '../header.php';
?>

<section class="py-16">
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold logo-glow">Write a Review</h1>
            <a href="/orders/view.php?id=<?= $order_id ?>" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition">
                ← Back to Order
            </a>
        </div>
        
        <?php if($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <!-- Product Info -->
        <div class="futuristic-card p-6 rounded-xl mb-6">
            <div class="flex gap-6">
                <img src="<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>" 
                     class="w-32 h-32 object-cover rounded-lg">
                <div>
                    <h2 class="text-2xl font-bold mb-2"><?= $product['name'] ?></h2>
                    <p class="text-slate-400"><?= $product['brand'] ?></p>
                </div>
            </div>
        </div>
        
        <!-- Review Form -->
        <div class="futuristic-card p-8 rounded-xl">
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-slate-400 mb-4">Rating *</label>
                    <div class="flex gap-2" id="star-rating">
                        <?php for($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" class="hidden" required>
                            <label for="star<?= $i ?>" class="text-5xl cursor-pointer text-gray-600 hover:text-yellow-400 transition star-label">★</label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div>
                    <label class="block text-slate-400 mb-2">Your Review *</label>
                    <textarea name="comment" rows="6" required placeholder="Share your experience with this product..."
                              class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue"></textarea>
                </div>
                
                <button type="submit" class="w-full py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200 text-lg font-semibold">
                    Submit Review
                </button>
            </form>
        </div>
    </div>
</section>

<script>
// Star rating interaction
document.querySelectorAll('.star-label').forEach((star, index) => {
    star.addEventListener('click', function() {
        // Reset all stars
        document.querySelectorAll('.star-label').forEach(s => s.classList.remove('text-yellow-400'));
        
        // Highlight selected and all previous stars
        for(let i = 0; i <= index; i++) {
            document.querySelectorAll('.star-label')[i].classList.add('text-yellow-400');
        }
    });
    
    star.addEventListener('mouseenter', function() {
        for(let i = 0; i <= index; i++) {
            document.querySelectorAll('.star-label')[i].classList.add('text-yellow-400');
        }
    });
});

document.getElementById('star-rating').addEventListener('mouseleave', function() {
    // Keep only checked stars highlighted
    const checked = document.querySelector('input[name="rating"]:checked');
    document.querySelectorAll('.star-label').forEach(s => s.classList.remove('text-yellow-400'));
    
    if(checked) {
        const value = parseInt(checked.value);
        for(let i = 0; i < 6 - value; i++) {
            document.querySelectorAll('.star-label')[i].classList.add('text-yellow-400');
        }
    }
});
</script>

<?php
include '../footer.php';
$conn->close();
?>