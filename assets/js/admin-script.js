document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const menuItems = document.querySelectorAll('.sidebar-menu a');

    hamburger.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        this.innerHTML = sidebar.classList.contains('collapsed') 
            ? '<i class="fas fa-expand-arrows-alt"></i>' 
            : '<i class="fas fa-bars"></i>';
    });

    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Remove the preventDefault() call to allow normal link navigation
            menuItems.forEach(mi => mi.classList.remove('active'));
            this.classList.add('active');

            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                hamburger.innerHTML = '<i class="fas fa-expand-arrows-alt"></i>';
            }
        });
    });

    // Profile dropdown functionality
    const profileDropdown = document.querySelector('.profile-dropdown');
    const profileDropdownContent = document.querySelector('.profile-dropdown-content');

    if (profileDropdown && profileDropdownContent) {
        profileDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdownContent.style.display = profileDropdownContent.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function() {
            profileDropdownContent.style.display = 'none';
        });
    }

    // Edit profile modal functionality
    const editProfileModal = document.getElementById('editProfileModal');
    const editProfileBtn = document.querySelector('.profile-dropdown-content a[onclick="editProfile()"]');
    const closeBtn = document.querySelector('.close-button');

    if (editProfileBtn && editProfileModal) {
        editProfileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            editProfileModal.style.display = 'block';
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            editProfileModal.style.display = 'none';
        });
    }

    window.addEventListener('click', function(e) {
        if (e.target === editProfileModal) {
            editProfileModal.style.display = 'none';
        }
    });

    // Edit profile form submission
    const editProfileForm = document.getElementById('editProfileForm');
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Add your form submission logic here
            console.log('Profile update submitted');
            editProfileModal.style.display = 'none';
        });
    }
});