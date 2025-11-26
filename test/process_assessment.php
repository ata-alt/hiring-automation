<?php
// Suppress any output before headers
ob_start();

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

if (ob_get_level()) ob_clean();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'u947717806_it_assessment');
define('DB_PASS', '#fXfdcGe^H09');
define('DB_NAME', 'u947717806_it_assessment');

define('ADMIN_EMAIL', 'rajesh@fcilondon.co.uk');
define('SEND_EMAIL_NOTIFICATIONS', true);
define('FROM_EMAIL', 'noreply@fcicontracts.co.uk');
define('FROM_NAME', 'IT Assessment System');

define('PASSING_SCORE_PERCENTAGE', 60);
define('EXCELLENT_SCORE_PERCENTAGE', 80);

// Define questions on server side to avoid sending them in POST
function getQuestions() {
    return [
        ['id' => 1, 'category' => 'Python', 'type' => 'mcq', 'points' => 10, 'correctAnswer' => 1, 'question' => 'What\'s the output of this code?', 'options' => ['[1]\n[1]\n[1]', '[1]\n[1, 1]\n[1, 1, 1]', 'Error', '[1, 1, 1]\n[1, 1, 1]\n[1, 1, 1]']],
        ['id' => 2, 'category' => 'Python', 'type' => 'mcq', 'points' => 10, 'correctAnswer' => 1, 'question' => 'Which approach correctly handles race condition?', 'options' => ['Use time.sleep()', 'Use threading.Lock()', 'Declare counter as volatile', 'Use asyncio']],
        ['id' => 3, 'category' => 'Python', 'type' => 'code', 'points' => 12, 'correctAnswer' => 'Implement cache eviction using collections.OrderedDict with maxsize or use TTL-based expiry', 'question' => 'Fix memory leak'],
        ['id' => 4, 'category' => 'Python', 'type' => 'mcq', 'points' => 8, 'correctAnswer' => 0, 'question' => 'List comprehension output?', 'options' => ['[0, 2, 2, 6, 4]', '[0, 1, 2, 3, 4]', '[0, 2, 4, 6, 8]', '[1, 3, 5, 7, 9]']],
        ['id' => 5, 'category' => 'Database', 'type' => 'mcq', 'points' => 10, 'correctAnswer' => 1, 'question' => 'Query slow - fix?', 'options' => ['Add index on created_at only', 'Add composite index on (created_at, last_login)', 'Use FORCE INDEX', 'Remove LIMIT']],
        ['id' => 6, 'category' => 'Database', 'type' => 'mcq', 'points' => 10, 'correctAnswer' => 1, 'question' => 'Query result?', 'options' => ['Departments with >5 employees', 'Error - cannot use alias in HAVING', 'All departments', 'First 5 departments']],
        ['id' => 7, 'category' => 'Database', 'type' => 'code', 'points' => 12, 'correctAnswer' => 'SELECT MAX(salary) FROM employees WHERE salary < (SELECT MAX(salary) FROM employees)', 'question' => 'Second highest salary query'],
        ['id' => 8, 'category' => 'Database', 'type' => 'mcq', 'points' => 8, 'correctAnswer' => 1, 'question' => 'INNER vs LEFT JOIN?', 'options' => ['No difference', 'INNER returns matching rows, LEFT returns all from left', 'LEFT is faster', 'INNER requires indexes']],
        ['id' => 9, 'category' => 'Database', 'type' => 'mcq', 'points' => 10, 'correctAnswer' => 1, 'question' => 'Prevent duplicate emails?', 'options' => ['Check with SELECT first', 'Create UNIQUE constraint', 'Use app validation only', 'Use TRIGGER']],
        ['id' => 10, 'category' => 'Apps Script', 'type' => 'mcq', 'points' => 10, 'correctAnswer' => 1, 'question' => 'Script timeout fix?', 'options' => ['Use Utilities.sleep()', 'Batch with setValues() and triggers', 'Split into smaller scripts', 'Request timeout increase']],
        ['id' => 11, 'category' => 'Apps Script', 'type' => 'code', 'points' => 10, 'correctAnswer' => 'var sheet = SpreadsheetApp.getActiveSheet(); var lastRow = sheet.getLastRow();', 'question' => 'Get last row with data'],
        ['id' => 12, 'category' => 'JavaScript', 'type' => 'mcq', 'points' => 8, 'correctAnswer' => 1, 'question' => 'Output of object reference?', 'options' => ['1', '2', 'undefined', 'Error']],
        ['id' => 13, 'category' => 'JavaScript', 'type' => 'code', 'points' => 12, 'correctAnswer' => 'Use let instead of var or use IIFE', 'question' => 'Fix closure bug'],
        ['id' => 14, 'category' => 'JavaScript', 'type' => 'mcq', 'points' => 10, 'correctAnswer' => 1, 'question' => 'Promise.all() behavior?', 'options' => ['Continues executing', 'Immediately rejects with first rejection', 'Returns partial results', 'Retries failed promise']],
        ['id' => 15, 'category' => 'JavaScript', 'type' => 'mcq', 'points' => 10, 'correctAnswer' => 2, 'question' => 'Deep clone object?', 'options' => ['let clone = obj', 'Object.assign({}, obj)', 'JSON.parse(JSON.stringify(obj))', '{...obj}']],
        ['id' => 16, 'category' => 'JavaScript', 'type' => 'code', 'points' => 12, 'correctAnswer' => 'function debounce(func, delay) { let timeout; return function(...args) { clearTimeout(timeout); timeout = setTimeout(() => func.apply(this, args), delay); }; }', 'question' => 'Debounce function'],
        ['id' => 17, 'category' => 'CSS/HTML', 'type' => 'mcq', 'points' => 10, 'correctAnswer' => 1, 'question' => 'Responsive 3-column grid?', 'options' => ['float with width 33.33%', 'display: grid with repeat(auto-fit, minmax(300px, 1fr))', 'Three separate divs', 'table layout']],
        ['id' => 18, 'category' => 'CSS/HTML', 'type' => 'mcq', 'points' => 8, 'correctAnswer' => 1, 'question' => 'display:none vs visibility:hidden?', 'options' => ['No difference', 'display:none removes from layout', 'visibility:hidden is faster', 'display:none for block only']],
        ['id' => 19, 'category' => 'CSS/HTML', 'type' => 'code', 'points' => 10, 'correctAnswer' => 'body { display: flex; justify-content: center; align-items: center; min-height: 100vh; }', 'question' => 'Center div with Flexbox'],
        ['id' => 20, 'category' => 'PHP', 'type' => 'mcq', 'points' => 10, 'correctAnswer' => 1, 'question' => 'Security issue?', 'options' => ['Missing error handling', 'SQL injection - use prepared statements', 'Not checking login', 'Use POST not GET']],
        ['id' => 21, 'category' => 'PHP', 'type' => 'mcq', 'points' => 8, 'correctAnswer' => 1, 'question' => 'Output of == vs ===?', 'options' => ['true, true', 'true, false', 'false, false', 'Error']],
        ['id' => 22, 'category' => 'PHP', 'type' => 'code', 'points' => 12, 'correctAnswer' => 'htmlspecialchars($name, ENT_QUOTES, \'UTF-8\')', 'question' => 'Fix XSS vulnerability'],
        ['id' => 23, 'category' => 'PHP', 'type' => 'code', 'points' => 8, 'correctAnswer' => 'session_start(); $_SESSION[\'user_id\'] = $userId;', 'question' => 'Start session and store user ID'],
        ['id' => 24, 'category' => 'PHP', 'type' => 'mcq', 'points' => 8, 'correctAnswer' => 1, 'question' => 'include vs require?', 'options' => ['No difference', 'require throws fatal error, include gives warning', 'include is faster', 'require once per file only']],
        ['id' => 25, 'category' => 'PHP', 'type' => 'code', 'points' => 12, 'correctAnswer' => '$stmt = $conn->prepare(\'INSERT INTO users (name, email) VALUES (?, ?)\'); $stmt->bind_param(\'ss\', $name, $email); $stmt->execute();', 'question' => 'Prepared statement'],
    ];
}

