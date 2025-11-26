<?php

/**
 * Process Assessment - Senior Manager Aptitude Test
 * Handles scoring, storage, and redirection to results
 */

// Start session for passing data
session_start();

// Database configuration (adjust as needed)
define('DB_HOST', 'localhost');
define('DB_NAME', 'aptitude_test');
define('DB_USER', 'root');
define('DB_PASS', '');

// Answer key with correct answers (0-indexed)
$answerKey = [
    '0-0' => 1,  // Q1: B (2 photographers)
    '0-1' => 2,  // Q2: C (£268.80)
    '0-2' => 2,  // Q3: C (Gamma Digital)
    '0-3' => 2,  // Q4: C (£20,040)
    '0-4' => 1,  // Q5: B (15)
    '1-0' => 1,  // Q6: B (Senior QS review only)
    '1-1' => 1,  // Q7: B (P2 - High)
    '1-2' => 2,  // Q8: C (96)
    '1-3' => 2,  // Q9: C (A:Tue, B:Thu, C:Wed)
    '1-4' => 2,  // Q10: C (Manager + Director)
    '2-0' => 3,  // Q11: D (In-house Retouch)
    '2-1' => 3,  // Q12: D (Cannot calculate)
    '2-2' => 2,  // Q13: C (Expand London + In-house)
    '2-3' => 2,  // Q14: C (Day 26)
    '2-4' => 0,  // Q15: A (-1 behind)
    '3-0' => 1,  // Q16: B (Document & review)
    '3-1' => 2,  // Q17: C (Joint session)
    '3-2' => 1,  // Q18: B (Spreadsheets)
    '3-3' => 3,  // Q19: D (Reduce staff 15%, vendor 30%)
    '3-4' => 1,  // Q20: B (Parallel system 30 days)
];

// Section names
$sectionNames = [
    0 => 'Numerical Reasoning',
    1 => 'Logical Reasoning',
    2 => 'Data Interpretation',
    3 => 'Situational Problem-Solving'
];

// Question details for review
$questionDetails = [
    1 => ['section' => 0, 'text' => 'Team productivity calculation - additional photographers needed'],
    2 => ['section' => 0, 'text' => 'Vendor pricing tiers - consolidation savings'],
    3 => ['section' => 0, 'text' => 'Quality control data - highest rework cost'],
    4 => ['section' => 0, 'text' => 'Budget reallocation - new quarterly allocation'],
    5 => ['section' => 0, 'text' => 'Workflow error rates - images requiring rework'],
    6 => ['section' => 1, 'text' => 'Production rules - Spring Campaign approvals'],
    7 => ['section' => 1, 'text' => 'Priority matrix - project classification'],
    8 => ['section' => 1, 'text' => 'Pattern recognition - Q4 productivity score'],
    9 => ['section' => 1, 'text' => 'Vendor scheduling constraints'],
    10 => ['section' => 1, 'text' => 'Workflow automation logic'],
    11 => ['section' => 2, 'text' => 'Production metrics - best balanced team'],
    12 => ['section' => 2, 'text' => 'Quality impact - effective turnaround time'],
    13 => ['section' => 2, 'text' => 'Capacity planning - output increase strategy'],
    14 => ['section' => 2, 'text' => 'Project timeline - earliest delivery day'],
    15 => ['section' => 2, 'text' => 'Project buffer calculation'],
    16 => ['section' => 3, 'text' => 'Vendor quality issues - first action'],
    17 => ['section' => 3, 'text' => 'Team conflict resolution'],
    18 => ['section' => 3, 'text' => 'System downtime - minimizing disruption'],
    19 => ['section' => 3, 'text' => 'Budget cut - reallocation strategy'],
    20 => ['section' => 3, 'text' => 'AI implementation - adoption approach']
];

// Option labels
$optionLabels = ['A', 'B', 'C', 'D'];

/**
 * Calculate scores from submitted answers
 */
function calculateScores($answers, $answerKey)
{
    $totalScore = 0;
    $sectionScores = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
    $questionResults = [];

    foreach ($answerKey as $qKey => $correctAnswer) {
        $parts = explode('-', $qKey);
        $section = (int)$parts[0];
        $questionInSection = (int)$parts[1];
        $questionNum = ($section * 5) + $questionInSection + 1;

        $userAnswer = isset($answers[$qKey]) ? (int)$answers[$qKey] : -1;
        $isCorrect = ($userAnswer === $correctAnswer);

        if ($isCorrect) {
            $totalScore += 4;
            $sectionScores[$section] += 4;
        }

        $questionResults[$questionNum] = [
            'section' => $section,
            'user_answer' => $userAnswer,
            'correct_answer' => $correctAnswer,
            'is_correct' => $isCorrect,
            'points' => $isCorrect ? 4 : 0
        ];
    }

    return [
        'total_score' => $totalScore,
        'section_scores' => $sectionScores,
        'question_results' => $questionResults
    ];
}

