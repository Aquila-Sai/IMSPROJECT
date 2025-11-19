<?php
// orders/checkout.php - Checkout Process
require_once '../config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Get user data
$user_stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Get cart items
$cart_items = $conn->query("SELECT c.*, p.name, p.brand, p.price, p.image_url, p.stock 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.product_id 
                            WHERE c.user_id = $user_id AND p.is_active = 1");

if ($cart_items->num_rows == 0) {
    header('Location: /cart/view.php');
    exit();
}

$total = 0;

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = sanitize($_POST['shipping_address']);
    $payment_method = sanitize($_POST['payment_method']);
    $discount_code = sanitize($_POST['discount_code'] ?? '');
    
    // Calculate total again
    $cart_check = $conn->query("SELECT c.*, p.price, p.stock 
                                FROM cart c 
                                JOIN products p ON c.product_id = p.product_id 
                                WHERE c.user_id = $user_id AND p.is_active = 1");
    
    $order_total = 0;
    $items_valid = true;
    
    while($item = $cart_check->fetch_assoc()) {
        if ($item['quantity'] > $item['stock']) {
            $_SESSION['error'] = "Some items are out of stock!";
            $items_valid = false;
            break;
        }
        $order_total += $item['price'] * $item['quantity'];
    }
    
    if ($items_valid) {
        // Create order
        $conn->begin_transaction();
        
        try {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, status, order_date) 
                                   VALUES (?, ?, ?, ?, 'Processing', NOW())");
            $stmt->bind_param("idss", $user_id, $order_total, $shipping_address, $payment_method);
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();
            
            // Add order items and update stock
            $cart_items2 = $conn->query("SELECT * FROM cart WHERE user_id = $user_id");
            
            while($cart_item = $cart_items2->fetch_assoc()) {
                // Get product price
                $price_query = $conn->query("SELECT price FROM products WHERE product_id = {$cart_item['product_id']}");
                $price_data = $price_query->fetch_assoc();
                
                // Insert order item
                $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $item_stmt->bind_param("iiid", $order_id, $cart_item['product_id'], $cart_item['quantity'], $price_data['price']);
                $item_stmt->execute();
                $item_stmt->close();
                
                // Update stock
                $stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
                $stock_stmt->bind_param("ii", $cart_item['quantity'], $cart_item['product_id']);
                $stock_stmt->execute();
                $stock_stmt->close();
            }
            
            // Clear cart
            $conn->query("DELETE FROM cart WHERE user_id = $user_id");
            
            $conn->commit();
            
            $_SESSION['message'] = "Order placed successfully! Order ID: #$order_id";
            header('Location: /orders/view.php?id=' . $order_id);
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Order failed: " . $e->getMessage();
        }
    }
}

include '../header.php';
?>

<section class="py-16">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-bold mb-8 logo-glow">Checkout</h1>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded mb-4">
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-2 space-y-6">
                <div class="futuristic-card p-6 rounded-xl">
                    <h2 class="text-2xl font-bold mb-4 text-neon-blue">Shipping Information</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-slate-400 mb-2">Full Name</label>
                            <input type="text" value="<?= $user['name'] ?>" disabled
                                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-gray-400 cursor-not-allowed">
                        </div>
                        
                        <div>
                            <label class="block text-slate-400 mb-2">Phone Number</label>
                            <input type="text" value="<?= $user['phone'] ?>" disabled
                                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-gray-400 cursor-not-allowed">
                        </div>
                        
                        <div>
                            <label class="block text-slate-400 mb-2">Shipping Address *</label>
                            <textarea name="shipping_address" rows="3" required
                                      class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue"><?= $user['address'] ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="futuristic-card p-6 rounded-xl">
                    <h2 class="text-2xl font-bold mb-4 text-neon-blue">Payment Method</h2>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-4 border border-neon-blue/30 rounded-lg cursor-pointer hover:bg-primary-panel/50">
                            <input type="radio" name="payment_method" value="Credit Card" required class="w-5 h-5">
                            <span>Credit Card</span>
                        </label>
                        
                        <label class="flex items-center gap-3 p-4 border border-neon-blue/30 rounded-lg cursor-pointer hover:bg-primary-panel/50">
                            <input type="radio" name="payment_method" value="GCash" required class="w-5 h-5">
                            <span>GCash</span>
                        </label>
                        
                        <label class="flex items-center gap-3 p-4 border border-neon-blue/30 rounded-lg cursor-pointer hover:bg-primary-panel/50">
                            <input type="radio" name="payment_method" value="Cash on Delivery" required class="w-5 h-5">
                            <span>Cash on Delivery</span>
                        </label>
                    </div>
                </div>
                
                <div class="futuristic-card p-6 rounded-xl">
                    <h2 class="text-2xl font-bold mb-4 text-neon-blue">Discount Code (Optional)</h2>
                    <input type="text" name="discount_code" placeholder="Enter discount code"
                           class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="futuristic-card p-6 rounded-xl sticky top-24">
                    <h2 class="text-2xl font-bold mb-6 text-neon-blue">Order Summary</h2>
                    
                    <div class="space-y-4 mb-6 max-h-96 overflow-y-auto">
                        <?php 
                        $cart_items->data_seek(0);
                        while($item = $cart_items->fetch_assoc()): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                        ?>
                            <div class="flex gap-3 pb-4 border-b border-slate-700">
                                <img src="<?= $item['image_url'] ?>" alt="<?= $item['name'] ?>" class="w-16 h-16 object-cover rounded">
                                <div class="flex-1">
                                    <p class="font-semibold text-sm"><?= $item['name'] ?></p>
                                    <p class="text-xs text-slate-400">Qty: <?= $item['quantity'] ?></p>
                                    <p class="text-sm font-bold text-neon-blue"><?= formatCurrency($subtotal) ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
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
                    
                    <button type="submit" class="w-full py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200 text-lg font-semibold">
                        Place Order
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<?php
include '../footer.php';
$conn->close();
?>