<?php

/**
 * View Schedule Page
 * Admin Module
 * 
 * List all schedule entries with DataTables and filters
 */

$pageTitle = 'Manage Schedule';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

// Fetch students and courses for filters
try {
  $studentsStmt = $pdo->query("SELECT id, student_id, name FROM students ORDER BY name");
  $students = $studentsStmt->fetchAll();
  $coursesStmt = $pdo->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code");
  $courses = $coursesStmt->fetchAll();
} catch (PDOException $e) {
  $students = [];
  $courses = [];
}

// Fetch all schedule entries
try {
  $stmt = $pdo->query("
        SELECT sc.id, sc.day_of_week, sc.start_time, sc.end_time, sc.room_number, 
               sc.instructor_name, sc.semester,
               s.student_id, s.name as student_name,
               c.course_code, c.course_name
        FROM schedule sc
        JOIN students s ON sc.student_id = s.id
        JOIN courses c ON sc.course_id = c.id
        ORDER BY sc.day_of_week ASC, sc.start_time ASC
    ");
  $schedules = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Error fetching schedule: " . $e->getMessage());
  $schedules = [];
}

$daysOfWeek = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
?>

<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1 class="mb-2"><i class="bi bi-calendar-week me-2"></i>Manage Schedule</h1>
      <p class="text-muted mb-0"><?= count($schedules) ?> schedule entr<?= count($schedules) !== 1 ? 'ies' : 'y' ?></p>
    </div>
    <a href="add-schedule.php" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i>Add Schedule Entry
    </a>
  </div>
</div>

<!-- Filters -->
<div class="card mb-4">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label for="student-filter" class="form-label">Filter by Student</label>
        <select class="form-select" id="student-filter">
          <option value="">All Students</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= h($s['student_id']) ?>"><?= h($s['student_id']) ?> - <?= h($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label for="day-filter" class="form-label">Filter by Day</label>
        <select class="form-select" id="day-filter">
          <option value="">All Days</option>
          <?php foreach ($daysOfWeek as $num => $day): ?>
            <option value="<?= h($day) ?>"><?= $day ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button type="button" class="btn btn-outline-secondary w-100" id="clear-filters">
          <i class="bi bi-x-circle me-1"></i>Clear Filters
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Schedule Table -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table id="schedule-table" class="table table-admin table-hover mb-0" style="width:100%">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Course</th>
            <th>Day</th>
            <th>Time</th>
            <th>Room</th>
            <th>Instructor</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($schedules as $sch): ?>
            <tr>
              <td><?= h($sch['student_id']) ?></td>
              <td><strong><?= h($sch['student_name']) ?></strong></td>
              <td><?= h($sch['course_code']) ?></td>
              <td><?= getDayName($sch['day_of_week']) ?></td>
              <td><?= formatTime($sch['start_time']) ?> - <?= formatTime($sch['end_time']) ?></td>
              <td><?= h($sch['room_number'] ?: '-') ?></td>
              <td><?= h($sch['instructor_name'] ?: '-') ?></td>
              <td>
                <div class="btn-group">
                  <a href="edit-schedule.php?id=<?= $sch['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="delete-schedule.php?id=<?= $sch['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var table = $('#schedule-table').DataTable({
      responsive: true,
      order: [
        [3, 'asc'],
        [4, 'asc']
      ],
      searchDelay: 500,
      columnDefs: [{
        orderable: false,
        targets: [7]
      }],
      language: {
        search: "",
        searchPlaceholder: "Search schedule...",
        emptyTable: "No schedule entries found"
      }
    });

    document.getElementById('student-filter').addEventListener('change', function() {
      table.column(0).search(this.value).draw();
    });

    document.getElementById('day-filter').addEventListener('change', function() {
      table.column(3).search(this.value).draw();
    });

    document.getElementById('clear-filters').addEventListener('click', function() {
      document.getElementById('student-filter').value = '';
      document.getElementById('day-filter').value = '';
      table.search('').columns().search('').draw();
    });
  });
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>