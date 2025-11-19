<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumen - Interactive Sales Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Deep space, neon, and high-contrast text
                        'space-dark': '#010414',      // Ultra deep navy/black
                        'primary-panel': '#0e172a',   // Slate 900
                        'neon-blue': '#4c65f8',       // Vibrant primary accent
                        'neon-glow': 'rgba(76, 101, 248, 0.5)', // Glow color
                        'text-base': '#e2e8f0',       // Off-white text
                    },
                    /* Removed glowpulse definition from Tailwind config to move it to custom CSS for text-shadow */
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link rel="stylesheet" href="design.css"> 
</head>

<body class="bg-space-dark font-sans flex flex-col min-h-screen">

    <header class="bg-primary-panel/90 backdrop-blur-sm sticky top-0 z-50 shadow-lg border-b border-neon-blue/20">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <a href="#" class="text-3xl font-extrabold tracking-wider hover:text-text-base transition duration-300 logo-glow">
                Lumen
            </a>
            
            <nav class="hidden md:flex space-x-8 text-lg font-medium">
                <a href="#" class="text-text-base hover:text-neon-blue transition duration-200">Products</a>
                <a href="#" class="text-text-base hover:text-neon-blue transition duration-200">Reviews</a>
                <a href="/lumen/user/login.php" class="px-4 py-2 bg-neon-blue rounded-lg text-white hover:bg-blue-600 transition duration-200 shadow-md shadow-neon-blue/30">
                    Client Portal
                </a>
            </nav>
            
            <button class="md:hidden text-text-base p-2 rounded-md hover:bg-primary-panel">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-12"></main>