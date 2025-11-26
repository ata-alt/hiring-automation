<?php
// view_results.php - Main list of all assessments
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

// Fetch all assessments
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        COUNT(CASE WHEN ans.score IS NULL THEN 1 END) as pending_count,
        COUNT(ans.id) as total_answers
    FROM assessments a
    LEFT JOIN assessment_answers ans ON a.id = ans.assessment_id
    GROUP BY a.id
    ORDER BY a.submission_date DESC
");
$stmt->execute();
$assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadge($pending_count)
{
    if ($pending_count == 0) {
        return '<span class="status-badge status-complete">Complete</span>';
    } elseif ($pending_count < 10) {
        return '<span class="status-badge status-progress">In Progress</span>';
    } else {
        return '<span class="status-badge status-pending">Pending Review</span>';
    }
}

function getScoreClass($score)
{
    if ($score >= 85) return 'score-excellent';
    if ($score >= 70) return 'score-good';
    if ($score >= 60) return 'score-average';
    return 'score-needs-improvement';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Results - Admin Dashboard</title>
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

        .logo-area {
            color: white;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
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
            margin-bottom: 10px;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        .header p {
            color: #4a5568;
            font-size: 16px;
            font-weight: 400;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 2px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #2d5f5d;
        }

        .stat-card h3 {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        .stat-card .value {
            font-size: 36px;
            font-weight: 700;
            color: #1a2332;
            margin-bottom: 5px;
        }

        .stat-card .label {
            font-size: 13px;
            color: #7f8c8d;
        }

        .assessments-table {
            background: white;
            border-radius: 2px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-header {
            background: #2d5f5d;
            color: white;
            padding: 25px 30px;
        }

        .table-header h2 {
            font-size: 20px;
            font-weight: 600;
            letter-spacing: -0.3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #e8ecef;
        }

        th {
            padding: 18px 30px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #1a2332;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 20px 30px;
            border-bottom: 1px solid #e8ecef;
            color: #4a5568;
            font-size: 14px;
        }

        tbody tr {
            transition: background 0.3s ease;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-complete {
            background: #d4edda;
            color: #155724;
        }

        .status-progress {
            background: #cce5ff;
            color: #004085;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .score-display {
            font-weight: 700;
            font-size: 18px;
        }

        .score-excellent {
            color: #28a745;
        }

        .score-good {
            color: #17a2b8;
        }

        .score-average {
            color: #ffc107;
        }

        .score-needs-improvement {
            color: #dc3545;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 2px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-decoration: none;
            display: inline-block;
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
            border: 2px solid #e8ecef;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            border-color: #2d5f5d;
        }

        .actions-cell {
            display: flex;
            gap: 10px;
        }

        .empty-state {
            padding: 60px 30px;
            text-align: center;
            color: #7f8c8d;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #4a5568;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 12px 15px;
            }

            .actions-cell {
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
            <div class="admin-badge">Admin Dashboard</div>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <h1>Assessment Results</h1>
            <p>Leadership Assessment - Virtual Styling Operations Manager Position</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Assessments</h3>
                <div class="value"><?php echo count($assessments); ?></div>
                <div class="label">All submissions</div>
            </div>

            <div class="stat-card">
                <h3>Complete Reviews</h3>
                <div class="value">
                    <?php
                    $complete = array_filter($assessments, function ($a) {
                        return $a['pending_count'] == 0;
                    });
                    echo count($complete);
                    ?>
                </div>
                <div class="label">Fully scored</div>
            </div>

            <div class="stat-card">
                <h3>Average Score</h3>
                <div class="value">
                    <?php
                    $avg = count($assessments) > 0 ? array_sum(array_column($assessments, 'total_score')) / count($assessments) : 0;
                    echo number_format($avg, 1);
                    ?>
                </div>
                <div class="label">Out of 100 points</div>
            </div>

            <div class="stat-card">
                <h3>Pending Reviews</h3>
                <div class="value">
                    <?php
                    $pending = array_filter($assessments, function ($a) {
                        return $a['pending_count'] > 0;
                    });
                    echo count($pending);
                    ?>
                </div>
                <div class="label">Need attention</div>
            </div>
        </div>

        <div class="assessments-table">
            <div class="table-header">
                <h2>All Submissions</h2>
            </div>

            <?php if (count($assessments) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Submission Date</th>
                            <th>Time Taken</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Pending</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assessments as $assessment): ?>
                            <?php
                            $hours = floor($assessment['total_time'] / 3600);
                            $minutes = floor(($assessment['total_time'] % 3600) / 60);
                            $time_display = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
                            $score_class = getScoreClass($assessment['total_score']);
                            ?>
                            <tr>
                                <td><strong>#<?php echo $assessment['id']; ?></strong></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($assessment['submission_date'])); ?></td>
                                <td><?php echo $time_display; ?></td>
                                <td>
                                    <span class="score-display <?php echo $score_class; ?>">
                                        <?php echo number_format($assessment['total_score'], 1); ?>/100
                                    </span>
                                </td>
                                <td><?php echo getStatusBadge($assessment['pending_count']); ?></td>
                                <td><?php echo $assessment['pending_count']; ?> questions</td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="view_details.php?id=<?php echo $assessment['id']; ?>" class="btn btn-primary">View Details</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3>No Assessments Yet</h3>
                    <p>Assessment submissions will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>