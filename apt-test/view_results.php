<?php

/**
 * View Results - Candidate Results Page
 * Displays assessment results to the candidate after completion
 */

session_start();

// Check if results exist in session
if (!isset($_SESSION['assessment_result'])) {
    header('Location: index.html');
    exit;
}

$result = $_SESSION['assessment_result'];
$candidate = $result['candidate'];
$scores = $result['scores'];
$performance = $result['performance'];
$timeTaken = $result['time_taken'];
$sectionNames = $result['section_names'];
$questionDetails = $result['question_details'];
$optionLabels = $result['option_labels'];

// Calculate percentage
$percentage = round(($scores['total_score'] / 80) * 100, 1);

// Clear session after displaying (optional - comment out to allow refresh)
// unset($_SESSION['assessment_result']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Results | Senior Manager - Production/Operations</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            color: #1a202c;
            line-height: 1.6;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 50%, #1e3a5f 100%);
            color: #fff;
            padding: 40px;
            border-radius: 16px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(30, 58, 95, 0.3);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 60%);
        }

        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .header p {
            opacity: 0.9;
            font-size: 1rem;
            position: relative;
            z-index: 1;
        }

        .candidate-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #90cdf4;
            margin-top: 10px;
            position: relative;
            z-index: 1;
        }

        .results-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .score-section {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 50px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .score-circle {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 10px 30px rgba(30, 58, 95, 0.4);
            position: relative;
        }

        .score-circle::before {
            content: '';
            position: absolute;
            top: 8px;
            left: 8px;
            right: 8px;
            bottom: 8px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
        }

        .score-circle .score {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .score-circle .total {
            font-size: 1rem;
            opacity: 0.8;
            margin-top: 5px;
        }

        .score-details {
            text-align: left;
        }

        .score-detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .score-detail-item .icon {
            width: 40px;
            height: 40px;
            background: #f7fafc;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .score-detail-item .label {
            color: #718096;
            font-size: 0.9rem;
        }

        .score-detail-item .value {
            font-weight: 600;
            color: #1e3a5f;
        }

        .band {
            display: inline-block;
            padding: 12px 32px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1.2rem;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .band.exceptional {
            background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
            color: #22543d;
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
        }

        .band.proficient {
            background: linear-gradient(135deg, #bee3f8 0%, #90cdf4 100%);
            color: #2a4365;
            box-shadow: 0 4px 15px rgba(66, 153, 225, 0.3);
        }

        .band.developing {
            background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%);
            color: #92400e;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .band.below {
            background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%);
            color: #c53030;
            box-shadow: 0 4px 15px rgba(245, 101, 101, 0.3);
        }

        .band-description {
            color: #4a5568;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .recommendation {
            background: #f7fafc;
            padding: 15px 25px;
            border-radius: 10px;
            color: #2d3748;
            font-size: 0.95rem;
            display: inline-block;
            margin-top: 10px;
        }

        .section-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .section-card h3 {
            color: #1e3a5f;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.15rem;
        }

        .section-scores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .section-score-item {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .section-score-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #1e3a5f 0%, #2d5a87 100%);
        }

        .section-score-item .name {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 8px;
        }

        .section-score-item .score-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-score-item .score-val {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e3a5f;
        }

        .section-score-item .score-max {
            color: #a0aec0;
            font-size: 1rem;
        }

        .section-score-item .percentage {
            font-size: 0.9rem;
            padding: 4px 10px;
            border-radius: 15px;
            font-weight: 600;
        }

        .percentage.high {
            background: #c6f6d5;
            color: #22543d;
        }

        .percentage.medium {
            background: #fef3c7;
            color: #92400e;
        }

        .percentage.low {
            background: #fed7d7;
            color: #c53030;
        }

        .progress-bar-section {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            margin-top: 12px;
            overflow: hidden;
        }

        .progress-bar-section .fill {
            height: 100%;
            border-radius: 4px;
            transition: width 1s ease;
        }

        .progress-bar-section .fill.high {
            background: linear-gradient(90deg, #48bb78 0%, #38a169 100%);
        }

        .progress-bar-section .fill.medium {
            background: linear-gradient(90deg, #ecc94b 0%, #d69e2e 100%);
        }

        .progress-bar-section .fill.low {
            background: linear-gradient(90deg, #fc8181 0%, #f56565 100%);
        }

        .question-review {
            margin-top: 20px;
        }

        .review-toggle {
            background: #1e3a5f;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 auto;
            transition: all 0.3s;
        }

        .review-toggle:hover {
            background: #2d5a87;
            transform: translateY(-2px);
        }

        .review-toggle .arrow {
            transition: transform 0.3s;
        }

        .review-toggle.open .arrow {
            transform: rotate(180deg);
        }

        .review-content {
            display: none;
            margin-top: 25px;
        }

        .review-content.open {
            display: block;
        }

        .review-question {
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .review-question:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .review-question.correct {
            border-left: 4px solid #48bb78;
            background: linear-gradient(90deg, #f0fff4 0%, #fff 20%);
        }

        .review-question.incorrect {
            border-left: 4px solid #e53e3e;
            background: linear-gradient(90deg, #fff5f5 0%, #fff 20%);
        }

        .review-question .q-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            flex-wrap: wrap;
        }

        .review-question .q-info {
            flex: 1;
        }

        .review-question .q-number {
            font-weight: 700;
            color: #1e3a5f;
            font-size: 1rem;
        }

        .review-question .q-section {
            font-size: 0.85rem;
            color: #718096;
            margin-top: 2px;
        }

        .review-question .q-text {
            font-size: 0.9rem;
            color: #4a5568;
            margin-top: 5px;
        }

        .review-question .status {
            font-size: 0.85rem;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            white-space: nowrap;
        }

        .review-question.correct .status {
            background: #c6f6d5;
            color: #22543d;
        }

        .review-question.incorrect .status {
            background: #fed7d7;
            color: #c53030;
        }

        .review-question .answers {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }

        .review-question .answer-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .review-question .your-answer {
            color: #718096;
        }

        .review-question .correct-answer {
            color: #22543d;
            font-weight: 600;
        }

        .review-question.correct .your-answer {
            color: #22543d;
            font-weight: 600;
        }

        .info-footer {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-top: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .info-footer p {
            color: #718096;
            font-size: 0.95rem;
            margin-bottom: 10px;
        }

        .info-footer .result-id {
            font-family: monospace;
            background: #f7fafc;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.9rem;
            color: #4a5568;
            display: inline-block;
        }

        .btn {
            background: linear-gradient(135deg, #2d5a87 0%, #1e3a5f 100%);
            color: #fff;
            border: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 58, 95, 0.4);
        }

        .btn-secondary {
            background: #fff;
            color: #1e3a5f;
            border: 2px solid #1e3a5f;
        }

        .btn-secondary:hover {
            background: #f7fafc;
        }

        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            color: #fff;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(30, 58, 95, 0.4);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 100;
        }

        .print-btn:hover {
            transform: scale(1.1);
        }

        @media print {
            body {
                background: #fff;
            }

            .print-btn,
            .review-toggle,
            .btn {
                display: none !important;
            }

            .review-content {
                display: block !important;
            }

            .container {
                max-width: 100%;
            }
        }

        @media (max-width: 600px) {
            .header {
                padding: 25px 20px;
            }

            .header h1 {
                font-size: 1.4rem;
            }

            .results-card {
                padding: 25px 20px;
            }

            .score-section {
                gap: 30px;
            }

            .score-circle {
                width: 150px;
                height: 150px;
            }

            .score-circle .score {
                font-size: 2.8rem;
            }

            .score-details {
                text-align: center;
            }

            .print-btn {
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Assessment Complete</h1>
            <p>Senior Manager - Production/Operations</p>
            <div class="candidate-name"><?php echo htmlspecialchars($candidate['name']); ?></div>
        </div>

        <!-- Main Results Card -->
        <div class="results-card">
            <div class="score-section">
                <div class="score-circle">
                    <div class="score"><?php echo $scores['total_score']; ?></div>
                    <div class="total">out of 80</div>
                </div>

                <div class="score-details">
                    <div class="score-detail-item">
                        <div class="icon">📊</div>
                        <div>
                            <div class="label">Percentage</div>
                            <div class="value"><?php echo $percentage; ?>%</div>
                        </div>
                    </div>
                    <div class="score-detail-item">
                        <div class="icon">⏱</div>
                        <div>
                            <div class="label">Time Taken</div>
                            <div class="value"><?php echo $timeTaken['formatted']; ?></div>
                        </div>
                    </div>
                    <div class="score-detail-item">
                        <div class="icon">✓</div>
                        <div>
                            <div class="label">Correct Answers</div>
                            <div class="value"><?php echo $scores['total_score'] / 4; ?> of 20</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="band <?php echo $performance['class']; ?>">
                <?php echo $performance['band']; ?>
            </div>

            <p class="band-description"><?php echo $performance['description']; ?></p>

            <div class="recommendation">
                <strong>Recommendation:</strong> <?php echo $performance['recommendation']; ?>
            </div>
        </div>

        <!-- Section Breakdown -->
        <div class="section-card">
            <h3>📈 Section Breakdown</h3>
            <div class="section-scores-grid">
                <?php foreach ($sectionNames as $sectionIndex => $sectionName):
                    $sectionScore = $scores['section_scores'][$sectionIndex];
                    $sectionPercentage = ($sectionScore / 20) * 100;
                    $percentClass = $sectionPercentage >= 70 ? 'high' : ($sectionPercentage >= 50 ? 'medium' : 'low');
                ?>
                    <div class="section-score-item">
                        <div class="name"><?php echo $sectionName; ?></div>
                        <div class="score-row">
                            <div>
                                <span class="score-val"><?php echo $sectionScore; ?></span>
                                <span class="score-max">/ 20</span>
                            </div>
                            <span class="percentage <?php echo $percentClass; ?>"><?php echo $sectionPercentage; ?>%</span>
                        </div>
                        <div class="progress-bar-section">
                            <div class="fill <?php echo $percentClass; ?>" style="width: <?php echo $sectionPercentage; ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Question Review -->
        <div class="section-card">
            <h3>📝 Question Review</h3>

            <button class="review-toggle" onclick="toggleReview()">
                <span>View Detailed Results</span>
                <span class="arrow">▼</span>
            </button>

            <div class="review-content" id="reviewContent">
                <?php foreach ($scores['question_results'] as $qNum => $qResult):
                    $qDetail = $questionDetails[$qNum];
                    $sectionName = $sectionNames[$qDetail['section']];
                    $isCorrect = $qResult['is_correct'];
                    $userAnswerLabel = $qResult['user_answer'] >= 0 ? $optionLabels[$qResult['user_answer']] : 'Not answered';
                    $correctAnswerLabel = $optionLabels[$qResult['correct_answer']];
                ?>
                    <div class="review-question <?php echo $isCorrect ? 'correct' : 'incorrect'; ?>">
                        <div class="q-header">
                            <div class="q-info">
                                <div class="q-number">Question <?php echo $qNum; ?></div>
                                <div class="q-section"><?php echo $sectionName; ?></div>
                                <div class="q-text"><?php echo $qDetail['text']; ?></div>
                            </div>
                            <span class="status">
                                <?php echo $isCorrect ? '✓ Correct (+4 pts)' : '✗ Incorrect (0 pts)'; ?>
                            </span>
                        </div>
                        <div class="answers">
                            <div class="answer-row">
                                <span class="your-answer">Your answer: <?php echo $userAnswerLabel; ?></span>
                                <?php if (!$isCorrect): ?>
                                    <span class="correct-answer">Correct answer: <?php echo $correctAnswerLabel; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="info-footer">
            <p>Your results have been recorded and will be reviewed by our hiring team.</p>
            <p>You will receive an email at <strong><?php echo htmlspecialchars($candidate['email']); ?></strong> with next steps.</p>
            <div class="result-id">Reference ID: <?php echo htmlspecialchars($result['id']); ?></div>
            <br>
            <p style="margin-top: 15px; font-size: 0.85rem; color: #a0aec0;">
                Completed on <?php echo date('F j, Y \a\t g:i A', strtotime($result['processed_at'])); ?>
            </p>
        </div>
    </div>

    <!-- Print Button -->
    <button class="print-btn" onclick="window.print()" title="Print Results">
        🖨
    </button>

    <script>
        function toggleReview() {
            const content = document.getElementById('reviewContent');
            const button = document.querySelector('.review-toggle');

            content.classList.toggle('open');
            button.classList.toggle('open');

            if (content.classList.contains('open')) {
                button.querySelector('span:first-child').textContent = 'Hide Detailed Results';
            } else {
                button.querySelector('span:first-child').textContent = 'View Detailed Results';
            }
        }

        // Animate progress bars on load
        document.addEventListener('DOMContentLoaded', function() {
            const fills = document.querySelectorAll('.progress-bar-section .fill');
            fills.forEach(fill => {
                const width = fill.style.width;
                fill.style.width = '0';
                setTimeout(() => {
                    fill.style.width = width;
                }, 100);
            });
        });
    </script>
</body>

</html>