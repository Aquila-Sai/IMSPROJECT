<?php
// reviews/update.php - Update Review with Bad Word Filter
require_once '../config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$review_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get review - verify ownership
$stmt = $conn->prepare("SELECT r.*, p.name, p.image_url 
                        FROM reviews r 
                        JOIN products p ON r.product_id = p.product_id 
                        WHERE r.review_id = ? AND r.user_id = ?");
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$review = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$review) {
    header('Location: /user/profile.php');
    exit();
}

$success = '';
$error = '';

// Bad words filter function
function filterBadWords($text) {
    $badWords = [
        'badword1', 'badword2', 'damn', 'hell', 'crap', 'shit', 'fuck', 
        'bitch', 'ass', 'asshole', 'bastard', 'stupid', 'idiot', 'dumb'
    ];
    
    foreach ($badWords as $word) {
        $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
        $replacement = str_repeat('*', strlen($word));
        $text = preg_replace($pattern, $replacement, $text);
    }
    
    return $text;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    
    // Apply bad word filter
    $filtered_comment = filterBadWords($comment);
    
    $stmt = $conn->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE review_id = ? AND user_id = ?");
    $stmt->bind_param("isii", $rating, $filtered_comment, $review_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Review updated successfully!";
        header('Location: /products/view.php?id=' . $review['product_id']);
        exit();
    } else {
        $error = "Failed to update review: " . $conn->error;
    }
    $stmt->close();
}

include '../header.php';
?>

<section class="py-16">
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold logo-glow">Edit Your Review</h1>
            <a href="/lumen/products/view.php?id=<?= $review['product_id'] ?>" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition">
                ← Back to Product
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
                <img src="<?= $review['image_url'] ?>" alt="<?= $review['name'] ?>" 
                     class="w-32 h-32 object-cover rounded-lg">
                <div>
                    <h2 class="text-2xl font-bold mb-2"><?= $review['name'] ?></h2>
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
                            <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" 
                                   <?= $review['rating'] == $i ? 'checked' : '' ?> class="hidden">
                            <label for="star<?= $i ?>" class="text-5xl cursor-pointer <?= $review['rating'] >= $i ? 'text-yellow-400' : 'text-gray-600' ?> hover:text-yellow-400 transition star-label">★</label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div>
                    <label class="block text-slate-400 mb-2">Your Review *</label>
                    <textarea name="comment" rows="6" placeholder="Share your experience with this product..."
                              class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue"><?= htmlspecialchars($review['comment']) ?></textarea>
                    <p class="text-xs text-slate-500 mt-1">Note: Inappropriate language will be automatically filtered</p>
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200 text-lg font-semibold">
                        Update Review
                    </button>
                    <a href="/lumen/reviews/delete.php?id=<?= $review_id ?>" 
                       onclick="return confirm('Are you sure you want to delete this review?')"
                       class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-center">
                        Delete Review
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
// Star rating interaction (same as create)
document.querySelectorAll('.star-label').forEach((star, index) => {
    star.addEventListener('click', function() {
        document.querySelectorAll('.star-label').forEach(s => s.classList.remove('text-yellow-400'));
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

<?php include '../footer.php'; $conn->close(); ?>