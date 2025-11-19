<?php
// user/login.php - User Login
require_once '../config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: /admin/dashboard.php');
    } else {
        header('Location: /user/profile.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Server-side validation
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $errors = [];
    
    // Email validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Password validation
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if (empty($errors)) {
        $conn = getConnection();
        
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
    } else {
        $error = implode("<br>", $errors);
    }
}

include 'header.php';
?>

<section class="py-16 flex items-center justify-center min-h-[80vh]">
    <div class="max-w-md w-full mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-5xl font-extrabold mb-4 logo-glow">Welcome Back</h1>
            <p class="text-xl text-slate-300">Login to your Lumen account</p>
        </div>
        
        <?php if($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded mb-6">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <div class="futuristic-card p-8 rounded-xl">
            <h2 class="text-2xl font-bold mb-6 text-center text-neon-blue">Client Login</h2>
            
            <form id="loginForm" method="POST" class="space-y-5">
                <div>
                    <label class="block text-slate-400 mb-2">Email Address</label>
                    <input type="email" name="email" id="email" placeholder="Enter your email" 
                           class="w-full px-4 py-3 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue focus:ring-2 focus:ring-neon-blue/50 transition">
                    <span class="error-message text-red-400 text-sm hidden block mt-1"></span>
                </div>
                
                <div>
                    <label class="block text-slate-400 mb-2">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" 
                           class="w-full px-4 py-3 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue focus:ring-2 focus:ring-neon-blue/50 transition">
                    <span class="error-message text-red-400 text-sm hidden block mt-1"></span>
                </div>
                
                <button type="submit" class="w-full py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200 font-semibold text-lg shadow-lg shadow-neon-blue/30 hover:shadow-neon-blue/50">
                    Login
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-slate-400">
                    Don't have an account? 
                    <a href="/lumen/user/register.php" class="text-neon-blue hover:underline font-semibold">Register here</a>
                </p>
            </div>
        </div>
        
        <div class="text-center mt-8">
            <a href="/lumen/index.php" class="text-neon-blue hover:underline text-lg inline-flex items-center gap-2">
                <span>←</span> Back to Products
            </a>
        </div>
    </div>
</section>

<script>
// Form validation (NO HTML5 validation)
document.getElementById('loginForm').addEventListener('submit', function(e) {
    let isValid = true;
    
    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(span => {
        span.classList.add('hidden');
        span.textContent = '';
    });
    
    // Email validation
    const email = document.getElementById('email');
    const emailValue = email.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (emailValue === '') {
        showError(email, 'Email is required');
        isValid = false;
    } else if (!emailRegex.test(emailValue)) {
        showError(email, 'Please enter a valid email address');
        isValid = false;
    }
    
    // Password validation
    const password = document.getElementById('password');
    const passwordValue = password.value;
    
    if (passwordValue === '') {
        showError(password, 'Password is required');
        isValid = false;
    } else if (passwordValue.length < 6) {
        showError(password, 'Password must be at least 6 characters');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
    }
});

function showError(input, message) {
    const errorSpan = input.nextElementSibling;
    errorSpan.textContent = message;
    errorSpan.classList.remove('hidden');
    input.classList.add('border-red-500');
}

// Remove error on input
document.querySelectorAll('#loginForm input').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('border-red-500');
        const errorSpan = this.nextElementSibling;
        if (errorSpan && errorSpan.classList.contains('error-message')) {
            errorSpan.classList.add('hidden');
        }
    });
});
</script>

<?php include '../footer.php'; ?>