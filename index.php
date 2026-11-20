<?php
include 'db_connect.php';

// Handle Filters securely
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_date = isset($_GET['filter_date']) ? trim($_GET['filter_date']) : '';
$filter_status = isset($_GET['filter_status']) ? trim($_GET['filter_status']) : '';
$filter_section = isset($_GET['filter_section']) ? trim($_GET['filter_section']) : '';

$where_clauses = [];

if (!empty($search)) {
    $safe_search = $conn->real_escape_string($search);
    $where_clauses[] = "(student_id LIKE '%$safe_search%' OR full_name LIKE '%$safe_search%' OR course_section LIKE '%$safe_search%')";
}

if (!empty($filter_date)) {
    $safe_date = $conn->real_escape_string($filter_date);
    $where_clauses[] = "attendance_date = '$safe_date'";
}

if (!empty($filter_status)) {
    $safe_status = $conn->real_escape_string($filter_status);
    $where_clauses[] = "status = '$safe_status'";
}

if (!empty($filter_section)) {
    $safe_section = $conn->real_escape_string($filter_section);
    $where_clauses[] = "course_section = '$safe_section'";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$sql = "SELECT * FROM attendance_records $where_sql ORDER BY attendance_id ASC";
$result = $conn->query($sql);

// Fetch distinct course & sections for the dropdown filter
$sections_result = $conn->query("SELECT DISTINCT course_section FROM attendance_records ORDER BY course_section ASC");

// Dashboard Summary Queries
$total_records = $conn->query("SELECT COUNT(*) as count FROM attendance_records")->fetch_assoc()['count'];
$total_present = $conn->query("SELECT COUNT(*) as count FROM attendance_records WHERE status='Present'")->fetch_assoc()['count'];
$total_late = $conn->query("SELECT COUNT(*) as count FROM attendance_records WHERE status='Late'")->fetch_assoc()['count'];
$total_absent = $conn->query("SELECT COUNT(*) as count FROM attendance_records WHERE status='Absent'")->fetch_assoc()['count'];

// Activity Log Query
$logs_result = $conn->query("SELECT * FROM attendance_records ORDER BY attendance_id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" id="htmlTheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISUFST Attendance Monitoring System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Apply saved theme immediately before page paints
        const savedTheme = localStorage.getItem('isufst_theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    </script>
    <style>
        body {
            background: url('isufst_bg2.png') no-repeat center center fixed;
            background-size: cover;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            transition: background-color 0.3s ease;
        }

        [data-bs-theme="dark"] body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: rgba(10, 12, 18, 0.55);
            z-index: -1;
            pointer-events: none;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
            border-radius: 16px;
            transition: background 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        [data-bs-theme="dark"] .glass-card {
            background: rgba(24, 27, 34, 0.82);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.45);
            color: #f8f9fa;
        }

        .glass-nav {
            background: rgba(13, 110, 253, 0.85) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            transition: background 0.3s ease;
        }

        [data-bs-theme="dark"] .glass-nav {
            background: rgba(18, 21, 28, 0.88) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: rgba(35, 39, 48, 0.9);
            border-color: rgba(255, 255, 255, 0.15);
            color: #f8f9fa;
        }

        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: rgba(42, 47, 58, 0.95);
            border-color: #0d6efd;
            color: #ffffff;
        }

        .table-glass {
            background: transparent !important;
        }
        
        .table-glass th, .table-glass td {
            background-color: transparent !important;
        }

        .table-glass thead th {
            background-color: rgba(33, 37, 41, 0.92) !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .table-glass thead th {
            background-color: rgba(15, 17, 22, 0.95) !important;
            color: #ffffff !important;
        }

        .soft-btn {
            border-radius: 12px;
            transition: all 0.2s ease-in-out;
        }
        .soft-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.18);
        }

        /* Enlarged clock by ~30% matching add/edit pages */
        .enlarged-clock {
            font-size: 1.15rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top px-3 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
            <img src="isufst_lg2.png" alt="Logo" width="54" height="54" style="object-fit: contain;">
            <span>ISUFST Attendance Monitoring System</span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white font-monospace enlarged-clock d-none d-md-inline" id="liveClock">Loading time...</span>
            <button class="btn btn-sm btn-light soft-btn fw-semibold" id="themeToggle" onclick="toggleTheme()">🌙 Dark</button>
        </div>
    </div>
</nav>

<div class="container my-4">
    
    <!-- Dashboard Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card glass-card border-primary border-top border-4">
                <div class="card-body py-3">
                    <span class="text-body-secondary small fw-semibold">Total Records</span>
                    <h3 class="mb-0 fw-bold"><?= $total_records ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card glass-card border-success border-top border-4">
                <div class="card-body py-3">
                    <span class="text-body-secondary small fw-semibold">Total Present</span>
                    <h3 class="mb-0 fw-bold text-success"><?= $total_present ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card glass-card border-warning border-top border-4">
                <div class="card-body py-3">
                    <span class="text-body-secondary small fw-semibold">Total Late</span>
                    <h3 class="mb-0 fw-bold text-warning"><?= $total_late ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card glass-card border-danger border-top border-4">
                <div class="card-body py-3">
                    <span class="text-body-secondary small fw-semibold">Total Absent</span>
                    <h3 class="mb-0 fw-bold text-danger"><?= $total_absent ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Container for Attendance Records Header & Add Button -->
    <div class="card glass-card p-3 mb-3 shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="mb-0 fw-bold text-body" id="recordsTitle">Attendance Records</h3>
            <a href="add.php" class="btn btn-success soft-btn fw-semibold shadow-sm">+ Log New Attendance</a>
        </div>
    </div>

    <!-- Advanced Filter Bar -->
    <div class="card glass-card p-3 mb-4 shadow-sm">
        <form method="GET" action="index.php" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control rounded-pill px-3" placeholder="Search ID, Name..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="filter_date" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($filter_date) ?>">
            </div>
            <div class="col-md-2">
                <select name="filter_status" class="form-select rounded-pill px-3">
                    <option value="">All Statuses</option>
                    <option value="Present" <?= $filter_status == 'Present' ? 'selected' : '' ?>>Present</option>
                    <option value="Late" <?= $filter_status == 'Late' ? 'selected' : '' ?>>Late</option>
                    <option value="Absent" <?= $filter_status == 'Absent' ? 'selected' : '' ?>>Absent</option>
                    <option value="Excused" <?= $filter_status == 'Excused' ? 'selected' : '' ?>>Excused</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="filter_section" class="form-select rounded-pill px-3">
                    <option value="">All Sections</option>
                    <?php while($sec = $sections_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($sec['course_section']) ?>" <?= $filter_section == $sec['course_section'] ? 'selected' : '' ?>><?= htmlspecialchars($sec['course_section']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid gap-2 d-flex">
                <button type="submit" class="btn btn-primary soft-btn rounded-pill flex-fill">Filter</button>
                <a href="index.php" class="btn btn-outline-secondary soft-btn rounded-pill">Reset</a>
            </div>
        </form>
    </div>

    <!-- Responsive Data Table -->
    <div class="card glass-card shadow-sm mb-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover table-glass align-middle mb-0">
                <thead>
                    <tr>
                        <th class="py-3">#</th>
                        <th class="py-3">Student ID</th>
                        <th class="py-3">Full Name</th>
                        <th class="py-3">Course & Section</th>
                        <th class="py-3">Date</th>
                        <th class="py-3">Time In</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Remarks</th>
                        <th class="text-center py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php 
                    $counter = 1;
                    while($row = $result->fetch_assoc()): 
                    ?>
                        <tr>
                            <td class="fw-bold"><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($row['student_id']) ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['course_section']) ?></td>
                            <td><?= $row['attendance_date'] ?></td>
                            <td><?= date("h:i A", strtotime($row['time_in'])) ?></td>
                            <td>
                                <?php
                                $badge = match($row['status']) {
                                    'Present' => 'bg-success',
                                    'Late' => 'bg-warning text-dark',
                                    'Absent' => 'bg-danger',
                                    'Excused' => 'bg-info text-dark',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $badge ?> px-2 py-1"><?= $row['status'] ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['remarks']) ?></td>
                            <td class="text-center">
                                <a href="edit.php?id=<?= $row['attendance_id'] ?>" class="btn btn-sm btn-warning soft-btn fw-semibold px-3">Edit</a>
                                <a href="delete.php?id=<?= $row['attendance_id'] ?>" class="btn btn-sm btn-danger soft-btn fw-semibold px-2" onclick="return confirm('Delete this attendance record?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center text-body-secondary py-4">No attendance records found matching filters.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Activity Log / Audit Trail Section -->
    <div class="card glass-card shadow-sm">
        <div class="card-header bg-secondary text-white py-2 rounded-top">
            <h6 class="mb-0 fw-semibold">Activity Log / Recent System Activity</h6>
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush small bg-transparent">
                <?php while($log = $logs_result->fetch_assoc()): ?>
                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-2 px-3">
                        <span>Attendance recorded for <strong><?= htmlspecialchars($log['full_name']) ?></strong> (<?= htmlspecialchars($log['student_id']) ?>) - Status: <span class="badge bg-secondary"><?= $log['status'] ?></span></span>
                        <span class="text-body-secondary"><?= $log['attendance_date'] ?> at <?= date("h:i A", strtotime($log['time_in'])) ?></span>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

</div>

<footer class="text-center text-body-secondary py-4 mt-5 border-top glass-card rounded-0">
    <small>ISUFST Attendance Monitoring System &copy; 2026 | Developed by <strong>Gemma S. Suelon</strong></small>
</footer>

<script>
    function updateClock() {
        const now = new Date();
        document.getElementById('liveClock').innerText = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();
    }
    setInterval(updateClock, 1000);
    updateClock();

    function applyThemeUI(theme) {
        const html = document.getElementById('htmlTheme');
        const btn = document.getElementById('themeToggle');
        const title = document.getElementById('recordsTitle');
        html.setAttribute('data-bs-theme', theme);
        if (theme === 'dark') {
            btn.innerText = '☀️ Light';
            btn.classList.replace('btn-light', 'btn-outline-light');
            if(title) title.style.color = '#ffffff';
        } else {
            btn.innerText = '🌙 Dark';
            btn.classList.replace('btn-outline-light', 'btn-light');
            if(title) title.style.color = '#212529';
        }
    }

    // Initialize button state on load
    applyThemeUI(localStorage.getItem('isufst_theme') || 'light');

    function toggleTheme() {
        const current = document.getElementById('htmlTheme').getAttribute('data-bs-theme');
        const next = current === 'light' ? 'dark' : 'light';
        localStorage.setItem('isufst_theme', next);
        applyThemeUI(next);
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>