<?php
// user/register.php - User Registration
require_once '../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getConnection();
    
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $error = "Email already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, is_admin) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("sssss", $name, $email, $password, $phone, $address);
        
        if ($stmt->execute()) {
            $success = "Registration successful! You can now login.";
            header('refresh:2;url=login.php');
        } else {
            $error = "Registration failed: " . $conn->error;
        }
        $stmt->close();
    }
    $check->close();
    $conn->close();
}

include '../header.php';
?>

<section class="py-16">
    <div class="max-w-md mx-auto futuristic-card p-8 rounded-xl">
        <h1 class="text-3xl font-bold mb-6 text-center logo-glow">Create Account</h1>
        
        <?php if($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="bg-green-500/20 border border-green-500 text-green-200 px-4 py-3 rounded mb-4">
                <?= $success ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-slate-400 mb-2">Full Name</label>
                <input type="text" name="name" required
                       class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
            </div>
            
            <div>
                <label class="block text-slate-400 mb-2">Email</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
            </div>
            
            <div>
                <label class="block text-slate-400 mb-2">Password</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
            </div>
            
            <div>
                <label class="block text-slate-400 mb-2">Phone</label>
                <input type="tel" name="phone" required
                       class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
            </div>
            
            <div>
                <label class="block text-slate-400 mb-2">Address</label>
                <textarea name="address" rows="3" required
                          class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue"></textarea>
            </div>
            
            <button type="submit" class="w-full py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200">
                Register
            </button>
        </form>
        
        <p class="mt-6 text-center text-slate-400">
            Already have an account? <a href="login.php" class="text-neon-blue hover:underline">Login</a>
        </p>
    </div>
</section>

<?php include '../footer.php'; ?>