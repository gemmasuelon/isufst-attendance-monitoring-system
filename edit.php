<?php
include 'db_connect.php';

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM attendance_records WHERE attendance_id = $id");
$record = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $full_name = $_POST['full_name'];
    $course_section = $_POST['course_section'];
    $attendance_date = $_POST['attendance_date'];
    $subject_start_time = $_POST['subject_start_time'];
    $subject_end_time = $_POST['subject_end_time'];
    $time_in = $_POST['time_in'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    if ($status == 'Auto-Calculate') {
        $time_in_sec = strtotime($time_in);
        $start_sec = strtotime($subject_start_time);
        $end_sec = strtotime($subject_end_time);

        if ($time_in_sec > $end_sec) {
            $status = 'Absent';
        } elseif ($time_in_sec > $start_sec) {
            $status = 'Late';
        } else {
            $status = 'Present';
        }
    }

    $stmt = $conn->prepare("UPDATE attendance_records SET student_id=?, full_name=?, course_section=?, attendance_date=?, subject_start_time=?, subject_end_time=?, time_in=?, status=?, remarks=? WHERE attendance_id=?");
    $stmt->bind_param("sssssssssi", $student_id, $full_name, $course_section, $attendance_date, $subject_start_time, $subject_end_time, $time_in, $status, $remarks, $id);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" id="htmlTheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance - ISUFST Attendance Monitoring System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
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
        .soft-btn {
            border-radius: 12px;
            transition: all 0.2s ease-in-out;
        }
        .soft-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.18);
        }
        /* Enlarged clock by ~30% */
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

<div class="container my-5" style="max-width: 600px;">
    <div class="card glass-card shadow-sm border-0">
        <div class="card-header bg-warning text-dark py-3 rounded-top-4">
            <h5 class="mb-0 fw-bold">Edit Attendance Record</h5>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Student ID</label>
                    <input type="text" name="student_id" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($record['student_id']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" name="full_name" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($record['full_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Course & Section</label>
                    <input type="text" name="course_section" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($record['course_section']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" name="attendance_date" class="form-control rounded-pill px-3" value="<?= $record['attendance_date'] ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Subject Start Time</label>
                        <input type="time" name="subject_start_time" class="form-control rounded-pill px-3" value="<?= isset($record['subject_start_time']) ? $record['subject_start_time'] : '08:00' ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Subject End Time</label>
                        <input type="time" name="subject_end_time" class="form-control rounded-pill px-3" value="<?= isset($record['subject_end_time']) ? $record['subject_end_time'] : '16:00' ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Time In (Actual)</label>
                        <input type="time" name="time_in" class="form-control rounded-pill px-3" value="<?= $record['time_in'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Status Override</label>
                        <select name="status" class="form-select rounded-pill px-3">
                            <option value="Auto-Calculate" selected>Auto-Calculate</option>
                            <option value="Absent" <?= $record['status'] == 'Absent' ? 'selected' : '' ?>>Mark as Absent</option>
                            <option value="Excused" <?= $record['status'] == 'Excused' ? 'selected' : '' ?>>Mark as Excused</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Remarks</label>
                    <input type="text" name="remarks" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($record['remarks']) ?>">
                </div>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary soft-btn rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-warning soft-btn fw-semibold rounded-pill px-4">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
        html.setAttribute('data-bs-theme', theme);
        if (theme === 'dark') {
            btn.innerText = '☀️ Light';
            btn.classList.replace('btn-light', 'btn-outline-light');
        } else {
            btn.innerText = '🌙 Dark';
            btn.classList.replace('btn-outline-light', 'btn-light');
        }
    }
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