function logError($message) {
    $logFile = __DIR__ . '/logs/assessment_errors.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $timestamp = date('Y-m-d H:i:s');
    @file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function logDebug($message) {
    $logFile = __DIR__ . '/logs/assessment_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    @file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function getDbConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) throw new Exception("Database connection failed: " . $conn->connect_error);
        $conn->set_charset("utf8mb4");
        logDebug("Database connection successful");
        return $conn;
    } catch (Exception $e) {
        logError("Database connection error: " . $e->getMessage());
        return null;
    }
}

logDebug("Request received: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    logDebug("Raw input received: " . substr($input, 0, 200));
    
    $data = json_decode($input, true);
    
    if (!$data) {
        $error = json_last_error_msg();
        logError("JSON decode error: $error");
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data: ' . $error]);
        exit;
    }
    
    $action = $data['action'] ?? '';
    logDebug("Action: $action");
    
    if ($action === 'submit_assessment') {
        handleAssessmentSubmission($data);
    } else {
        logError("Unknown action: $action");
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
    }
} else {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Only POST requests allowed']);
}

function handleAssessmentSubmission($data) {
    try {
        logDebug("Starting assessment submission");
        
        // NOW we only need name, email, and answers - NOT allQuestions
        $requiredFields = ['name', 'email', 'answers'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) throw new Exception("Missing field: $field");
        }
        
        $candidateName = htmlspecialchars(strip_tags(trim($data['name'])), ENT_QUOTES, 'UTF-8');
        $candidateEmail = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        
        if (!$candidateEmail) throw new Exception("Invalid email");
        
        logDebug("Candidate: $candidateName ($candidateEmail)");
        
        $answers = $data['answers'];
        $timeSpent = intval($data['timeSpent'] ?? 0);
        $tabSwitches = intval($data['tabSwitches'] ?? 0);
        $questionTimings = $data['questionTimings'] ?? [];
        $submittedAt = $data['submittedAt'] ?? date('Y-m-d H:i:s');
        
        // Get questions from server side
        $allQuestions = getQuestions();
        
        $scoreData = calculateScore($answers, $allQuestions);
        logDebug("Score: " . $scoreData['scorePercentage'] . "%");
        
        $conn = getDbConnection();
        if (!$conn) throw new Exception("Database connection failed");
        
        $assessmentId = saveAssessment($conn, $candidateName, $candidateEmail, $answers, $allQuestions, $scoreData, $timeSpent, $tabSwitches, $questionTimings, $submittedAt);
        logDebug("Saved with ID: $assessmentId");
        
        $conn->close();
        
        if (SEND_EMAIL_NOTIFICATIONS) {
            try {
                sendEmailNotification($candidateName, $candidateEmail, $scoreData, $assessmentId);
                logDebug("Email sent successfully");
            } catch (Exception $e) {
                logError("Email error: " . $e->getMessage());
            }
        }
        
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Assessment submitted successfully', 'assessmentId' => $assessmentId, 'score' => $scoreData]);
        
    } catch (Exception $e) {
        logError("Error: " . $e->getMessage());
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function calculateScore($answers, $allQuestions) {
    $totalPoints = 0;
    $earnedPoints = 0;
    $correctAnswers = 0;
    $detailedResults = [];
    $categoryScores = [];
    
    foreach ($allQuestions as $question) {
        $questionId = $question['id'];
        $totalPoints += $question['points'];
        
        $category = $question['category'];
        if (!isset($categoryScores[$category])) {
            $categoryScores[$category] = ['total' => 0, 'earned' => 0, 'correct' => 0, 'questions' => 0];
        }
        $categoryScores[$category]['total'] += $question['points'];
        $categoryScores[$category]['questions']++;
        
        $isCorrect = false;
        $userAnswer = $answers[$questionId] ?? null;
        $pointsEarned = 0;
        
        if ($question['type'] === 'mcq') {
            if ($userAnswer !== null && $userAnswer == $question['correctAnswer']) {
                $isCorrect = true;
                $pointsEarned = $question['points'];
                $earnedPoints += $question['points'];
                $correctAnswers++;
                $categoryScores[$category]['earned'] += $question['points'];
                $categoryScores[$category]['correct']++;
            }
        } else if ($question['type'] === 'code') {
            if ($userAnswer && strlen($userAnswer) > 20) {
                $keywords = extractKeywords($question['correctAnswer']);
                $matchCount = 0;
                foreach ($keywords as $keyword) {
                    if (stripos($userAnswer, $keyword) !== false) $matchCount++;
                }
                $keywordRatio = $matchCount / max(count($keywords), 1);
                $partialPoints = $question['points'] * $keywordRatio;
                $partialPoints = max($question['points'] * 0.3, min($partialPoints, $question['points'] * 0.7));
                
                $pointsEarned = $partialPoints;
                $earnedPoints += $partialPoints;
                $categoryScores[$category]['earned'] += $partialPoints;
                
                if ($keywordRatio >= 0.6) {
                    $correctAnswers++;
                    $isCorrect = true;
                    $categoryScores[$category]['correct']++;
                }
            }
        }
        
        $detailedResults[] = [
            'questionId' => $questionId,
            'category' => $question['category'],
            'type' => $question['type'],
            'isCorrect' => $isCorrect,
            'points' => $question['points'],
            'earnedPoints' => $pointsEarned,
            'userAnswer' => $userAnswer
        ];
    }
    
    $scorePercentage = ($totalPoints > 0) ? ($earnedPoints / $totalPoints) * 100 : 0;
    $status = $scorePercentage >= EXCELLENT_SCORE_PERCENTAGE ? 'excellent' : ($scorePercentage >= PASSING_SCORE_PERCENTAGE ? 'pass' : 'fail');
    
    return [
        'totalQuestions' => count($allQuestions),
        'correctAnswers' => $correctAnswers,
        'totalPoints' => $totalPoints,
        'earnedPoints' => round($earnedPoints, 2),
        'scorePercentage' => round($scorePercentage, 2),
        'status' => $status,
        'detailedResults' => $detailedResults,
        'categoryScores' => $categoryScores
    ];
}

function extractKeywords($text) {
    $text = strtolower($text);
    $words = preg_split('/[\s,.:;()\[\]{}]+/', $text);
    $keywords = [];
    foreach ($words as $word) {
        $word = trim($word);
        if (strlen($word) > 3 && !in_array($word, ['with', 'from', 'that', 'this', 'then', 'when', 'where'])) {
            $keywords[] = $word;
        }
    }
    return array_unique($keywords);
}

function saveAssessment($conn, $name, $email, $answers, $questions, $scoreData, $timeSpent, $tabSwitches, $questionTimings, $submittedAt) {
    $stmt = $conn->prepare("INSERT INTO assessments (candidate_name, candidate_email, total_questions, correct_answers, total_points, earned_points, score_percentage, status, time_spent_seconds, tab_switches, submitted_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    
    $stmt->bind_param("ssiiddssiis", $name, $email, $scoreData['totalQuestions'], $scoreData['correctAnswers'], $scoreData['totalPoints'], $scoreData['earnedPoints'], $scoreData['scorePercentage'], $scoreData['status'], $timeSpent, $tabSwitches, $submittedAt);
    
    if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
    
    $assessmentId = $conn->insert_id;
    $stmt->close();
    
    $stmt = $conn->prepare("INSERT INTO assessment_details (assessment_id, question_id, category, question_type, question_text, question_options, user_answer, correct_answer, is_correct, points_possible, points_earned, time_spent_seconds) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) throw new Exception("Prepare details failed: " . $conn->error);
    
    foreach ($scoreData['detailedResults'] as $result) {
        $questionId = $result['questionId'];
        $userAnswer = json_encode($result['userAnswer']);
        
        $questionData = null;
        foreach ($questions as $q) {
            if ($q['id'] == $questionId) {
                $questionData = $q;
                break;
            }
        }
        
        if (!$questionData) continue;
        
        $questionText = $questionData['question'];
        $questionOptions = isset($questionData['options']) ? json_encode($questionData['options']) : null;
        $correctAnswer = json_encode($questionData['correctAnswer']);
        
        $timeOnQuestion = $questionTimings[$questionId] ?? 0;
        
        $stmt->bind_param("iissssssiddi", 
            $assessmentId, 
            $questionId, 
            $result['category'], 
            $result['type'], 
            $questionText,
            $questionOptions,
            $userAnswer, 
            $correctAnswer, 
            $result['isCorrect'], 
            $result['points'], 
            $result['earnedPoints'], 
            $timeOnQuestion
        );
        $stmt->execute();
    }
    
    $stmt->close();
    return $assessmentId;
}

function sendEmailNotification($name, $email, $scoreData, $assessmentId) {
    $to = ADMIN_EMAIL;
    $subject = "New IT Assessment - $name";
    
    $message = "<html><body style='font-family: Arial;'>";
    $message .= "<h2 style='color: #667eea;'>New Assessment Submission</h2>";
    $message .= "<p><strong>Name:</strong> $name</p>";
    $message .= "<p><strong>Email:</strong> $email</p>";
    $message .= "<p><strong>ID:</strong> $assessmentId</p>";
    $message .= "<div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #667eea;'>";
    $message .= "<h3>Score: {$scoreData['scorePercentage']}%</h3>";
    $message .= "<p>Correct: {$scoreData['correctAnswers']} / {$scoreData['totalQuestions']}</p>";
    $message .= "<p>Points: {$scoreData['earnedPoints']} / {$scoreData['totalPoints']}</p>";
    $message .= "<p>Status: " . strtoupper($scoreData['status']) . "</p>";
    $message .= "</div>";
    $message .= "<p><a href='https://fcicontracts.co.uk/view_details.php?id={$assessmentId}' style='display:inline-block;padding:10px 20px;background:#667eea;color:white;text-decoration:none;border-radius:5px;'>View Full Details</a></p>";
    $message .= "</body></html>";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . FROM_EMAIL . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>