/**
 * Determine performance band
 */
function getPerformanceBand($score)
{
    if ($score >= 68) {
        return [
            'band' => 'Exceptional',
            'class' => 'exceptional',
            'description' => 'Strong candidate for senior leadership roles',
            'recommendation' => 'Highly recommended for immediate consideration'
        ];
    } elseif ($score >= 54) {
        return [
            'band' => 'Proficient',
            'class' => 'proficient',
            'description' => 'Well-suited for the role with standard onboarding',
            'recommendation' => 'Recommended for next interview stage'
        ];
    } elseif ($score >= 40) {
        return [
            'band' => 'Developing',
            'class' => 'developing',
            'description' => 'May require additional support or training',
            'recommendation' => 'Consider for role with structured development plan'
        ];
    } else {
        return [
            'band' => 'Below Threshold',
            'class' => 'below',
            'description' => 'Does not meet minimum requirements',
            'recommendation' => 'Not recommended for this position'
        ];
    }
}

/**
 * Calculate time taken
 */
function calculateTimeTaken($startTime, $endTime)
{
    $start = new DateTime($startTime);
    $end = new DateTime($endTime);
    $diff = $start->diff($end);

    $minutes = ($diff->h * 60) + $diff->i;
    $seconds = $diff->s;

    return [
        'minutes' => $minutes,
        'seconds' => $seconds,
        'formatted' => sprintf('%d min %d sec', $minutes, $seconds)
    ];
}

/**
 * Store results in database
 */
