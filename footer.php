</main>
    <footer class="bg-primary-panel/80 mt-auto py-6 border-t border-neon-blue/20">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center text-slate-500 text-sm">
            <p>&copy; 2025 Lumen. All rights reserved. | **Copyright Infringement** notice applies to all content.</p>
        </div>
    </footer>

    <script>
        /**
         * Toggles the open/closed state of the interactive laptop login.
         */
        function toggleLogin() {
            const laptopBody = document.getElementById('laptop-body');
            const container = document.getElementById('laptop-login-container');
            const closeBtn = document.getElementById('close-btn');

            const isOpen = laptopBody.classList.toggle('open');
            
            // Adjust the container's perspective to make the 'open' state feel more centered
            if (isOpen) {
                // Focus the first form element (email input) after a short delay
                setTimeout(() => {
                    const emailInput = document.getElementById('email');
                    if (emailInput) emailInput.focus();
                }, 800); 
                container.style.transform = 'translateY(50px) scale(1.1)';
                closeBtn.classList.remove('hidden');
            } else {
                container.style.transform = 'translateY(0) scale(1)';
                closeBtn.classList.add('hidden');
            }
        }
    </script>

</body>
</html>