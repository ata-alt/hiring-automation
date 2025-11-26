<?php

/**
 * View Details - Admin/HR Results Page
 * Detailed view of candidate assessment results for hiring team
 */

session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'aptitude_test');
define('DB_USER', 'root');
define('DB_PASS', '');

// Section names
$sectionNames = [
    0 => 'Numerical Reasoning',
    1 => 'Logical Reasoning',
    2 => 'Data Interpretation',
    3 => 'Situational Problem-Solving'
];

// Question details with full information
$questionDetails = [
    1 => [
        'section' => 0,
        'text' => 'Team produces 240 images/day with 4 photographers. Need 360 images daily. How many additional photographers needed?',
        'options' => ['1', '2', '3', '4'],
        'correct' => 1,
        'skill' => 'Proportional reasoning'
    ],
    2 => [
        'section' => 0,
        'text' => 'Vendor pricing tiers: £0.85 (<500), £0.72 (500-999), £0.58 (1000+). Three projects: 380, 290, 450 images. Savings from consolidation?',
        'options' => ['£184.80', '£212.40', '£268.80', '£302.40'],
        'correct' => 2,
        'skill' => 'Cost optimization'
    ],
    3 => [
        'section' => 0,
        'text' => 'QC rejection rates: Alpha 2,400 @ 4.5%, Beta 1,800 @ 6.2%, Gamma 3,200 @ 3.8%. Rework costs £12/image. Highest total cost?',
        'options' => ['Alpha Studio', 'Beta Creative', 'Gamma Digital', 'All equal'],
        'correct' => 2,
        'skill' => 'Data analysis'
    ],
    4 => [
        'section' => 0,
        'text' => '£48,000 budget: Photo 45%, Retouch 35%, QC 20%. Shift 15% of Photo budget to Retouch. New Retouch allocation?',
        'options' => ['£16,800', '£19,440', '£20,040', '£21,600'],
        'correct' => 2,
        'skill' => 'Budget management'
    ],
    5 => [
        'section' => 0,
        'text' => 'Workflow stages: Capture 2%, Selection 5%, Retouching 8%, QC 1% error rates. From 100 images, how many need rework?',
        'options' => ['12', '15', '16', '18'],
        'correct' => 1,
        'skill' => 'Probability calculation'
    ],
    6 => [
        'section' => 1,
        'text' => 'Production rules for approvals. Spring Campaign at £8,500. Which approval required?',
        'options' => ['Director only', 'Senior QS only', 'Both', 'Neither'],
        'correct' => 1,
        'skill' => 'Rule interpretation'
    ],
    7 => [
        'section' => 1,
        'text' => 'Priority matrix: P1 (48hrs OR >£50K), P2 (1 week AND >£20K), P3 (other). Project: 5 days, £35K. Priority?',
        'options' => ['P1 - Critical', 'P2 - High', 'P3 - Standard', 'Cannot determine'],
        'correct' => 1,
        'skill' => 'Logical operators'
    ],
    8 => [
        'section' => 1,
        'text' => 'Productivity pattern: Q1:78 → Q2:82 → Q3:88. Improvement increases by 2 each quarter. Q4 score?',
        'options' => ['92', '94', '96', '98'],
        'correct' => 2,
        'skill' => 'Pattern recognition'
    ],
    9 => [
        'section' => 1,
        'text' => 'Vendor scheduling: A (Mon-Wed only), B (no consecutive days), C (day after A). A works Tuesday. Valid arrangement?',
        'options' => ['A:Tue, B:Mon, C:Wed', 'A:Tue, B:Wed, C:Wed', 'A:Tue, B:Thu, C:Wed', 'A:Tue, B:Mon, C:Thu'],
        'correct' => 2,
        'skill' => 'Constraint satisfaction'
    ],
    10 => [
        'section' => 1,
        'text' => 'Workflow logic: Rush if >100 AND <3 days; Notify if Premium OR Rush; Escalate if Notify AND >200. Premium, 250 images, 5 days?',
        'options' => ['Rush only', 'Manager notification only', 'Manager + Director', 'No actions'],
        'correct' => 2,
        'skill' => 'Conditional logic'
    ],
    11 => [
        'section' => 2,
        'text' => 'Weekly metrics for 4 teams. Best balance of meeting targets while quality >92%?',
        'options' => ['London Studio', 'Mumbai Remote', 'Freelance Pool', 'In-house Retouch'],
        'correct' => 3,
        'skill' => 'Multi-criteria evaluation'
    ],
    12 => [
        'section' => 2,
        'text' => 'Quality <90% triggers +2 hrs review per image. Mumbai Remote effective turnaround?',
        'options' => ['14 hours', '16 hours', '18.2 hours', 'Cannot calculate'],
        'correct' => 3,
        'skill' => 'Data sufficiency'
    ],
    13 => [
        'section' => 2,
        'text' => 'Increase output 25% while maintaining 92%+ quality average. Most viable approach?',
        'options' => ['Increase Mumbai +650', 'Double Freelance', 'Expand London + In-house', 'Add Mumbai + In-house equally'],
        'correct' => 2,
        'skill' => 'Strategic planning'
    ],
    14 => [
        'section' => 2,
        'text' => 'Project timeline delays. Retouching keeps planned duration, Review compressed 1 day. Earliest delivery?',
        'options' => ['Day 24', 'Day 25', 'Day 26', 'Day 27'],
        'correct' => 2,
        'skill' => 'Project scheduling'
    ],
    15 => [
        'section' => 2,
        'text' => 'Original project had 3 buffer days. Subsequent phases maintain planned durations. Buffer remaining?',
        'options' => ['-1 (behind)', '0', '1', '2'],
        'correct' => 0,
        'skill' => 'Timeline analysis'
    ],
    16 => [
        'section' => 3,
        'text' => 'Vendor delivering 15% below quality standard for a month. Team manually correcting. First action?',
        'options' => ['Terminate contract', 'Document & review meeting', 'Reduce workload 50%', 'Inform client'],
        'correct' => 1,
        'skill' => 'Vendor management'
    ],
    17 => [
        'section' => 3,
        'text' => 'Two team leads in conflict: one prioritizes speed, one quality. Most effective resolution?',
        'options' => ['Single standard with consequences', 'Separate teams by project type', 'Joint session for shared metrics', 'Promote higher performer'],
        'correct' => 2,
        'skill' => 'Conflict resolution'
    ],
    18 => [
        'section' => 3,
        'text' => 'Critical PM tool unavailable 48 hours. Three active projects. Minimize disruption?',
        'options' => ['Pause all projects', 'Export data, use spreadsheets', 'Shift to vendors', 'Request IT priority'],
        'correct' => 1,
        'skill' => 'Contingency planning'
    ],
    19 => [
        'section' => 3,
        'text' => 'Budget cut 20%. Current: Staff 60%, Vendor 25%, Tech 15%. Best reallocation strategy?',
        'options' => ['Reduce all 20%', 'Cut vendor 50%', 'Cut tech 60%', 'Staff -15%, Vendor -30%, keep tech'],
        'correct' => 3,
        'skill' => 'Resource optimization'
    ],
    20 => [
        'section' => 3,
        'text' => 'AI QC catches 40% more errors but 12% false positive. Team resistant. How proceed?',
        'options' => ['Mandate adoption', 'Parallel system 30 days', 'Delay until <5% FP', 'Voluntary opt-in'],
        'correct' => 1,
        'skill' => 'Change management'
    ]
];

