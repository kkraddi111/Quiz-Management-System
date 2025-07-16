<div class="top-bar">
    <div class="hamburger" id="hamburger">
        <i class="fas fa-bars"></i>
    </div>
    <div class="quiz-hub-header">
        <i class="fas fa-graduation-cap"></i> Quiz<span class="highlight">Hub</span>
    </div>
    <div class="user-actions">
        <div class="profile-dropdown">
            <div class="profile-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <span><?php echo htmlspecialchars($admin_name); ?></span>
            <i class="fas fa-caret-down"></i>
            <div class="profile-dropdown-content">
                <a href="edit-profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</div> 