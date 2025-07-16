document.addEventListener('DOMContentLoaded', function() {
    // Password strength meter
    const passwordInput = document.getElementById('password');
    const passwordStrength = document.getElementById('password-strength');

    if (passwordInput && passwordStrength) {
        passwordInput.addEventListener('input', function() {
            const strength = calculatePasswordStrength(this.value);
            passwordStrength.textContent = strength;
            passwordStrength.className = strength.toLowerCase();
        });
    }

    // Function to calculate password strength
    function calculatePasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[$@#&!]+/)) strength++;

        switch (strength) {
            case 0:
            case 1:
                return "Weak";
            case 2:
                return "Fair";
            case 3:
                return "Good";
            case 4:
                return "Strong";
            case 5:
                return "Very Strong";
        }
    }

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });

            if (!isValid) {
                event.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });

    // Dynamic search suggestions
    const searchInput = document.getElementById('search-input');
    const searchSuggestions = document.getElementById('search-suggestions');

    if (searchInput && searchSuggestions) {
        searchInput.addEventListener('input', function() {
            if (this.value.length > 2) {
                // Here you would typically make an AJAX call to your server
                // For demonstration, we'll just show some dummy suggestions
                searchSuggestions.innerHTML = `
                    <li>Suggestion 1</li>
                    <li>Suggestion 2</li>
                    <li>Suggestion 3</li>
                `;
                searchSuggestions.style.display = 'block';
            } else {
                searchSuggestions.style.display = 'none';
            }
        });
    }

    // Sidebar toggle functionality
    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');

    if (hamburger && sidebar && mainContent) {
        hamburger.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    }
});