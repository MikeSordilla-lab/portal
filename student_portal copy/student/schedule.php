<?php

/**
 * Student Schedule Page
 * College Student Portal
 * 
 * Weekly timetable grid with class schedule
 */

$pageTitle = 'My Schedule';
require_once __DIR__ . '/../includes/student-header.php';
require_once __DIR__ . '/../config/database.php';

// Get student's database ID and current semester
$studentDbId = $_SESSION['student_db_id'];
$currentSemester = $_SESSION['current_semester'] ?? 1;

// Fetch schedule for current semester
try {
  $stmt = $pdo->prepare("
        SELECT s.day_of_week, s.start_time, s.end_time, s.room_number, s.instructor_name,
               c.course_code, c.course_name, c.credits
        FROM schedule s
        JOIN courses c ON s.course_id = c.id
        WHERE s.student_id = :student_id AND s.semester = :semester
        ORDER BY s.day_of_week ASC, s.start_time ASC
    ");
  $stmt->execute([':student_id' => $studentDbId, ':semester' => $currentSemester]);
  $scheduleItems = $stmt->fetchAll();

  // Organize schedule by day
  $scheduleByDay = [];
  $courseColors = [];
  $colorIndex = 1;

  foreach ($scheduleItems as $item) {
    $day = $item['day_of_week'];
    if (!isset($scheduleByDay[$day])) {
      $scheduleByDay[$day] = [];
    }

    // Assign color to each unique course
    if (!isset($courseColors[$item['course_code']])) {
      $courseColors[$item['course_code']] = 'schedule-color-' . (($colorIndex++ % 6) + 1);
    }
    $item['color_class'] = $courseColors[$item['course_code']];

    $scheduleByDay[$day][] = $item;
  }
} catch (PDOException $e) {
  error_log("Schedule error: " . $e->getMessage());
  $scheduleItems = [];
  $scheduleByDay = [];
}

// Time slots (8 AM to 6 PM)
$timeSlots = [];
for ($hour = 8; $hour <= 17; $hour++) {
  $timeSlots[] = sprintf('%02d:00:00', $hour);
}

// Days of week
$daysOfWeek = [
  1 => 'Monday',
  2 => 'Tuesday',
  3 => 'Wednesday',
  4 => 'Thursday',
  5 => 'Friday'
];

/**
 * Check if a class falls within a time slot
 */
function isClassInSlot($classes, $slotTime)
{
  $slotStart = strtotime($slotTime);
  $slotEnd = strtotime($slotTime) + 3600; // 1 hour slot

  foreach ($classes as $class) {
    $classStart = strtotime($class['start_time']);
    $classEnd = strtotime($class['end_time']);

    // Check if class overlaps with this slot
    if ($classStart < $slotEnd && $classEnd > $slotStart) {
      return $class;
    }
  }
  return null;
}

/**
 * Calculate rowspan for a class
 */
function getClassRowspan($class)
{
  $start = strtotime($class['start_time']);
  $end = strtotime($class['end_time']);
  $hours = ($end - $start) / 3600;
  return max(1, ceil($hours));
}
?>

<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1 class="mb-2">
        <i class="bi bi-calendar-week me-2"></i>My Schedule
      </h1>
      <p class="text-muted mb-0">
        Semester <?= h($currentSemester) ?> &bull;
        <?= count($scheduleItems) ?> class session<?= count($scheduleItems) !== 1 ? 's' : '' ?>
      </p>
    </div>
    <button class="btn btn-outline-primary btn-print no-print" onclick="window.print()">
      <i class="bi bi-printer me-2"></i>Print Schedule
    </button>
  </div>
</div>

<?php if (empty($scheduleItems)): ?>
  <!-- Empty State -->
  <div class="card">
    <div class="card-body">
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="bi bi-calendar-x"></i>
        </div>
        <h4>No Classes Scheduled</h4>
        <p class="text-muted">You don't have any classes scheduled for this semester yet. Your schedule will appear here once it's set up by the administration.</p>
      </div>
    </div>
  </div>
<?php else: ?>
  <!-- Course Legend -->
  <div class="card mb-4 no-print">
    <div class="card-header card-header-secondary bg-light text-dark">
      <i class="bi bi-palette me-2"></i>Course Legend
    </div>
    <div class="card-body">
      <div class="d-flex flex-wrap gap-3">
        <?php
        $uniqueCourses = [];
        foreach ($scheduleItems as $item) {
          if (!isset($uniqueCourses[$item['course_code']])) {
            $uniqueCourses[$item['course_code']] = [
              'name' => $item['course_name'],
              'color' => $courseColors[$item['course_code']]
            ];
          }
        }
        foreach ($uniqueCourses as $code => $course):
        ?>
          <div class="d-flex align-items-center">
            <span class="schedule-block <?= $course['color'] ?> me-2" style="width: 20px; height: 20px; min-height: 20px;"></span>
            <span><strong><?= h($code) ?></strong> - <?= h($course['name']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Timetable Grid -->
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="timetable">
          <thead>
            <tr>
              <th class="time-slot">Time</th>
              <?php foreach ($daysOfWeek as $day): ?>
                <th><?= $day ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php
            $renderedCells = []; // Track which cells have been rendered (for rowspan)

            foreach ($timeSlots as $slotIndex => $timeSlot):
              $displayTime = date('g:i A', strtotime($timeSlot));
            ?>
              <tr>
                <td class="time-slot"><?= $displayTime ?></td>
                <?php foreach ($daysOfWeek as $dayNum => $dayName):
                  // Check if this cell is covered by a previous rowspan
                  $cellKey = $dayNum . '-' . $slotIndex;
                  if (isset($renderedCells[$cellKey])) {
                    continue; // Skip this cell, it's covered by rowspan
                  }

                  $dayClasses = $scheduleByDay[$dayNum] ?? [];
                  $class = isClassInSlot($dayClasses, $timeSlot);

                  if ($class):
                    // Check if this is the start of the class
                    $classStartSlot = (int)date('G', strtotime($class['start_time'])) - 8;

                    if ($slotIndex === $classStartSlot):
                      $rowspan = getClassRowspan($class);

                      // Mark cells as rendered for rowspan
                      for ($r = 0; $r < $rowspan && ($slotIndex + $r) < count($timeSlots); $r++) {
                        $renderedCells[$dayNum . '-' . ($slotIndex + $r)] = true;
                      }
                ?>
                      <td rowspan="<?= $rowspan ?>" class="p-1">
                        <div class="schedule-block <?= $class['color_class'] ?>">
                          <div class="course-name"><?= h($class['course_code']) ?></div>
                          <div class="course-details">
                            <?= formatTime($class['start_time']) ?> - <?= formatTime($class['end_time']) ?>
                          </div>
                          <div class="course-details">
                            <i class="bi bi-geo-alt-fill"></i> <?= h($class['room_number'] ?: 'TBA') ?>
                          </div>
                          <div class="course-details">
                            <i class="bi bi-person-fill"></i> <?= h($class['instructor_name'] ?: 'TBA') ?>
                          </div>
                        </div>
                      </td>
                    <?php
                    endif;
                  else:
                    ?>
                    <td></td>
                <?php
                  endif;
                endforeach;
                ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Class List (Alternative View) -->
  <div class="card mt-4">
    <div class="card-header card-header-secondary bg-light text-dark">
      <i class="bi bi-list-ul me-2"></i>Class Details
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-student table-hover mb-0">
          <thead>
            <tr>
              <th>Course</th>
              <th>Day</th>
              <th>Time</th>
              <th>Room</th>
              <th>Instructor</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($scheduleItems as $item): ?>
              <tr>
                <td>
                  <strong><?= h($item['course_code']) ?></strong><br>
                  <small class="text-muted"><?= h($item['course_name']) ?></small>
                </td>
                <td><?= getDayName($item['day_of_week']) ?></td>
                <td><?= formatTime($item['start_time']) ?> - <?= formatTime($item['end_time']) ?></td>
                <td><?= h($item['room_number'] ?: 'TBA') ?></td>
                <td><?= h($item['instructor_name'] ?: 'TBA') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/student-footer.php'; ?>