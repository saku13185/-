<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="1.css">
    <style>
        /* Header Style */
        .fixed-header {
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 1;
        }

        /* Table Style */
        .shift-table {
            overflow: auto;
            height: 400px;
        }

        /* ID and Name Style */
        .fixed-column {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 1;
        }
    </style>
    <title>Shift Creator Shift Confirmation</title>
</head>

<body>
    <h1>Shift Creator Shift Confirmation</h1>

    <form method="GET" action="12.php">
        <label for="date">Date Selection:</label>
        <input type="month" name="date" id="date" value="<?php echo isset($_GET['date']) ? $_GET['date'] : ''; ?>">
        <input type="submit" value="Display">
    </form>

    <?php
    // Database connection settings
    require_once("db.php");

    // Retrieve data for staff ID, name, phone number, and hourly wage from the database
    $stmt = $db->query("SELECT * FROM arubaito_table");
    $staff_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set the start and end dates for the shift table
    $start_date = null;

    if (isset($_GET['date'])) {
        $selected_date = new DateTime($_GET['date']);
        $start_date = clone $selected_date;
        $start_date->modify('first day of this month');
    } else {
        $current_date = new DateTime();
        $start_date = clone $current_date;
        $start_date->modify('first day of this month');
    }

    $end_date = clone $start_date;
    $end_date->modify('last day of this month');

    // Array of weekdays
    $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    // Create the header of the shift table
    echo "<div class='shift-table'>";
    echo "<table>";
    echo "<thead class='fixed-header'>";
    echo "<tr><th class='fixed-column'>ID</th><th class='fixed-column'>Name</th>";

    $current_date = clone $start_date;

    // Display dates from the specified date to the end of the month
    while ($current_date <= $end_date) {
        $date = $current_date->format('Y-m-d');
        $weekday = $weekdays[$current_date->format('w')];
        $cell_style = '';

        if ($weekday === 'Sat') {
            $cell_style = 'background-color: blue; color: white;';
        } elseif ($weekday === 'Sun') {
            $cell_style = 'background-color: red; color: white;';
        }

        echo "<th style='border: 1px solid black; $cell_style'>$date ($weekday)</th>";
        $current_date->add(new DateInterval('P1D'));
    }

    echo "<th style='border: 1px solid black;'>Work Hours</th>";
    echo "<th style='border: 1px solid black;'>Break Time</th>";
    echo "<th style='border: 1px solid black; background-color: orange;'>Salary</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    // Loop through staff members and display shift information
    foreach ($staff_data as $staff) {
        $id = $staff['バイトID'];
        $name = $staff['名前'];

        echo "<tr>";
        echo "<td class='fixed-column' style='border: 1px solid black;'>$id</td>";
        echo "<td class='fixed-column' style='border: 1px solid black;'>$name</td>";

        $current_date = clone $start_date;

        // Calculate and display shift information from the specified date to the end of the month
        while ($current_date <= $end_date) {
            $date = $current_date->format('Y-m-d');

            // Retrieve shift information from the database
            $stmt = $db->prepare("SELECT TIME_FORMAT(開始, '%H:%i') AS 開始, TIME_FORMAT(終了, '%H:%i') AS 終了 FROM revised_sihuto_table WHERE バイトID = ? AND 日付 = ?");
            $stmt->execute([$id, $date]);
            $shift_data = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get start and end times
            $start_time = isset($shift_data['開始']) ? $shift_data['開始'] : '';
            $end_time = isset($shift_data['終了']) ? $shift_data['終了'] : '';

            echo "<td style='border: 1px solid black;'>$start_time - $end_time</td>";

            $current_date->add(new DateInterval('P1D'));
        }

        // Calculate total work hours, break time, and salary
        $total_work_hours = 0;
        $total_break_hours = 0;
        $total_pay = 0;

        $current_date = clone $start_date;

        // Calculate shift information from the specified date to the end of the month
        while ($current_date <= $end_date) {
            $date = $current_date->format('Y-m-d');

            // Retrieve shift information from the database
            $stmt = $db->prepare("SELECT TIME_FORMAT(開始, '%H:%i') AS 開始, TIME_FORMAT(終了, '%H:%i') AS 終了 FROM revised_sihuto_table WHERE バイトID = ? AND 日付 = ?");
            $stmt->execute([$id, $date]);
            $shift_data = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get start and end times
            $start_time = isset($shift_data['開始']) ? $shift_data['開始'] : '';
            $end_time = isset($shift_data['終了']) ? $shift_data['終了'] : '';

            if ($start_time && $end_time) {
                $start_datetime = new DateTime($date . ' ' . $start_time);
                $end_datetime = new DateTime($date . ' ' . $end_time);
                $interval = $start_datetime->diff($end_datetime);

                // Calculate work hours
                $work_hours = $interval->format('%H:%I');
                $total_work_hours += strtotime($work_hours) - strtotime('00:00');

                // Calculate break time
                if ($interval->h >= 8) {
                    $break_hours = '01:00';
                } elseif ($interval->h >= 6) {
                    $break_hours = '00:45';
                } else {
                    $break_hours = '00:00';
                }
                $total_break_hours += strtotime($break_hours) - strtotime('00:00');
            }

            $current_date->add(new DateInterval('P1D'));
        }

        // Calculate salary
        $hourly_wage = $staff['時給'];
        $total_pay = floor(($total_work_hours - $total_break_hours) / 3600 * $hourly_wage);

        echo "<td style='border: 1px solid black;'>";
        echo gmdate('H:i', $total_work_hours);
        echo "</td>";

        echo "<td style='border: 1px solid black;'>";
        echo gmdate('H:i', $total_break_hours);
        echo "</td>";

        echo "<td style='border: 1px solid black; background-color: orange;'>";
        echo number_format($total_pay);
        echo "</td>";

        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    ?>

    <br>
    <a href="1.php" class="btn_01">
        <span class="vertical-text">Back</span>
    </a>

</body>

</html>
