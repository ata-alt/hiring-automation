<?php
/**
 * Detailed Assessment View with Advanced Analytics
 * Shows complete breakdown of a candidate's assessment with insights
 */

// Include authentication
require_once 'auth.php';
checkAuth();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'u947717806_it_assessment');
define('DB_PASS', '#fXfdcGe^H09');
define('DB_NAME', 'u947717806_it_assessment');

// Get assessment ID
$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($assessment_id <= 0) {
    die("Invalid assessment ID");
}

// Get database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get assessment summary
$summary_query = "SELECT * FROM assessments WHERE id = ?";
$stmt = $conn->prepare($summary_query);
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$summary) {
    die("Assessment not found");
}

// Get detailed responses
$details_query = "SELECT * FROM assessment_details WHERE assessment_id = ? ORDER BY question_id";
$stmt = $conn->prepare($details_query);
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$details = $stmt->get_result();
$stmt->close();

// Get comparative analytics - average scores from all assessments
$comparative_query = "
    SELECT 
        AVG(score_percentage) as avg_score,
        AVG(time_spent_seconds) as avg_time,
        AVG(tab_switches) as avg_tab_switches,
        COUNT(*) as total_candidates
    FROM assessments
";
$comp_result = $conn->query($comparative_query);
$comparative_data = $comp_result->fetch_assoc();

// Get percentile ranking
$percentile_query = "
    SELECT COUNT(*) as below_count
    FROM assessments
    WHERE score_percentage < ?
";
$stmt = $conn->prepare($percentile_query);
$stmt->bind_param("d", $summary['score_percentage']);
$stmt->execute();
$percentile_result = $stmt->get_result()->fetch_assoc();
$percentile = ($comparative_data['total_candidates'] > 0) 
    ? round(($percentile_result['below_count'] / $comparative_data['total_candidates']) * 100, 1) 
    : 0;
$stmt->close();

// Calculate category-wise performance and time analysis
$category_performance = [];
$details_array = [];
$time_analysis = [
    'too_fast' => 0,  // < 15 seconds
    'normal' => 0,    // 15-120 seconds
    'too_slow' => 0,  // > 120 seconds
    'total_time' => 0,
    'avg_time' => 0
];
$red_flags = [];

while ($row = $details->fetch_assoc()) {
    $details_array[] = $row;
    $cat = $row['category'];
    
    // Category performance
    if (!isset($category_performance[$cat])) {
        $category_performance[$cat] = ['correct' => 0, 'total' => 0, 'points_earned' => 0, 'points_possible' => 0];
    }
    $category_performance[$cat]['total']++;
    $category_performance[$cat]['points_possible'] += $row['points_possible'];
    $category_performance[$cat]['points_earned'] += $row['points_earned'];
    if ($row['is_correct']) {
        $category_performance[$cat]['correct']++;
    }
    
    // Time analysis
    $time_spent = $row['time_spent_seconds'];
    $time_analysis['total_time'] += $time_spent;
    
    if ($time_spent < 15) {
        $time_analysis['too_fast']++;
        if (!$row['is_correct']) {
            $red_flags[] = "Question " . count($details_array) . ": Answered in {$time_spent}s (possibly rushed/guessed)";
        }
    } elseif ($time_spent > 120) {
        $time_analysis['too_slow']++;
    } else {
        $time_analysis['normal']++;
    }
}

$time_analysis['avg_time'] = count($details_array) > 0 
    ? round($time_analysis['total_time'] / count($details_array), 1) 
    : 0;

// Check for suspicious patterns
if ($summary['tab_switches'] > 10) {
    $red_flags[] = "High tab switching activity ({$summary['tab_switches']} switches) - possible external resource usage";
}

// Performance trend analysis
$first_half = array_slice($details_array, 0, ceil(count($details_array) / 2));
$second_half = array_slice($details_array, ceil(count($details_array) / 2));

$first_half_correct = 0;
foreach ($first_half as $q) if ($q['is_correct']) $first_half_correct++;

