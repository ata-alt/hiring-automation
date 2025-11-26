<?php
// process_assessment.php
session_start();

// Database configuration
$db_host = 'localhost';
$db_name = 'leadership_assessment';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
$createTables = "
CREATE TABLE IF NOT EXISTS assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_time INT,
    total_score DECIMAL(5,2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'pending',
    candidate_email VARCHAR(255),
    candidate_name VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS assessment_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT,
    question_number INT,
    question_type VARCHAR(50),
    answer TEXT,
    score DECIMAL(5,2) DEFAULT NULL,
    max_points INT,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE
);
";

try {
    $pdo->exec($createTables);
} catch(PDOException $e) {
    // Tables already exist
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Question configuration
    $questions = [
        'q1' => ['type' => 'essay', 'points' => 10, 'correct' => null],
        'q2' => ['type' => 'multiple_choice', 'points' => 2, 'correct' => 'B'],
        'q3' => ['type' => 'short_answer', 'points' => 8, 'correct' => null],
        'q4' => ['type' => 'multiple_choice', 'points' => 2, 'correct' => 'B'],
        'q5' => ['type' => 'short_answer', 'points' => 5, 'correct' => null],
        'q6' => ['type' => 'essay', 'points' => 15, 'correct' => null],
        'q7' => ['type' => 'multiple_choice', 'points' => 2, 'correct' => 'B'],
        'q8' => ['type' => 'short_answer', 'points' => 6, 'correct' => null],
        'q9' => ['type' => 'short_answer', 'points' => 8, 'correct' => null],
        'q10' => ['type' => 'short_answer', 'points' => 5, 'correct' => null],
        'q11' => ['type' => 'multiple_choice', 'points' => 1, 'correct' => 'B'],
        'q12' => ['type' => 'short_answer', 'points' => 3, 'correct' => null],
        'q13' => ['type' => 'essay', 'points' => 10, 'correct' => null],
        'q14' => ['type' => 'multiple_select', 'points' => 3, 'correct' => ['A', 'B', 'C', 'D', 'F']],
        'q15' => ['type' => 'short_answer', 'points' => 5, 'correct' => null],
        'q16' => ['type' => 'essay', 'points' => 10, 'correct' => null],
        'q17' => ['type' => 'essay', 'points' => 8, 'correct' => null],
        'q18' => ['type' => 'short_answer', 'points' => 3, 'correct' => null],
        'q19' => ['type' => 'short_answer', 'points' => 2, 'correct' => null],
        'q20' => ['type' => 'short_answer', 'points' => 6, 'correct' => null],
        'q21' => ['type' => 'short_answer', 'points' => 4, 'correct' => null]
    ];
    
    $total_time = isset($_POST['total_time']) ? (int)$_POST['total_time'] : 0;
    
    // Insert main assessment record
    $stmt = $pdo->prepare("INSERT INTO assessments (total_time, candidate_email, candidate_name) VALUES (?, ?, ?)");
    $stmt->execute([$total_time, '', '']);
    $assessment_id = $pdo->lastInsertId();
    
    $auto_score = 0;
    $total_possible = 0;
    
    // Process each answer
    foreach ($questions as $q_num => $q_config) {
        $answer = '';
        $score = null;
        
        if ($q_config['type'] === 'multiple_select') {
            // Handle checkbox answers
            if (isset($_POST[$q_num]) && is_array($_POST[$q_num])) {
                $answer = implode(',', $_POST[$q_num]);
                
                // Auto-score multiple select
                $submitted = $_POST[$q_num];
                $correct = $q_config['correct'];
                
                $correct_count = count(array_intersect($submitted, $correct));
                $incorrect_count = count($submitted) - $correct_count;
                $missed_count = count($correct) - $correct_count;
                
                // 0.5 points per correct answer, deduct for incorrect selections
                $score = max(0, ($correct_count * 0.5) - ($incorrect_count * 0.5));
                $auto_score += $score;
            }
        } else {
            $answer = isset($_POST[$q_num]) ? trim($_POST[$q_num]) : '';
            
            // Auto-score multiple choice
            if ($q_config['type'] === 'multiple_choice' && $q_config['correct']) {
                if ($answer === $q_config['correct']) {
                    $score = $q_config['points'];
                    $auto_score += $score;
                } else {
                    $score = 0;
                }
            }
        }
        
        $total_possible += $q_config['points'];
        
        // Insert answer
        $stmt = $pdo->prepare("
            INSERT INTO assessment_answers 
            (assessment_id, question_number, question_type, answer, score, max_points) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $q_number = (int)str_replace('q', '', $q_num);
        $stmt->execute([
            $assessment_id,
            $q_number,
            $q_config['type'],
            $answer,
            $score,
            $q_config['points']
        ]);
    }
    
    // Update total score (only auto-scored questions for now)
    $stmt = $pdo->prepare("UPDATE assessments SET total_score = ? WHERE id = ?");
    $stmt->execute([$auto_score, $assessment_id]);
    
    // Store assessment ID in session
    $_SESSION['assessment_id'] = $assessment_id;
    
    // Redirect to results page
    header("Location: view_results.php?id=" . $assessment_id);
    exit;
}

// If accessed directly without POST, redirect to index
header("Location: index.html");
exit;
?>