function storeResults($data, $scores, $performanceBand, $timeTaken)
{
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Create table if not exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS assessment_results (
                id INT AUTO_INCREMENT PRIMARY KEY,
                candidate_name VARCHAR(255) NOT NULL,
                candidate_email VARCHAR(255) NOT NULL,
                candidate_phone VARCHAR(50),
                position VARCHAR(255),
                total_score INT NOT NULL,
                max_score INT DEFAULT 80,
                percentage DECIMAL(5,2),
                performance_band VARCHAR(50),
                section_1_score INT,
                section_2_score INT,
                section_3_score INT,
                section_4_score INT,
                time_taken_minutes INT,
                time_taken_seconds INT,
                test_start_time DATETIME,
                test_end_time DATETIME,
                answers_json TEXT,
                question_results_json TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (candidate_email),
                INDEX idx_score (total_score),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Insert result
        $stmt = $pdo->prepare("
            INSERT INTO assessment_results (
                candidate_name, candidate_email, candidate_phone, position,
                total_score, percentage, performance_band,
                section_1_score, section_2_score, section_3_score, section_4_score,
                time_taken_minutes, time_taken_seconds,
                test_start_time, test_end_time,
                answers_json, question_results_json,
                ip_address, user_agent
            ) VALUES (
                :name, :email, :phone, :position,
                :total_score, :percentage, :band,
                :s1, :s2, :s3, :s4,
                :time_min, :time_sec,
                :start_time, :end_time,
                :answers, :results,
                :ip, :ua
            )
        ");

        $stmt->execute([
            ':name' => $data['candidate_name'],
            ':email' => $data['candidate_email'],
            ':phone' => $data['candidate_phone'] ?? '',
            ':position' => $data['position'] ?? 'Senior Manager - Production/Operations',
            ':total_score' => $scores['total_score'],
            ':percentage' => round(($scores['total_score'] / 80) * 100, 2),
            ':band' => $performanceBand['band'],
            ':s1' => $scores['section_scores'][0],
            ':s2' => $scores['section_scores'][1],
            ':s3' => $scores['section_scores'][2],
            ':s4' => $scores['section_scores'][3],
            ':time_min' => $timeTaken['minutes'],
            ':time_sec' => $timeTaken['seconds'],
            ':start_time' => date('Y-m-d H:i:s', strtotime($data['test_start_time'])),
            ':end_time' => date('Y-m-d H:i:s', strtotime($data['test_end_time'])),
            ':answers' => json_encode($data['answers']),
            ':results' => json_encode($scores['question_results']),
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        // Log error but continue (fallback to session storage)
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Store results in JSON file (fallback)
 */
function storeResultsFile($data, $scores, $performanceBand, $timeTaken)
{
    $resultsDir = __DIR__ . '/results';

    if (!is_dir($resultsDir)) {
        mkdir($resultsDir, 0755, true);
    }

    $resultId = uniqid('result_', true);
    $filename = $resultsDir . '/' . $resultId . '.json';

    $resultData = [
        'id' => $resultId,
        'candidate' => [
            'name' => $data['candidate_name'],
            'email' => $data['candidate_email'],
            'phone' => $data['candidate_phone'] ?? ''
        ],
        'position' => $data['position'] ?? 'Senior Manager - Production/Operations',
        'scores' => [
            'total' => $scores['total_score'],
            'max' => 80,
            'percentage' => round(($scores['total_score'] / 80) * 100, 2),
            'sections' => $scores['section_scores']
        ],
        'performance' => $performanceBand,
        'time_taken' => $timeTaken,
        'question_results' => $scores['question_results'],
        'raw_answers' => $data['answers'],
        'metadata' => [
            'test_start' => $data['test_start_time'],
            'test_end' => $data['test_end_time'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'processed_at' => date('Y-m-d H:i:s')
        ]
    ];

    file_put_contents($filename, json_encode($resultData, JSON_PRETTY_PRINT));

    return $resultId;
}

/**
 * Send email notification
 */
function sendEmailNotification($data, $scores, $performanceBand, $resultId)
{
    $to = 'hr@example.com'; // Change to your HR email
    $subject = "New Assessment Completed: {$data['candidate_name']} - {$performanceBand['band']}";

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .header { background: #1e3a5f; color: white; padding: 20px; }
            .content { padding: 20px; }
            .score-box { background: #f7fafc; padding: 15px; border-radius: 8px; margin: 15px 0; }
            .band-exceptional { color: #22543d; background: #c6f6d5; }
            .band-proficient { color: #2a4365; background: #bee3f8; }
            .band-developing { color: #92400e; background: #fef3c7; }
            .band-below { color: #c53030; background: #fed7d7; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>Senior Manager Assessment Completed</h2>
        </div>
        <div class='content'>
            <h3>Candidate Information</h3>
            <p><strong>Name:</strong> {$data['candidate_name']}</p>
            <p><strong>Email:</strong> {$data['candidate_email']}</p>
            <p><strong>Phone:</strong> {$data['candidate_phone']}</p>
            
            <div class='score-box'>
                <h3>Results Summary</h3>
                <p><strong>Total Score:</strong> {$scores['total_score']} / 80</p>
                <p><strong>Percentage:</strong> " . round(($scores['total_score'] / 80) * 100, 1) . "%</p>
                <p><strong>Performance Band:</strong> <span class='band-{$performanceBand['class']}'>{$performanceBand['band']}</span></p>
                <p><strong>Recommendation:</strong> {$performanceBand['recommendation']}</p>
            </div>
            
            <h3>Section Breakdown</h3>
            <ul>
                <li>Numerical Reasoning: {$scores['section_scores'][0]} / 20</li>
                <li>Logical Reasoning: {$scores['section_scores'][1]} / 20</li>
                <li>Data Interpretation: {$scores['section_scores'][2]} / 20</li>
                <li>Situational Problem-Solving: {$scores['section_scores'][3]} / 20</li>
            </ul>
            
            <p><a href='view_details.php?id={$resultId}'>View Full Results</a></p>
        </div>
    </body>
    </html>
    ";

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: Assessment System <noreply@example.com>'
    ];

    // Uncomment to enable email
    // mail($to, $subject, $message, implode("\r\n", $headers));
}

// ============================================
// MAIN PROCESSING
// ============================================

// Check for POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['assessment_data'])) {
    header('Location: index.html');
    exit;
}

// Decode submitted data
$submittedData = json_decode($_POST['assessment_data'], true);

if (!$submittedData || !isset($submittedData['answers'])) {
    $_SESSION['error'] = 'Invalid assessment data received.';
    header('Location: index.html');
    exit;
}

// Calculate scores
$scores = calculateScores($submittedData['answers'], $answerKey);

// Get performance band
$performanceBand = getPerformanceBand($scores['total_score']);

// Calculate time taken
$timeTaken = calculateTimeTaken(
    $submittedData['test_start_time'],
    $submittedData['test_end_time']
);

// Try to store in database first
$resultId = storeResults($submittedData, $scores, $performanceBand, $timeTaken);

// Fallback to file storage if database fails
if (!$resultId) {
    $resultId = storeResultsFile($submittedData, $scores, $performanceBand, $timeTaken);
}

// Send email notification (optional)
// sendEmailNotification($submittedData, $scores, $performanceBand, $resultId);

// Store in session for results page
$_SESSION['assessment_result'] = [
    'id' => $resultId,
    'candidate' => [
        'name' => $submittedData['candidate_name'],
        'email' => $submittedData['candidate_email'],
        'phone' => $submittedData['candidate_phone'] ?? ''
    ],
    'position' => $submittedData['position'] ?? 'Senior Manager - Production/Operations',
    'scores' => $scores,
    'performance' => $performanceBand,
    'time_taken' => $timeTaken,
    'section_names' => $sectionNames,
    'question_details' => $questionDetails,
    'option_labels' => $optionLabels,
    'processed_at' => date('Y-m-d H:i:s')
];

// Redirect to results page
header('Location: view_results.php');
exit;
