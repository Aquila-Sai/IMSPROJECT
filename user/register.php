<?php
// user/register.php - User Registration
require_once '../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getConnection();
    
    // Server-side validation
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    $errors = [];
    
    // Name validation
    if (empty($name)) {
        $errors[] = "Name is required";
    } elseif (strlen($name) < 3) {
        $errors[] = "Name must be at least 3 characters";
    }
    
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
    
    // Phone validation
    if (empty($phone)) {
        $errors[] = "Phone is required";
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $errors[] = "Phone must be 10-11 digits";
    }
    
    // Address validation
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    if (empty($errors)) {
    
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
    } else {
        $error = implode("<br>", $errors);
    }
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
        
        <form method="POST" class="space-y-4" id="registerForm">
            <div>
                <label class="block text-slate-400 mb-2">Full Name</label>
                <input type="text" name="name" id="name"
                       class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                <span class="error-message text-red-400 text-sm hidden"></span>
            </div>
            
            <div>
                <label class="block text-slate-400 mb-2">Email</label>
                <input type="email" name="email" id="email"
                       class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                <span class="error-message text-red-400 text-sm hidden"></span>
            </div>
            
            <div>
                <label class="block text-slate-400 mb-2">Password</label>
                <input type="password" name="password" id="password"
                       class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                <span class="error-message text-red-400 text-sm hidden"></span>
            </div>
            
            <div>
                <label class="block text-slate-400 mb-2">Phone</label>
                <input type="tel" name="phone" id="phone"
                       class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue">
                <span class="error-message text-red-400 text-sm hidden"></span>
            </div>
            
            <div>
                <label class="block text-slate-400 mb-2">Address</label>
                <textarea name="address" id="address" rows="3"
                          class="w-full px-4 py-2 bg-space-dark border border-neon-blue/30 rounded-lg text-text-base focus:outline-none focus:border-neon-blue"></textarea>
                <span class="error-message text-red-400 text-sm hidden"></span>
            </div>
            
            <button type="submit" class="w-full py-3 bg-neon-blue text-white rounded-lg hover:bg-blue-600 transition duration-200">
                Register
            </button>
        </form>
        
        <p class="mt-6 text-center text-slate-400">
            Already have an account? <a href="/lumen/userlogin.php" class="text-neon-blue hover:underline">Login</a>
        </p>
    </div>
</section>

<script>
// Form validation without HTML5 validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    let isValid = true;
    
    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(span => {
        span.classList.add('hidden');
        span.textContent = '';
    });
    
    // Name validation
    const name = document.getElementById('name');
    const nameValue = name.value.trim();
    if (nameValue === '') {
        showError(name, 'Name is required');
        isValid = false;
    } else if (nameValue.length < 3) {
        showError(name, 'Name must be at least 3 characters');
        isValid = false;
    }
    
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
    
    // Phone validation
    const phone = document.getElementById('phone');
    const phoneValue = phone.value.trim();
    const phoneRegex = /^[0-9]{10,11}$/;
    if (phoneValue === '') {
        showError(phone, 'Phone is required');
        isValid = false;
    } else if (!phoneRegex.test(phoneValue)) {
        showError(phone, 'Phone must be 10-11 digits');
        isValid = false;
    }
    
    // Address validation
    const address = document.getElementById('address');
    const addressValue = address.value.trim();
    if (addressValue === '') {
        showError(address, 'Address is required');
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
document.querySelectorAll('#registerForm input, #registerForm textarea').forEach(input => {
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