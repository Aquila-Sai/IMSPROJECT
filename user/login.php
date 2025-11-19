<?php
// user/login.php - User Login
require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getConnection();
    
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT user_id, name, email, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            if ($user['is_admin'] == 1) {
                header('Location: /admin/dashboard.php');
            } else {
                header('Location: /user/profile.php');
            }
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Invalid email or password!";
    }
    
    $stmt->close();
    $conn->close();
}

include '../header.php';
?>

<section class="py-16">
    <div class="max-w-md mx-auto futuristic-card p-8 rounded-xl">
        <h1 class="text-3xl font-bold mb-6 text-center logo-glow">Client Login</h1>
        
        <?php if($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-slate-400 mb-2">Email</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
            </div>
            
            <div>
                <label class="block text-slate-400 mb-2">Password</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
            </div>
            
            <button type="submit" class="w-full py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200">
                Login
            </button>
        </form>
        
        <p class="mt-6 text-center text-slate-400">
            Don't have an account? <a href="register.php" class="text-neon-blue hover:underline">Register</a>
        </p>
    </div>
</section>

<?php include '../footer.php'; ?>