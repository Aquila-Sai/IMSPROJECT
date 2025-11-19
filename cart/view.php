<?php
// cart/view.php - View Shopping Cart
require_once '../config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Get cart items
$cart_items = $conn->query("SELECT c.*, p.name, p.brand, p.price, p.image_url, p.stock 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.product_id 
                            WHERE c.user_id = $user_id AND p.is_active = 1");

// Calculate total
$total = 0;

include '../header.php';
?>

<section class="py-16">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-bold mb-8 logo-glow">Shopping Cart</h1>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="bg-green-500/20 border border-green-500 text-green-200 px-4 py-3 rounded mb-4">
                <?= $_SESSION['message'] ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded mb-4">
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if($cart_items->num_rows > 0): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <?php while($item = $cart_items->fetch_assoc()): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                        <div class="futuristic-card p-6 rounded-xl">
                            <div class="flex gap-6">
                                <img src="<?= $item['image_url'] ?>" alt="<?= $item['name'] ?>" 
                                     class="w-32 h-32 object-cover rounded-lg">
                                
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold mb-2"><?= $item['name'] ?></h3>
                                    <p class="text-slate-400 mb-2"><?= $item['brand'] ?></p>
                                    <p class="text-2xl font-bold text-neon-blue mb-4"><?= formatCurrency($item['price']) ?></p>
                                    
                                    <div class="flex items-center gap-4">
                                        <form action="/cart/update.php" method="POST" class="flex items-center gap-2">
                                            <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" 
                                                   min="1" max="<?= $item['stock'] ?>"
                                                   class="w-20 px-3 py-1 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base text-center focus:outline-none focus:border-neon-blue">
                                            <button type="submit" class="px-4 py-1 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition text-sm">
                                                Update
                                            </button>
                                        </form>
                                        
                                        <a href="/lumen/cart/remove.php?id=<?= $item['cart_id'] ?>" 
                                           onclick="return confirm('Remove this item from cart?')"
                                           class="text-red-400 hover:underline">Remove</a>
                                    </div>
                                    
                                    <p class="text-sm text-slate-400 mt-2">Stock: <?= $item['stock'] ?> available</p>
                                </div>
                                
                                <div class="text-right">
                                    <p class="text-slate-400 text-sm mb-1">Subtotal</p>
                                    <p class="text-2xl font-bold"><?= formatCurrency($subtotal) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="futuristic-card p-6 rounded-xl sticky top-24">
                        <h2 class="text-2xl font-bold mb-6 text-neon-blue">Order Summary</h2>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Subtotal</span>
                                <span class="font-semibold"><?= formatCurrency($total) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Shipping</span>
                                <span class="font-semibold text-green-400">FREE</span>
                            </div>
                            <div class="border-t border-slate-700 pt-3 flex justify-between text-xl">
                                <span class="font-bold">Total</span>
                                <span class="font-bold text-neon-blue"><?= formatCurrency($total) ?></span>
                            </div>
                        </div>
                        
                        <a href="/lumen/orders/checkout.php" class="block w-full py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200 text-center text-lg font-semibold">
                            Proceed to Checkout
                        </a>
                        
                        <a href="/lumen/index.php" class="block w-full mt-3 py-2 text-center text-neon-blue hover:underline">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="futuristic-card p-12 rounded-xl text-center">
                <p class="text-2xl text-slate-400 mb-6">Your cart is empty</p>
                <a href="/lumen/index.php" class="inline-block px-8 py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition">
                    Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
include '../footer.php';
$conn->close();
?>