$optionLabels = ['A', 'B', 'C', 'D'];

/**
 * Get result from database
 */
function getResultFromDB($id)
{
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $pdo->prepare("SELECT * FROM assessment_results WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get result from file
 */
function getResultFromFile($id)
{
    $filename = __DIR__ . '/results/' . $id . '.json';
    if (file_exists($filename)) {
        return json_decode(file_get_contents($filename), true);
    }
    return false;
}

/**
 * Get all results for listing
 */
function getAllResults($limit = 50, $offset = 0)
{
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $pdo->prepare("
            SELECT id, candidate_name, candidate_email, total_score, percentage, 
                   performance_band, created_at 
            FROM assessment_results 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Fallback to file-based results
        return getResultsFromFiles();
    }
}

/**
 * Get results from files (fallback)
 */
function getResultsFromFiles()
{
    $results = [];
    $resultsDir = __DIR__ . '/results';

    if (is_dir($resultsDir)) {
        $files = glob($resultsDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $results[] = [
                    'id' => $data['id'],
                    'candidate_name' => $data['candidate']['name'],
                    'candidate_email' => $data['candidate']['email'],
                    'total_score' => $data['scores']['total'],
                    'percentage' => $data['scores']['percentage'],
                    'performance_band' => $data['performance']['band'],
                    'created_at' => $data['metadata']['processed_at']
                ];
            }
        }
        // Sort by date descending
        usort($results, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
    }

    return $results;
}

// Determine view mode
$viewMode = 'list'; // 'list' or 'detail'
$result = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Try database first
    if (is_numeric($id)) {
        $result = getResultFromDB($id);
    }

    // Try file storage
    if (!$result) {
        $result = getResultFromFile($id);
    }

    if ($result) {
        $viewMode = 'detail';

        // Normalize data structure
        if (isset($result['candidate_name'])) {
            // Database format
            $candidate = [
                'name' => $result['candidate_name'],
                'email' => $result['candidate_email'],
                'phone' => $result['candidate_phone'] ?? ''
            ];
            $scores = [
                'total_score' => $result['total_score'],
                'section_scores' => [
                    0 => $result['section_1_score'],
                    1 => $result['section_2_score'],
                    2 => $result['section_3_score'],
                    3 => $result['section_4_score']
                ]
            ];
            $questionResults = json_decode($result['question_results_json'], true);
            $timeTaken = [
                'minutes' => $result['time_taken_minutes'],
                'seconds' => $result['time_taken_seconds'],
                'formatted' => $result['time_taken_minutes'] . ' min ' . $result['time_taken_seconds'] . ' sec'
            ];
            $createdAt = $result['created_at'];
            $percentage = $result['percentage'];
            $performanceBand = $result['performance_band'];
        } else {
            // File format
            $candidate = $result['candidate'];
            $scores = [
                'total_score' => $result['scores']['total'],
                'section_scores' => $result['scores']['sections']
            ];
            $questionResults = $result['question_results'];
            $timeTaken = $result['time_taken'];
            $createdAt = $result['metadata']['processed_at'];
            $percentage = $result['scores']['percentage'];
            $performanceBand = $result['performance']['band'];
        }
    }
}

// Get all results for list view
$allResults = [];
if ($viewMode === 'list') {
    $allResults = getAllResults();
}

// Performance band class mapping
function getBandClass($band)
{
    $map = [
        'Exceptional' => 'exceptional',
        'Proficient' => 'proficient',
        'Developing' => 'developing',
        'Below Threshold' => 'below'
    ];
    return $map[$band] ?? 'below';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $viewMode === 'detail' ? htmlspecialchars($candidate['name']) . ' - ' : ''; ?>Assessment Results | Admin</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f0f2f5;
            color: #1a202c;
            line-height: 1.6;
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            color: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar h1 {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .navbar-links {
            display: flex;
            gap: 20px;
        }

        .navbar-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .navbar-links a:hover {
            color: #fff;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header h2 {
            color: #1e3a5f;
            font-size: 1.5rem;
        }

        .btn {
            background: linear-gradient(135deg, #2d5a87 0%, #1e3a5f 100%);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 58, 95, 0.3);
        }

        .btn-secondary {
            background: #fff;
            color: #1e3a5f;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #f7fafc;
        }

        /* List View Styles */
        .results-table-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .results-table th,
        .results-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .results-table th {
            background: #f7fafc;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .results-table tr:hover {
            background: #f7fafc;
        }

        .results-table .candidate-info {
            display: flex;
            flex-direction: column;
        }

        .results-table .candidate-name {
            font-weight: 600;
            color: #1e3a5f;
        }

        .results-table .candidate-email {
            font-size: 0.85rem;
            color: #718096;
        }

        .score-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .band-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .band-badge.exceptional {
            background: #c6f6d5;
            color: #22543d;
        }

        .band-badge.proficient {
            background: #bee3f8;
            color: #2a4365;
        }

        .band-badge.developing {
            background: #fef3c7;
            color: #92400e;
        }

        .band-badge.below {
            background: #fed7d7;
            color: #c53030;
        }

        .action-btn {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        /* Detail View Styles */
        .detail-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 25px;
        }

        @media (max-width: 900px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .card h3 {
            color: #1e3a5f;
            font-size: 1rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .candidate-card .avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 2rem;
            font-weight: 600;
            margin: 0 auto 15px;
        }

        .candidate-card .name {
            text-align: center;
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e3a5f;
            margin-bottom: 5px;
        }

        .candidate-card .position {
            text-align: center;
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .candidate-card .info-list {
            list-style: none;
        }

        .candidate-card .info-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }

        .candidate-card .info-list li:last-child {
            border-bottom: none;
        }

        .candidate-card .info-list .icon {
            width: 30px;
            text-align: center;
        }

        .score-card {
            text-align: center;
        }

        .score-card .big-score {
            font-size: 3rem;
            font-weight: 700;
            color: #1e3a5f;
            line-height: 1;
        }

        .score-card .score-label {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .score-card .percentage {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d5a87;
        }

        .main-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .section-scores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .section-score-card {
            background: #f7fafc;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #2d5a87;
        }

        .section-score-card .section-name {
            font-size: 0.85rem;
            color: #718096;
            margin-bottom: 8px;
        }

        .section-score-card .section-score {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e3a5f;
        }

        .section-score-card .section-max {
            color: #a0aec0;
            font-size: 1rem;
        }

        .section-score-card .section-bar {
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            margin-top: 12px;
            overflow: hidden;
        }

        .section-score-card .section-bar-fill {
            height: 100%;
            border-radius: 3px;
        }

        .section-score-card .section-bar-fill.high {
            background: linear-gradient(90deg, #48bb78, #38a169);
        }

        .section-score-card .section-bar-fill.medium {
            background: linear-gradient(90deg, #ecc94b, #d69e2e);
        }

        .section-score-card .section-bar-fill.low {
            background: linear-gradient(90deg, #fc8181, #f56565);
        }

        .questions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .questions-table th,
        .questions-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }

        .questions-table th {
            background: #f7fafc;
            font-weight: 600;
            color: #4a5568;
        }

        .questions-table tr.correct {
            background: #f0fff4;
        }

        .questions-table tr.incorrect {
            background: #fff5f5;
        }

        .questions-table .status-icon {
            font-size: 1.1rem;
        }

        .questions-table .skill-tag {
            display: inline-block;
            padding: 2px 8px;
            background: #e2e8f0;
            border-radius: 4px;
            font-size: 0.75rem;
            color: #4a5568;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #4a5568;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e3a5f;
        }

        .stat-card .stat-label {
            font-size: 0.85rem;
            color: #718096;
            margin-top: 5px;
        }

        @media print {

            .navbar,
            .btn,
            .action-btn {
                display: none !important;
            }

            body {
                background: #fff;
            }

            .card {
                box-shadow: none;
                border: 1px solid #e2e8f0;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .results-table {
                font-size: 0.85rem;
            }

            .results-table th,
            .results-table td {
                padding: 10px 12px;
            }

            .hide-mobile {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <h1>📊 Assessment Admin</h1>
        <div class="navbar-links">
            <a href="view_details.php">All Results</a>
            <a href="index.html">Test Page</a>
        </div>
    </nav>

    <div class="container">
        <?php if ($viewMode === 'list'): ?>
            <!-- LIST VIEW -->
            <div class="page-header">
                <h2>Assessment Results</h2>
                <div>
                    <button class="btn btn-secondary" onclick="window.print()">🖨 Print</button>
                    <a href="index.html" class="btn">View Test</a>
                </div>
            </div>

            <?php if (empty($allResults)): ?>
                <div class="card">
                    <div class="empty-state">
                        <h3>No Results Yet</h3>
                        <p>Assessment results will appear here once candidates complete the test.</p>
                    </div>
                </div>
            <?php else: ?>

                <!-- Stats Summary -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo count($allResults); ?></div>
                        <div class="stat-label">Total Candidates</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">
                            <?php
                            $avgScore = array_sum(array_column($allResults, 'total_score')) / count($allResults);
                            echo round($avgScore, 1);
                            ?>
                        </div>
                        <div class="stat-label">Avg Score (of 80)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">
                            <?php
                            $exceptional = count(array_filter($allResults, fn($r) => $r['performance_band'] === 'Exceptional'));
                            echo $exceptional;
                            ?>
                        </div>
                        <div class="stat-label">Exceptional</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">
                            <?php
                            $proficient = count(array_filter($allResults, fn($r) => $r['performance_band'] === 'Proficient'));
                            echo $proficient;
                            ?>
                        </div>
                        <div class="stat-label">Proficient</div>
                    </div>
                </div>

                <div class="results-table-container">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th>Score</th>
                                <th>Performance</th>
                                <th class="hide-mobile">Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allResults as $row): ?>
                                <tr>
                                    <td>
                                        <div class="candidate-info">
                                            <span class="candidate-name"><?php echo htmlspecialchars($row['candidate_name']); ?></span>
                                            <span class="candidate-email"><?php echo htmlspecialchars($row['candidate_email']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="score-badge"><?php echo $row['total_score']; ?>/80</span>
                                        <span style="color: #718096; font-size: 0.85rem;">(<?php echo $row['percentage']; ?>%)</span>
                                    </td>
                                    <td>
                                        <span class="band-badge <?php echo getBandClass($row['performance_band']); ?>">
                                            <?php echo $row['performance_band']; ?>
                                        </span>
                                    </td>
                                    <td class="hide-mobile">
                                        <?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td>
                                        <a href="view_details.php?id=<?php echo $row['id']; ?>" class="btn action-btn">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- DETAIL VIEW -->
            <div class="page-header">
                <h2>Candidate Details</h2>
                <div>
                    <a href="view_details.php" class="btn btn-secondary">← Back to List</a>
                    <button class="btn" onclick="window.print()">🖨 Print Report</button>
                </div>
            </div>

            <div class="detail-grid">
                <!-- Sidebar -->
                <div class="sidebar">
                    <!-- Candidate Info Card -->
                    <div class="card candidate-card">
                        <div class="avatar">
                            <?php echo strtoupper(substr($candidate['name'], 0, 1)); ?>
                        </div>
                        <div class="name"><?php echo htmlspecialchars($candidate['name']); ?></div>
                        <div class="position">Senior Manager - Production/Operations</div>

                        <ul class="info-list">
                            <li>
                                <span class="icon">📧</span>
                                <span><?php echo htmlspecialchars($candidate['email']); ?></span>
                            </li>
                            <?php if (!empty($candidate['phone'])): ?>
                                <li>
                                    <span class="icon">📱</span>
                                    <span><?php echo htmlspecialchars($candidate['phone']); ?></span>
                                </li>
                            <?php endif; ?>
                            <li>
                                <span class="icon">📅</span>
                                <span><?php echo date('M j, Y', strtotime($createdAt)); ?></span>
                            </li>
                            <li>
                                <span class="icon">⏱</span>
                                <span><?php echo $timeTaken['formatted']; ?></span>
                            </li>
                        </ul>
                    </div>

                    <!-- Score Card -->
                    <div class="card score-card">
                        <h3>Overall Score</h3>
                        <div class="big-score"><?php echo $scores['total_score']; ?></div>
                        <div class="score-label">out of 80 points</div>
                        <div class="percentage"><?php echo $percentage; ?>%</div>
                        <div style="margin-top: 15px;">
                            <span class="band-badge <?php echo getBandClass($performanceBand); ?>" style="font-size: 1rem; padding: 8px 20px;">
                                <?php echo $performanceBand; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="main-content">
                    <!-- Section Scores -->
                    <div class="card">
                        <h3>Section Performance</h3>
                        <div class="section-scores-grid">
                            <?php foreach ($sectionNames as $idx => $name):
                                $sScore = $scores['section_scores'][$idx];
                                $sPct = ($sScore / 20) * 100;
                                $sClass = $sPct >= 70 ? 'high' : ($sPct >= 50 ? 'medium' : 'low');
                            ?>
                                <div class="section-score-card">
                                    <div class="section-name"><?php echo $name; ?></div>
                                    <div>
                                        <span class="section-score"><?php echo $sScore; ?></span>
                                        <span class="section-max">/ 20</span>
                                    </div>
                                    <div class="section-bar">
                                        <div class="section-bar-fill <?php echo $sClass; ?>" style="width: <?php echo $sPct; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Question Details -->
                    <div class="card">
                        <h3>Question-by-Question Analysis</h3>
                        <table class="questions-table">
                            <thead>
                                <tr>
                                    <th style="width: 30px;">Q#</th>
                                    <th>Question</th>
                                    <th class="hide-mobile">Skill</th>
                                    <th style="width: 80px;">Answer</th>
                                    <th style="width: 80px;">Correct</th>
                                    <th style="width: 50px;">Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($questionResults as $qNum => $qResult):
                                    $qDetail = $questionDetails[$qNum];
                                    $isCorrect = $qResult['is_correct'];
                                    $userAnswer = $qResult['user_answer'] >= 0 ? $optionLabels[$qResult['user_answer']] : '-';
                                    $correctAnswer = $optionLabels[$qResult['correct_answer']];
                                ?>
                                    <tr class="<?php echo $isCorrect ? 'correct' : 'incorrect'; ?>">
                                        <td><strong><?php echo $qNum; ?></strong></td>
                                        <td><?php echo htmlspecialchars($qDetail['text']); ?></td>
                                        <td class="hide-mobile">
                                            <span class="skill-tag"><?php echo $qDetail['skill']; ?></span>
                                        </td>
                                        <td><strong><?php echo $userAnswer; ?></strong></td>
                                        <td><strong><?php echo $correctAnswer; ?></strong></td>
                                        <td class="status-icon">
                                            <?php echo $isCorrect ? '✅' : '❌'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Skill Analysis -->
                    <div class="card">
                        <h3>Skill Assessment Summary</h3>
                        <div class="section-scores-grid">
                            <?php
                            // Group by skills
                            $skillResults = [];
                            foreach ($questionResults as $qNum => $qResult) {
                                $skill = $questionDetails[$qNum]['skill'];
                                if (!isset($skillResults[$skill])) {
                                    $skillResults[$skill] = ['correct' => 0, 'total' => 0];
                                }
                                $skillResults[$skill]['total']++;
                                if ($qResult['is_correct']) {
                                    $skillResults[$skill]['correct']++;
                                }
                            }

                            foreach ($skillResults as $skill => $data):
                                $skillPct = ($data['correct'] / $data['total']) * 100;
                                $skillClass = $skillPct >= 70 ? 'high' : ($skillPct >= 50 ? 'medium' : 'low');
                            ?>
                                <div class="section-score-card" style="border-left-color: <?php echo $skillPct >= 70 ? '#48bb78' : ($skillPct >= 50 ? '#ecc94b' : '#fc8181'); ?>">
                                    <div class="section-name"><?php echo $skill; ?></div>
                                    <div>
                                        <span class="section-score"><?php echo $data['correct']; ?></span>
                                        <span class="section-max">/ <?php echo $data['total']; ?></span>
                                    </div>
                                    <div class="section-bar">
                                        <div class="section-bar-fill <?php echo $skillClass; ?>" style="width: <?php echo $skillPct; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>