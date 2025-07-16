<div class="sidebar">
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="manage-teachers.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage-teachers.php' ? 'class="active"' : ''; ?>><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a></li>
        <li><a href="manage-students.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage-students.php' ? 'class="active"' : ''; ?>><i class="fas fa-user-graduate"></i> Manage Students</a></li>
        <li><a href="manage-courses.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage-courses.php' ? 'class="active"' : ''; ?>><i class="fas fa-book"></i> Manage Courses</a></li>
        <li><a href="manage-questions.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage-questions.php' ? 'class="active"' : ''; ?>><i class="fas fa-question-circle"></i> Manage Questions</a></li>
    </ul>
</div>