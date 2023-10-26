<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="11.css">
    <title>Part-time Shift Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <?php
        // Database connection settings
        require_once("db.php");

        // Display the name of the logged-in user
        session_start();
        if (isset($_SESSION["name"])) {
            $name = $_SESSION["name"];
            echo "<p style='text-align:center'>Logged-in User: $name</p>";

            // Get the part-time employee's ID
            $stmt = $db->prepare("SELECT バイトID FROM part_time_employee_table WHERE 名前 = ?");
            $stmt->execute([$name]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $baitoID = $result['バイトID'];

            // Set the timezone
            date_default_timezone_set('Asia/Tokyo');

            // Get the year and month from the GET parameter when the previous or next month is clicked
            if (isset($_GET['ym'])) {
                $ym = $_GET['ym'];
            } else {
                // Display the current year and month
                $ym = date('Y-m');
            }

            // Create a timestamp and check the format
            $timestamp = strtotime($ym . '-01');
            if ($timestamp === false) {
                $ym = date('Y-m');
                $timestamp = strtotime($ym . '-01');
            }

            // Today's date format (e.g., 2021-06-3)
            $today = date('Y-m-j');

            // Create the calendar title (e.g., 2021年6月)
            $html_title = date('Y年n月', $timestamp);

            // Get the previous and next year and month
            $prev = date('Y-m', strtotime('-1 month', $timestamp));
            $next = date('Y-m', strtotime('+1 month', $timestamp));

            // Get the number of days in the selected month
            $day_count = date('t', $timestamp);

            // Determine the day of the week for the 1st day (0: Sunday, 1: Monday, ..., 6: Saturday)
            $youbi = date('w', $timestamp);

            // Calendar preparation
            $weeks = [];
            $week = '';
            $total_work_hours = 0;
            $total_break_hours = 0;

            // The 1st week: Add empty cells
            $week .= str_repeat('<td></td>', $youbi);

            for ($day = 1; $day <= $day_count; $day++, $youbi++) {
                $date = $ym . '-' . $day;

                if ($today == $date) {
                    // Add class="today" to the cell for today's date
                    $week .= '<td class="today">' . $day;
                } else {
                    $week .= '<td>' . $day;
                }

                // Get shift information from the database
                $stmt = $db->prepare("SELECT TIME_FORMAT(開始, '%H:%i') AS 開始, TIME_FORMAT(終了, '%H:%i') AS 終了 FROM revised_sihuto_table WHERE バイトID = ? AND 日付 = ?");
                $stmt->execute([$baitoID, $date]);
                $shift_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($shift_data)) {
                    $week .= '<div class="shift-info">';
                    $daily_work_hours = 0;
                    $daily_break_hours = 0;

                    foreach ($shift_data as $shift) {
                        $start_time = isset($shift['開始']) ? $shift['開始'] : '';
                        $end_time = isset($shift['終了']) ? $shift['終了'] : '';

                        // Display the shift time data
                        $week .= '<div class="shift-time">' . $start_time . ' ～ ' . $end_time . '</div';

                        // Calculate the time difference to determine the salary
                        $start_datetime = DateTime::createFromFormat('H:i', $start_time);
                        $end_datetime = DateTime::createFromFormat('H:i', $end_time);
                        $interval = $start_datetime->diff($end_datetime);
                        $hours = $interval->h + ($interval->i / 60); // Calculate in hours

                        $daily_work_hours += $hours;

                        // Calculate break time
                        if ($daily_work_hours >= 8) {
                            $daily_break_hours = 60;
                        } elseif ($daily_work_hours >= 6) {
                            $daily_break_hours = 45;
                        } else {
                            $daily_break_hours = 0;
                        }
                    }

                    $week .= '</div>';

                    // Sum the daily working hours and break hours
                    $total_work_hours += $daily_work_hours;
                    $total_break_hours += $daily_break_hours;
                }

                // At the end of the week or the end of the month
                if ($youbi % 7 == 6 || $day == $day_count) {
                    if ($day == $day_count) {
                        // If it's the last day of the month, add empty cells
                        $week .= str_repeat('<td></td>', 6 - $youbi % 7);
                    }

                    // Add the row and week to the weeks array
                    $weeks[] = '<tr>' . $week . '</tr>';

                    // Reset the week
                    $week = '';
                }
            }

            // Calculate salary
            $hourly_wage = 1000; // Hourly wage (e.g., 1000 yen)
            $total_work_hours_without_break = $total_work_hours - ($total_break_hours / 60);
            $total_salary = floor($total_work_hours_without_break * $hourly_wage);
        } else {
            // If not logged in, redirect to the login page
            header("Location: 6.php");
            exit;
        }
        ?>

        <h3 class="mb-5">
            <a href="?ym=<?php echo $prev; ?>">&lt;</a>
            <?php echo $html_title; ?>
            <a href="?ym=<?php echo $next; ?>">&gt;</a>
        </h3>

        <div class="summary">
            <p style="display: inline-block; margin-right: 20px;">Total Working Hours: <?php echo $total_work_hours; ?> hours</p>
            <p style="display: inline-block; margin-right: 20px;">Break Time: <?php echo $total_break_hours; ?> minutes</p>
            <p style="display: inline-block;">Total Salary: <?php echo $total_salary; ?> yen</p>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Sat</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($weeks as $week) {
                    echo "<tr>$week</tr>";
                }
                ?>
            </tbody>
        </table>

        <br>
        <a href="8.php" class="btn_01">
            <span class="vertical-text">Back</span>
        </a>
    </div>
</body>

</html>
