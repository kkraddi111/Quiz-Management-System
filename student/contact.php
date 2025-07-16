<?php
session_start();
require_once '../config/db.php';

$page_title = "Contact Us";
require_once '../includes/student_header.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Insert into contact_messages table
        $insert_query = "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sss", $name, $email, $message);
        
        if ($stmt->execute()) {
            $success_message = "Thank you for your message! We'll get back to you soon.";
            // Clear form data after successful submission
            $name = $email = $subject = $message = '';
        } else {
            $error_message = "Error sending message. Please try again.";
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0"><i class="fas fa-envelope"></i> Contact Us</h2>
                </div>
                <div class="card-body">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" 
                                      required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card mt-4 shadow">
                <div class="card-body">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Contact Information</h3>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="contact-info">
                                <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                                <h4>Address</h4>
                                <p>123 Education Street<br>Learning City, ED 12345</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="contact-info">
                                <i class="fas fa-phone fa-2x text-primary"></i>
                                <h4>Phone</h4>
                                <p>+1 (555) 123-4567</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="contact-info">
                                <i class="fas fa-envelope fa-2x text-primary"></i>
                                <h4>Email</h4>
                                <p>support@quizhub.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.contact-info {
    text-align: center;
    padding: 1rem;
}

.contact-info i {
    margin-bottom: 1rem;
}

.contact-info h4 {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.contact-info p {
    color: #666;
    margin-bottom: 0;
}

.card {
    border: none;
}

.card-header {
    border-bottom: none;
    border-radius: 0.5rem 0.5rem 0 0;
}

.btn i {
    margin-right: 0.5rem;
}
</style>

<?php require_once '../includes/footer.php'; ?>