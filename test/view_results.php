<?php
/**
 * Admin Dashboard for IT Assessment Results
 * Simple interface to view all assessment submissions
 * 
 * SECURITY NOTE: In production, add proper authentication!
 */

// Include authentication
require_once 'auth.php';
checkAuth();

// Database configuration - same as in process_assessment.php
define('DB_HOST', 'localhost');
define('DB_USER', 'u947717806_it_assessment');
define('DB_PASS', '#fXfdcGe^H09');
define('DB_NAME', 'u947717806_it_assessment');

// Get database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$sort_by = $_GET['sort'] ?? 'submitted_at';
$order = $_GET['order'] ?? 'DESC';

// Build query
$where_clause = "";
if ($status_filter !== 'all') {
    $where_clause = "WHERE status = '" . $conn->real_escape_string($status_filter) . "'";
}

$query = "
    SELECT 
        id,
        candidate_name,
        candidate_email,
        score_percentage,
        status,
        correct_answers,
        total_questions,
        time_spent_seconds,
        tab_switches,
        submitted_at
    FROM assessments
    $where_clause
    ORDER BY $sort_by $order
";

$result = $conn->query($query);

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_submissions,
        AVG(score_percentage) as avg_score,
        COUNT(CASE WHEN status = 'excellent' THEN 1 END) as excellent_count,
        COUNT(CASE WHEN status = 'pass' THEN 1 END) as pass_count,
        COUNT(CASE WHEN status = 'fail' THEN 1 END) as fail_count
    FROM assessments
";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Results - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            padding: 20px;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
        }
        .results-table {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .status-excellent {
            background: #28a745;
            color: white;
        }
        .status-pass {
            background: #ffc107;
            color: #333;
        }
        .status-fail {
            background: #dc3545;
            color: white;
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-chart-line"></i> IT Assessment Dashboard</h1>
                    <p class="mb-0">Review candidate submissions and technical screening results</p>
                </div>
                <div>
                    <span class="badge bg-light text-dark me-3">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['admin_username']; ?>
                    </span>
                    <a href="auth.php?logout=1" class="btn btn-light">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_submissions']; ?></div>
                    <div class="stat-label">Total Submissions</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo round($stats['avg_score'], 1); ?>%</div>
                    <div class="stat-label">Average Score</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['excellent_count']; ?></div>
                    <div class="stat-label">Excellent (80%+)</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pass_count']; ?></div>
                    <div class="stat-label">Pass (60-79%)</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['fail_count']; ?></div>
                    <div class="stat-label">Fail (<60%)</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Candidates</option>
                        <option value="excellent" <?php echo $status_filter === 'excellent' ? 'selected' : ''; ?>>Excellent Only</option>
                        <option value="pass" <?php echo $status_filter === 'pass' ? 'selected' : ''; ?>>Pass Only</option>
                        <option value="fail" <?php echo $status_filter === 'fail' ? 'selected' : ''; ?>>Fail Only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="submitted_at" <?php echo $sort_by === 'submitted_at' ? 'selected' : ''; ?>>Submission Date</option>
                        <option value="score_percentage" <?php echo $sort_by === 'score_percentage' ? 'selected' : ''; ?>>Score</option>
                        <option value="candidate_name" <?php echo $sort_by === 'candidate_name' ? 'selected' : ''; ?>>Name</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order</label>
                    <select name="order" class="form-select">
                        <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                        <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <a href="view_results.php" class="btn btn-secondary w-100">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <div class="results-table">
            <h4 class="mb-4">
                <i class="fas fa-users"></i> Candidate Results 
                <span class="badge bg-primary"><?php echo $result->num_rows; ?> results</span>
            </h4>
            
            <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Candidate Name</th>
                            <th>Email</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Correct/Total</th>
                            <th>Time Spent</th>
                            <th>Tab Switches</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['candidate_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['candidate_email']); ?></td>
                            <td>
                                <span style="font-size: 1.2em; font-weight: bold; color: <?php 
                                    echo $row['score_percentage'] >= 80 ? '#28a745' : 
                                        ($row['score_percentage'] >= 60 ? '#ffc107' : '#dc3545'); 
                                ?>">
                                    <?php echo number_format($row['score_percentage'], 1); ?>%
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $row['status']; ?>">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $row['correct_answers']; ?> / <?php echo $row['total_questions']; ?></td>
                            <td><?php echo floor($row['time_spent_seconds'] / 60); ?> min</td>
                            <td>
                                <span class="badge <?php echo $row['tab_switches'] > 5 ? 'bg-danger' : 'bg-success'; ?>">
                                    <?php echo $row['tab_switches']; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($row['submitted_at'])); ?></td>
                            <td>
                                <a href="view_details.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Details
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No assessment submissions found matching your criteria.
            </div>
            <?php endif; ?>
        </div>

        <!-- Export Options -->
        <div class="mt-4 text-center">
            <a href="export_results.php?format=csv" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export to CSV
            </a>
            <a href="export_results.php?format=pdf" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Export to PDF
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>