$second_half_correct = 0;
foreach ($second_half as $q) if ($q['is_correct']) $second_half_correct++;

$first_half_percentage = count($first_half) > 0 ? ($first_half_correct / count($first_half)) * 100 : 0;
$second_half_percentage = count($second_half) > 0 ? ($second_half_correct / count($second_half)) * 100 : 0;

$performance_trend = $second_half_percentage - $first_half_percentage;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Details - <?php echo htmlspecialchars($summary['candidate_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            padding: 20px;
        }
        .detail-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .score-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5em;
            font-weight: bold;
            color: white;
            margin: 0 auto 20px;
        }
        .score-excellent { background: #28a745; }
        .score-pass { background: #ffc107; color: #333; }
        .score-fail { background: #dc3545; }
        .question-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 5px solid #6c757d;
        }
        .question-card.correct {
            border-left-color: #28a745;
        }
        .question-card.incorrect {
            border-left-color: #dc3545;
        }
        .answer-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }
        .correct-answer {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .wrong-answer {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .category-badge {
            background: #764ba2;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85em;
            display: inline-block;
            margin-right: 10px;
        }
        .progress-bar-custom {
            height: 30px;
            border-radius: 15px;
        }
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        .row.mb-4 {
            display: flex;
            flex-wrap: wrap;
        }
        .row.mb-4 > [class*='col-'] {
            display: flex;
            flex-direction: column;
        }
        .info-card-compact {
            padding: 15px;
        }
        .info-card-compact h5 {
            font-size: 1em;
            margin-bottom: 10px;
        }
        .info-card-compact hr {
            margin: 10px 0;
        }
        .info-card-compact .d-flex {
            font-size: 0.9em;
        }
        .score-circle-compact {
            width: 100px;
            height: 100px;
            font-size: 1.8em;
        }
        .insight-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            margin: 0 auto 10px;
        }
        .time-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.75em;
            margin-left: 5px;
        }
        .time-fast { background: #ffc107; color: #333; }
        .time-normal { background: #28a745; color: white; }
        .time-slow { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="detail-header">
            <a href="view_results.php" class="btn btn-light mb-3">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($summary['candidate_name']); ?></h1>
            <p class="mb-0">
                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($summary['candidate_email']); ?>
                <span class="ms-3">
                    <i class="fas fa-calendar"></i> <?php echo date('F d, Y \a\t H:i', strtotime($summary['submitted_at'])); ?>
                </span>
            </p>
        </div>

        <!-- Summary Cards Row -->
        <div class="row mb-4">
            <!-- Score Card -->
            <div class="col-md-4">
                <div class="info-card info-card-compact text-center">
                    <div class="score-circle score-circle-compact score-<?php echo $summary['status']; ?>">
                        <?php echo number_format($summary['score_percentage'], 1); ?>%
                    </div>
                    <h5>Overall Score</h5>
                    <p class="text-muted mb-2" style="font-size: 0.9em;">
                        <?php echo $summary['earned_points']; ?> / <?php echo $summary['total_points']; ?> points
                    </p>
                    <span class="badge bg-<?php 
                        echo $summary['status'] === 'excellent' ? 'success' : 
                            ($summary['status'] === 'pass' ? 'warning' : 'danger'); 
                    ?> p-2" style="font-size: 0.85em;">
                        <?php echo strtoupper($summary['status']); ?>
                    </span>
                </div>
            </div>

            <!-- Performance Card -->
            <div class="col-md-4">
                <div class="info-card info-card-compact">
                    <h5><i class="fas fa-chart-bar"></i> Performance Metrics</h5>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Correct Answers:</span>
                        <strong><?php echo $summary['correct_answers']; ?> / <?php echo $summary['total_questions']; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Accuracy Rate:</span>
                        <strong><?php echo round(($summary['correct_answers'] / $summary['total_questions']) * 100, 1); ?>%</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Time Spent:</span>
                        <strong><?php echo floor($summary['time_spent_seconds'] / 60); ?> min</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Tab Switches:</span>
                        <strong>
                            <span class="badge bg-<?php echo $summary['tab_switches'] > 5 ? 'danger' : 'success'; ?>">
                                <?php echo $summary['tab_switches']; ?>
                            </span>
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Category Performance Card -->
            <div class="col-md-4">
                <div class="info-card info-card-compact">
                    <h5><i class="fas fa-layer-group"></i> Category Breakdown</h5>
                    <hr>
                    <?php foreach ($category_performance as $category => $perf): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="category-badge" style="padding: 3px 8px; font-size: 0.75em;"><?php echo $category; ?></span>
                            <small style="font-size: 0.8em;"><?php echo $perf['correct']; ?> / <?php echo $perf['total']; ?></small>
                        </div>
                        <div class="progress" style="height: 15px;">
                            <?php 
                                $percentage = ($perf['points_possible'] > 0) 
                                    ? ($perf['points_earned'] / $perf['points_possible']) * 100 
                                    : 0;
                                $color = $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                            ?>
                            <div class="progress-bar bg-<?php echo $color; ?>" style="width: <?php echo $percentage; ?>%; font-size: 0.75em;">
                                <?php echo round($percentage, 1); ?>%
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- NEW: Time Analysis Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="info-card">
                    <h4 class="mb-4"><i class="fas fa-clock"></i> Time Analysis & Behavior Patterns</h4>
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="insight-icon bg-success text-white">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h5><?php echo $time_analysis['too_fast']; ?> Questions</h5>
                            <p class="text-muted">Answered Quickly<br>(&lt;15 seconds)</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="insight-icon bg-primary text-white">
                                <i class="fas fa-check"></i>
                            </div>
                            <h5><?php echo $time_analysis['normal']; ?> Questions</h5>
                            <p class="text-muted">Normal Pace<br>(15-120 seconds)</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="insight-icon bg-warning text-dark">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <h5><?php echo $time_analysis['too_slow']; ?> Questions</h5>
                            <p class="text-muted">Took Longer<br>(&gt;120 seconds)</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="insight-icon bg-info text-white">
                                <i class="fas fa-stopwatch"></i>
                            </div>
                            <h5><?php echo $time_analysis['avg_time']; ?>s</h5>
                            <p class="text-muted">Average Time<br>Per Question</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEW: Comparative Analytics Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="info-card">
                    <h4 class="mb-4"><i class="fas fa-chart-line"></i> Comparative Analytics</h4>
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h6 class="text-muted mb-2">Percentile Rank</h6>
                            <div class="display-4 text-primary"><?php echo $percentile; ?>%</div>
                            <small class="text-muted">Better than <?php echo $percentile; ?>% of candidates</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6 class="text-muted mb-2">Score vs Average</h6>
                            <div class="display-4 <?php echo $summary['score_percentage'] >= $comparative_data['avg_score'] ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $summary['score_percentage'] >= $comparative_data['avg_score'] ? '+' : ''; ?>
                                <?php echo round($summary['score_percentage'] - $comparative_data['avg_score'], 1); ?>%
                            </div>
                            <small class="text-muted">Average: <?php echo round($comparative_data['avg_score'], 1); ?>%</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6 class="text-muted mb-2">Performance Trend</h6>
                            <div class="display-4 <?php echo $performance_trend >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php if ($performance_trend > 0): ?>
                                    <i class="fas fa-arrow-up"></i>
                                <?php elseif ($performance_trend < 0): ?>
                                    <i class="fas fa-arrow-down"></i>
                                <?php else: ?>
                                    <i class="fas fa-arrow-right"></i>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">
                                <?php if ($performance_trend > 5): ?>
                                    Improved as test progressed
                                <?php elseif ($performance_trend < -5): ?>
                                    Declined as test progressed
                                <?php else: ?>
                                    Consistent throughout
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6 class="text-muted mb-2">Total Candidates</h6>
                            <div class="display-4 text-secondary"><?php echo $comparative_data['total_candidates']; ?></div>
                            <small class="text-muted">In comparison pool</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEW: Red Flags Section -->
        <?php if (count($red_flags) > 0): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="info-card">
                    <div class="accordion" id="redFlagsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingRedFlags">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRedFlags" aria-expanded="false" aria-controls="collapseRedFlags">
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i> 
                                    <strong>Red Flags & Warnings</strong>
                                    <span class="badge bg-warning text-dark ms-2"><?php echo count($red_flags); ?></span>
                                </button>
                            </h2>
                            <div id="collapseRedFlags" class="accordion-collapse collapse" aria-labelledby="headingRedFlags" data-bs-parent="#redFlagsAccordion">
                                <div class="accordion-body">
                                    <ul class="list-group">
                                        <?php foreach ($red_flags as $flag): ?>
                                        <li class="list-group-item list-group-item-warning">
                                            <i class="fas fa-flag"></i> <?php echo $flag; ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Detailed Question Responses -->
        <div class="info-card">
            <h4 class="mb-4"><i class="fas fa-list-check"></i> Question-by-Question Analysis</h4>
            
            <?php foreach ($details_array as $index => $detail): ?>
            <div class="question-card <?php echo $detail['is_correct'] ? 'correct' : 'incorrect'; ?>">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5>
                            Question <?php echo $index + 1; ?>
                            <?php if ($detail['is_correct']): ?>
                                <i class="fas fa-check-circle text-success"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-danger"></i>
                            <?php endif; ?>
                        </h5>
                        <span class="category-badge"><?php echo $detail['category']; ?></span>
                        <span class="badge bg-secondary"><?php echo strtoupper($detail['question_type']); ?></span>
                    </div>
                    <div class="text-end">
                        <strong><?php echo $detail['points_earned']; ?> / <?php echo $detail['points_possible']; ?> points</strong>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> <?php echo $detail['time_spent_seconds']; ?> seconds
                            <?php 
                                $time = $detail['time_spent_seconds'];
                                if ($time < 15) {
                                    echo '<span class="time-badge time-fast">Fast</span>';
                                } elseif ($time > 120) {
                                    echo '<span class="time-badge time-slow">Slow</span>';
                                } else {
                                    echo '<span class="time-badge time-normal">Normal</span>';
                                }
                            ?>
                        </small>
                    </div>
                </div>
        
                <!-- SHOW THE ACTUAL QUESTION -->
                <div class="mb-3">
                    <strong>Question:</strong>
                    <div class="answer-box" style="background: #e7f3ff; border: 1px solid #b3d9ff;">
                        <?php 
                            $questionText = $detail['question_text'] ?? 'Question text not available';
                            $questionText = preg_replace('/```(\w+)?\n(.*?)```/s', '<pre style="background:#2d2d2d;color:#f8f8f2;padding:10px;border-radius:5px;overflow-x:auto;">$2</pre>', $questionText);
                            echo nl2br($questionText);
                        ?>
                    </div>
                </div>
        
                <?php if ($detail['question_type'] === 'mcq'): ?>
                    <!-- MCQ: Show all options -->
                    <?php 
                        $options = json_decode($detail['question_options'], true);
                        $userAnswerIndex = json_decode($detail['user_answer']);
                        $correctAnswerIndex = json_decode($detail['correct_answer']);
                    ?>
                    
                    <?php if ($options): ?>
                    <div class="mb-3">
                        <strong>Answer Options:</strong>
                        <?php foreach ($options as $idx => $option): ?>
                            <div class="answer-box <?php 
                                if ($idx == $correctAnswerIndex) {
                                    echo 'correct-answer';
                                } elseif ($idx == $userAnswerIndex && $idx != $correctAnswerIndex) {
                                    echo 'wrong-answer';
                                }
                            ?>" style="margin: 5px 0;">
                                <strong><?php echo chr(65 + $idx); ?>.</strong> <?php echo htmlspecialchars($option); ?>
                                <?php if ($idx == $correctAnswerIndex): ?>
                                    <i class="fas fa-check text-success float-end" title="Correct Answer"></i>
                                <?php endif; ?>
                                <?php if ($idx == $userAnswerIndex): ?>
                                    <span class="badge bg-primary float-end me-2">Candidate's Choice</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
        
                    <div class="mb-3">
                        <strong>Candidate Selected:</strong>
                        <div class="answer-box <?php echo $detail['is_correct'] ? 'correct-answer' : 'wrong-answer'; ?>">
                            Option <?php echo chr(65 + $userAnswerIndex); ?>
                            <?php if (isset($options[$userAnswerIndex])): ?>
                                - <?php echo htmlspecialchars($options[$userAnswerIndex]); ?>
                            <?php endif; ?>
                            <?php if ($detail['is_correct']): ?>
                                <i class="fas fa-check text-success float-end"></i>
                            <?php else: ?>
                                <i class="fas fa-times text-danger float-end"></i>
                            <?php endif; ?>
                        </div>
                    </div>
        
                <?php else: ?>
                    <!-- Code/Text Response -->
                    <div class="mb-3">
                        <strong>Candidate's Answer:</strong>
                        <div class="answer-box <?php echo $detail['is_correct'] ? 'correct-answer' : ''; ?>">
                            <?php 
                                $user_answer = json_decode($detail['user_answer']);
                                echo $user_answer ? nl2br(htmlspecialchars($user_answer)) : '<em class="text-muted">No answer provided</em>';
                            ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Expected Answer / Key Points:</strong>
                        <div class="answer-box correct-answer">
                            <?php echo nl2br(htmlspecialchars(json_decode($detail['correct_answer']))); ?>
                        </div>
                    </div>
                    <?php if (!$detail['is_correct']): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> 
                        Code answers are partially auto-graded using keyword matching. 
                        Manual review recommended for accurate assessment.
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Recommendation Section -->
        <div class="info-card">
            <h4><i class="fas fa-lightbulb"></i> Recommendation</h4>
            <hr>
            <?php if ($summary['status'] === 'excellent'): ?>
                <div class="alert alert-success">
                    <h5><i class="fas fa-star"></i> Strong Candidate - Highly Recommended</h5>
                    <p>This candidate demonstrated excellent technical knowledge with a score of <?php echo number_format($summary['score_percentage'], 1); ?>%. 
                    They answered <?php echo $summary['correct_answers']; ?> out of <?php echo $summary['total_questions']; ?> questions correctly and performed better than <?php echo $percentile; ?>% of all candidates.</p>
                    <p><strong>Next Step:</strong> Schedule interview with CTO immediately.</p>
                </div>
            <?php elseif ($summary['status'] === 'pass'): ?>
                <div class="alert alert-warning">
                    <h5><i class="fas fa-check"></i> Acceptable Candidate - Consider for Interview</h5>
                    <p>This candidate showed satisfactory technical knowledge with a score of <?php echo number_format($summary['score_percentage'], 1); ?>%. 
                    Review the detailed responses above, especially in weak areas.</p>
                    <p><strong>Next Step:</strong> Consider for initial phone screening or technical interview.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <h5><i class="fas fa-times"></i> Below Expectations - Not Recommended</h5>
                    <p>This candidate scored <?php echo number_format($summary['score_percentage'], 1); ?>%, which is below the passing threshold. 
                    Significant gaps in technical knowledge were identified across multiple topics.</p>
                    <p><strong>Next Step:</strong> Send polite rejection email.</p>
                </div>
            <?php endif; ?>

            <?php if ($summary['tab_switches'] > 10): ?>
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>Warning:</strong> This candidate switched tabs <?php echo $summary['tab_switches']; ?> times during the assessment, 
                which may indicate they were searching for answers online.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Print Button -->
    <button onclick="window.print()" class="btn btn-primary btn-lg print-btn">
        <i class="fas fa-print"></i> Print Report
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>