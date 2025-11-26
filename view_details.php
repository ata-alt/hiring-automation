<?php
// view_details.php
session_start();

$db_host = 'localhost';
$db_name = 'leadership_assessment';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$assessment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$assessment_id) {
    header("Location: view_results.php");
    exit;
}

// Handle manual scoring
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score_question'])) {
    $question_id = (int)$_POST['question_id'];
    $score = (float)$_POST['score'];

    $stmt = $pdo->prepare("UPDATE assessment_answers SET score = ? WHERE id = ?");
    $stmt->execute([$score, $question_id]);

    // Recalculate total score
    $stmt = $pdo->prepare("
        SELECT SUM(score) as total 
        FROM assessment_answers 
        WHERE assessment_id = ? AND score IS NOT NULL
    ");
    $stmt->execute([$assessment_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("UPDATE assessments SET total_score = ? WHERE id = ?");
    $stmt->execute([$result['total'], $assessment_id]);

    header("Location: view_details.php?id=" . $assessment_id);
    exit;
}

// Fetch assessment data
$stmt = $pdo->prepare("SELECT * FROM assessments WHERE id = ?");
$stmt->execute([$assessment_id]);
$assessment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assessment) {
    die("Assessment not found");
}

// Fetch all answers
$stmt = $pdo->prepare("
    SELECT * FROM assessment_answers 
    WHERE assessment_id = ? 
    ORDER BY question_number
");
$stmt->execute([$assessment_id]);
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Question details
$question_details = [
    1 => [
        'title' => 'Team Turnaround Action Plan',
        'description' => '90-day plan for distributed team with morale issues',
        'criteria' => [
            'Diagnostic approach and stakeholder engagement (3 pts)',
            'Communication strategy and trust-building (3 pts)',
            'Performance metrics and accountability systems (4 pts)'
        ]
    ],
    2 => [
        'title' => 'Scaling Team - Hiring Approach',
        'description' => 'Scale from 10 to 30 members in 8 weeks',
        'correct_answer' => 'B',
        'options' => [
            'A' => 'Hire all 20 simultaneously with group onboarding',
            'B' => 'Phased hiring with cohorts of 5-7, mentor system',
            'C' => 'Outsource hiring to 3PL vendor',
            'D' => 'Prioritize speed over cultural fit'
        ]
    ],
    3 => [
        'title' => 'Accountability Framework',
        'description' => 'Framework for 20+ image quality specialists',
        'criteria' => [
            'Specificity and measurability of KPIs (3 pts)',
            'Review structure and frequency (2 pts)',
            'Performance management approach (3 pts)'
        ]
    ],
    4 => [
        'title' => 'Top Performer Resignation',
        'description' => 'Response to top stylist resignation',
        'correct_answer' => 'B',
        'options' => [
            'A' => 'Immediately recruit replacement',
            'B' => 'Exit interview and retention conversation',
            'C' => 'Redistribute workload',
            'D' => 'Escalate for counter-offer'
        ]
    ],
    5 => [
        'title' => 'Performance Disparity Analysis',
        'description' => 'In-house 95% vs outsourced 73% acceptance rate',
        'criteria' => [
            'Root cause analysis approach (2 pts)',
            'Short-term interventions (1.5 pts)',
            'Long-term systemic solutions (1.5 pts)'
        ]
    ],
    6 => [
        'title' => 'Project Launch Planning',
        'description' => '500 AI-generated images in 6 weeks',
        'criteria' => [
            'Realistic capacity planning and mathematical accuracy (5 pts)',
            'Risk identification and mitigation (5 pts)',
            'Project management methodology (5 pts)'
        ]
    ],
    7 => [
        'title' => 'Project Recovery Intervention',
        'description' => '15% behind due to rework rates',
        'correct_answer' => 'B',
        'options' => [
            'A' => 'Request timeline extension',
            'B' => 'Root cause analysis and corrective training',
            'C' => 'Add weekend shifts',
            'D' => 'Reduce quality standards temporarily'
        ]
    ],
    8 => [
        'title' => 'Multi-Timezone Scheduling',
        'description' => 'Production schedule across PST, GMT, IST',
        'criteria' => [
            'Timezone coordination strategy (2 pts)',
            'Handoff protocols and documentation (2 pts)',
            'Communication and collaboration approach (2 pts)'
        ]
    ],
    9 => [
        'title' => '3PL Vendor Onboarding',
        'description' => 'Onboarding plan for new vendor handling 40% volume',
        'criteria' => [
            'Onboarding structure and timeline (2 pts)',
            'Training and documentation approach (2 pts)',
            'Performance benchmarks (2 pts)',
            'Communication protocols (2 pts)'
        ]
    ],
    10 => [
        'title' => 'Vendor Performance Issues',
        'description' => 'Vendor consistently 48-72 hours late',
        'criteria' => [
            'Immediate action plan (2 pts)',
            'Performance improvement framework (2 pts)',
            'Decision-making criteria (1 pt)'
        ]
    ],
    11 => [
        'title' => 'Critical SLA Metric',
        'description' => 'Most critical vendor SLA metric',
        'correct_answer' => 'B',
        'options' => [
            'A' => 'Cost per image',
            'B' => 'On-time delivery + quality acceptance (composite)',
            'C' => 'Monthly volume capacity',
            'D' => 'Communication response time'
        ]
    ],
    12 => [
        'title' => 'Vendor Scorecard Design',
        'description' => 'Create vendor scorecard template',
        'criteria' => [
            'Comprehensiveness of metrics (2 pts)',
            'Appropriate weighting rationale (1 pt)'
        ]
    ],
    13 => [
        'title' => 'Styling Quality Evaluation',
        'description' => 'AI-generated image quality assessment',
        'criteria' => [
            'Fashion/styling knowledge and quality criteria (3 pts)',
            'Cross-functional collaboration approach (3 pts)',
            'Problem analysis methodology (2 pts)',
            'Documentation and prevention strategy (2 pts)'
        ]
    ],
    14 => [
        'title' => 'Digital Image Quality Standards',
        'description' => 'Critical factors for virtual styling',
        'correct_answer' => ['A', 'B', 'C', 'D', 'F'],
        'options' => [
            'A' => 'Color accuracy and brand palette consistency',
            'B' => 'Fabric texture and material realism',
            'C' => 'Model pose and body proportions',
            'D' => 'Shadow and lighting consistency',
            'E' => 'Background styling and environmental context',
            'F' => 'Outfit cohesion and styling logic'
        ]
    ],
    15 => [
        'title' => 'AI Accessory Rendering Issues',
        'description' => 'Pattern of incorrect accessory scale/positioning',
        'criteria' => [
            'Documentation approach (2 pts)',
            'Interim quality controls (2 pts)',
            'Training methodology (1 pt)'
        ]
    ],
    16 => [
        'title' => 'Production Data Analysis',
        'description' => 'Q3 data analysis and intervention',
        'criteria' => [
            'Trend identification and analysis (3 pts)',
            'Hypothesis development (3 pts)',
            'Data requirements (2 pts)',
            'Actionable intervention (2 pts)'
        ]
    ],
    17 => [
        'title' => 'AI Integration Strategy',
        'description' => '30% time reduction while maintaining quality',
        'criteria' => [
            'Strategic automation approach (2 pts)',
            'Change management and team communication (2 pts)',
            'Pilot methodology and success metrics (2 pts)',
            'Quality maintenance strategy (2 pts)'
        ]
    ],
    18 => [
        'title' => 'Emerging Technologies',
        'description' => '3 technologies impacting workflows in 18-24 months',
        'criteria' => [
            'Relevance and currency of technologies (1.5 pts)',
            'Practical application understanding (1.5 pts)'
        ]
    ],
    19 => [
        'title' => 'Industry Trend Awareness',
        'description' => 'Staying current with industry trends',
        'criteria' => [
            'Diversity and quality of resources (1 pt)',
            'Proactive learning approach (1 pt)'
        ]
    ],
    20 => [
        'title' => 'SOP Outline Development',
        'description' => 'Virtual Styling Quality Review Process SOP',
        'criteria' => [
            'Logical structure and completeness (2 pts)',
            'Decision points and governance (2 pts)',
            'Measurability and maintenance (2 pts)'
        ]
    ],
    21 => [
        'title' => 'SOP Critique and Rewrite',
        'description' => 'Improve vague SOP excerpt',
        'criteria' => [
            'Critique accuracy (1 pt)',
            'Rewrite quality and specificity (2 pts)',
            'Professional SOP standards (1 pt)'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Details - ID <?php echo $assessment_id; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            color: #2c3e50;
        }

        .top-bar {
            background: #2d5f5d;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .top-bar-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-area img {
            height: 50px;
            width: auto;
            display: block;
        }

        .admin-badge {
            background: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            color: #2d5f5d;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header {
            background: white;
            padding: 40px;
            border-radius: 2px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 32px;
            color: #1a2332;
            margin-bottom: 15px;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        .header-meta {
            display: flex;
            gap: 30px;
            color: #4a5568;
            font-size: 14px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meta-item strong {
            color: #1a2332;
        }

        .score-highlight {
            font-size: 24px;
            font-weight: 700;
            color: #2d5f5d;
        }

        .question-detail {
            background: white;
            border-radius: 2px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .question-header {
            background: #2d5f5d;
            color: white;
            padding: 25px 30px;
            position: relative;
        }

        .question-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2d5f5d 0%, #245a58 100%);
        }

        .question-title {
            font-size: 20px;
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: -0.3px;
        }

        .question-subtitle {
            opacity: 0.9;
            font-size: 14px;
            font-weight: 300;
        }

        .question-body {
            padding: 35px 35px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2d5f5d;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .answer-display {
            background: #fafbfc;
            padding: 20px;
            border-radius: 2px;
            border: 2px solid #e8ecef;
            line-height: 1.8;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 14px;
            color: #4a5568;
        }

        .criteria-list {
            list-style: none;
            padding: 0;
        }

        .criteria-list li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: #4a5568;
            font-size: 14px;
            line-height: 1.7;
        }

        .criteria-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #2d5f5d;
            font-weight: bold;
            font-size: 16px;
        }

        .scoring-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 2px;
            border: 2px solid #e8ecef;
        }

        .score-input-group {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
        }

        .score-input-group label {
            font-weight: 600;
            color: #1a2332;
            font-size: 14px;
        }

        .score-input-group input {
            width: 120px;
            padding: 12px 18px;
            border: 2px solid #e8ecef;
            border-radius: 2px;
            font-size: 16px;
            font-family: 'Montserrat', sans-serif;
            background: white;
            transition: all 0.3s ease;
        }

        .score-input-group input:focus {
            outline: none;
            border-color: #2d5f5d;
            box-shadow: 0 0 0 3px rgba(45, 95, 93, 0.1);
        }

        .score-input-group button {
            padding: 12px 28px;
            background: #2d5f5d;
            color: white;
            border: none;
            border-radius: 2px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.3px;
        }

        .score-input-group button:hover {
            background: #245a58;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(45, 95, 93, 0.3);
        }

        .current-score {
            display: inline-block;
            padding: 12px 24px;
            background: #d4edda;
            color: #155724;
            border-radius: 2px;
            font-weight: 700;
            font-size: 18px;
            border: 2px solid #c3e6cb;
        }

        .pending-score {
            display: inline-block;
            padding: 12px 24px;
            background: #fff3cd;
            color: #856404;
            border-radius: 2px;
            font-weight: 700;
            font-size: 18px;
            border: 2px solid #ffeaa7;
        }

        .options-list {
            list-style: none;
            padding: 0;
            margin-top: 15px;
        }

        .options-list li {
            padding: 15px 18px;
            margin-bottom: 10px;
            background: #fafbfc;
            border-radius: 2px;
            border: 2px solid #e8ecef;
            font-size: 14px;
            color: #4a5568;
        }

        .options-list li.correct {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
            font-weight: 600;
        }

        .options-list li.selected {
            border-color: #2d5f5d;
            border-width: 2px;
            font-weight: 600;
            background: #f0f7fc;
        }

        .options-list li.correct.selected {
            background: #d4edda;
            border-color: #28a745;
        }

        .option-label {
            font-weight: 700;
            margin-right: 8px;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 10px;
        }

        .badge-correct {
            background: #28a745;
            color: white;
        }

        .badge-selected {
            background: #2d5f5d;
            color: white;
        }

        .actions {
            margin-top: 40px;
            display: flex;
            gap: 15px;
            justify-content: center;
            padding-top: 30px;
            border-top: 2px solid #e8ecef;
        }

        .btn {
            padding: 14px 36px;
            border: none;
            border-radius: 2px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .btn-primary {
            background: #2d5f5d;
            color: white;
        }

        .btn-primary:hover {
            background: #245a58;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(45, 95, 93, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: #1a2332;
            border: 2px solid #1a2332;
        }

        .btn-secondary:hover {
            background: #1a2332;
            color: white;
        }

        @media print {

            .scoring-section form,
            .actions,
            .top-bar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .header-meta {
                flex-direction: column;
                gap: 15px;
            }

            .question-body {
                padding: 25px 20px;
            }

            .score-input-group {
                flex-direction: column;
                align-items: flex-start;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="top-bar">
        <div class="top-bar-content">
            <div class="logo-area">
                <img src="https://noondalton.com/wp-content/uploads/2021/12/nd-logo-blue-globe-75h.png" alt="Noon Dalton">
            </div>
            <div class="admin-badge">Assessment Details</div>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <h1>Detailed Assessment Report</h1>
            <div class="header-meta">
                <div class="meta-item">
                    <strong>Assessment ID:</strong> #<?php echo $assessment_id; ?>
                </div>
                <div class="meta-item">
                    <strong>Submitted:</strong> <?php echo date('F j, Y g:i A', strtotime($assessment['submission_date'])); ?>
                </div>
                <div class="meta-item">
                    <strong>Total Score:</strong>
                    <span class="score-highlight"><?php echo number_format($assessment['total_score'], 1); ?>/100</span>
                </div>
            </div>
        </div>

        <?php foreach ($answers as $answer): ?>
            <?php
            $q_num = $answer['question_number'];
            $detail = $question_details[$q_num];
            ?>
            <div class="question-detail">
                <div class="question-header">
                    <div class="question-title">Question <?php echo $q_num; ?>: <?php echo $detail['title']; ?></div>
                    <div class="question-subtitle"><?php echo $detail['description']; ?></div>
                </div>

                <div class="question-body">
                    <?php if (isset($detail['criteria'])): ?>
                        <div class="section">
                            <div class="section-label">Scoring Criteria</div>
                            <ul class="criteria-list">
                                <?php foreach ($detail['criteria'] as $criterion): ?>
                                    <li><?php echo $criterion; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($detail['options'])): ?>
                        <div class="section">
                            <div class="section-label">Answer Options</div>
                            <ul class="options-list">
                                <?php foreach ($detail['options'] as $key => $option): ?>
                                    <?php
                                    $isCorrect = false;
                                    $isSelected = false;

                                    if (is_array($detail['correct_answer'])) {
                                        $isCorrect = in_array($key, $detail['correct_answer']);
                                        $selected = explode(',', $answer['answer']);
                                        $isSelected = in_array($key, $selected);
                                    } else {
                                        $isCorrect = ($key === $detail['correct_answer']);
                                        $isSelected = ($answer['answer'] === $key);
                                    }

                                    $classes = [];
                                    if ($isCorrect) $classes[] = 'correct';
                                    if ($isSelected) $classes[] = 'selected';
                                    ?>
                                    <li class="<?php echo implode(' ', $classes); ?>">
                                        <span class="option-label"><?php echo $key; ?>)</span>
                                        <?php echo $option; ?>
                                        <?php if ($isCorrect): ?>
                                            <span class="badge badge-correct">Correct</span>
                                        <?php endif; ?>
                                        <?php if ($isSelected): ?>
                                            <span class="badge badge-selected">Selected</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="section">
                        <div class="section-label">Candidate Answer</div>
                        <div class="answer-display"><?php echo htmlspecialchars($answer['answer']); ?></div>
                    </div>

                    <div class="section">
                        <div class="section-label">Scoring</div>
                        <div class="scoring-section">
                            <?php if ($answer['score'] !== null): ?>
                                <div class="current-score">
                                    Score: <?php echo number_format($answer['score'], 1); ?> / <?php echo $answer['max_points']; ?> points
                                </div>

                                <?php if ($answer['question_type'] !== 'multiple_choice' && $answer['question_type'] !== 'multiple_select'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="question_id" value="<?php echo $answer['id']; ?>">
                                        <div class="score-input-group">
                                            <label>Update Score:</label>
                                            <input type="number" name="score" step="0.5" min="0" max="<?php echo $answer['max_points']; ?>" value="<?php echo $answer['score']; ?>" required>
                                            <span>/ <?php echo $answer['max_points']; ?></span>
                                            <button type="submit" name="score_question">Update</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="pending-score">
                                    Pending Manual Review (<?php echo $answer['max_points']; ?> points possible)
                                </div>

                                <form method="POST">
                                    <input type="hidden" name="question_id" value="<?php echo $answer['id']; ?>">
                                    <div class="score-input-group">
                                        <label>Assign Score:</label>
                                        <input type="number" name="score" step="0.5" min="0" max="<?php echo $answer['max_points']; ?>" required>
                                        <span>/ <?php echo $answer['max_points']; ?></span>
                                        <button type="submit" name="score_question">Submit Score</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="actions">
            <a href="view_results.php" class="btn btn-primary">Back to All Assessments</a>
            <button onclick="window.print()" class="btn btn-secondary">Print Report</button>
        </div>
    </div>
</body>

</html>