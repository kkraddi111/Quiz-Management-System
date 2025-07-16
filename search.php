<?php
session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$search_term = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$search_type = isset($_GET['type']) ? $_GET['type'] : 'courses';

?>

<h2>Search Results</h2>

<form action="search.php" method="get">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" required>
    <select name="type">
        <option value="courses" <?php echo $search_type == 'courses' ? 'selected' : ''; ?>>Courses</option>
        <option value="questions" <?php echo $search_type == 'questions' ? 'selected' : ''; ?>>Questions</option>
    </select>
    <button type="submit">Search</button>
</form>

<?php if ($search_term): ?>
    <?php if ($search_type == 'courses'): ?>
        <?php
        $courses = $conn->query("
            SELECT c.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
            FROM courses c
            LEFT JOIN reviews r ON c.id = r.course_id
            WHERE c.name LIKE '%$search_term%' OR c.description LIKE '%$search_term%'
            GROUP BY c.id
            ORDER BY c.name
        ");
        ?>
        <h3>Courses</h3>
        <?php if ($courses->num_rows > 0): ?>
            <div class="course-grid">
                <?php while ($course = $courses->fetch_assoc()): ?>
                    <!-- Display course card (similar to course-catalog.php) -->
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No courses found matching your search.</p>
        <?php endif; ?>
    <?php else: ?>
        <?php
        $questions = $conn->query("
            SELECT q.*, c.name AS course_name
            FROM questions q
            JOIN courses c ON q.course_id = c.id
            WHERE q.question_text LIKE '%$search_term%'
            ORDER BY c.name, q.id
        ");
        ?>
        <h3>Questions</h3>
        <?php if ($questions->num_rows > 0): ?>
            <ul>
                <?php while ($question = $questions->fetch_assoc()): ?>
                    <li>
                        <strong><?php echo $question['course_name']; ?>:</strong>
                        <?php echo $question['question_text']; ?>
                        (<?php echo ucfirst($question['difficulty']); ?>)
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No questions found matching your search.</p>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>