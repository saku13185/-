<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="9.css">
    <title>Shift Confirmation</title>
</head>

<body>
    <form action="9.php" method="post">
        <h2 style="text-align:left">Create and Register Shifts</h2>

        <?php
        // Database connection settings
        require_once("db.php");

        // Shift Data (Placeholder Data)
        $shift_data = [];

        // Fetch staff ID, name, phone number, and hourly rate from the database
        $stmt = $db->query("SELECT * FROM part_time_employee_table");
        $staff_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Set the start and end dates for the shift table
        if (isset($_GET['date'])) {
            $start_date = new DateTime($_GET['date']);
        } else {
            // Get the date for the next Sunday two weeks from now
            $two_weeks_later = strtotime('+2 weeks next Sunday');
            $two_weeks_later_date = date('Y-m-d', $two_weeks_later);
            $start_date = new DateTime($two_weeks_later_date);
        }

        // Move to the next Sunday if the start date is not a Sunday
        if ($start_date->format('w') !== '0') {
            $start_date->modify('next Sunday');
        }

        $end_date = clone $start_date;
        $end_date->add(new DateInterval('P13D'));

        // Calculate the start and end dates for the previous 2 weeks
        $prev_start_date = clone $start_date;
        $prev_start_date->sub(new DateInterval('P14D'));
        $prev_end_date = clone $prev_start_date;
        $prev_end_date->add(new DateInterval('P13D'));

        // Calculate the start and end dates for the next 2 weeks
        $next_start_date = clone $start_date;
        $next_start_date->add(new DateInterval('P14D'));
        $next_end_date = clone $next_start_date;
        $next_end_date->add(new DateInterval('P13D'));

        // Days of the week array
        $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        // Display links for the previous 2 weeks
        echo "<a href='?date={$prev_start_date->format('Y-m-d')}'>Previous 2 Weeks</a> | ";

        // Display links for the next 2 weeks
        echo "<a href='?date={$next_start_date->format('Y-m-d')}'>Next 2 Weeks</a>";

        // Create the header for the shift table
        echo "<table>";
        echo "<tr><th>ID</th><th>Name>";

        $current_date = clone $start_date;

        // Loop through each day in the shift table
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

        echo "</tr>";

        // Loop through staff members and display shift information
        foreach ($staff_data as $staff) {
            $id = $staff['バイトID'];
            $name = $staff['名前'];

            echo "<tr>";
            echo "<td style='border: 1px solid black;'>$id</td>";
            echo "<td style='border: 1px solid black;'>$name</td>";

            $current_date = clone $start_date;

            while ($current_date <= $end_date) {
                $date = $current_date->format('Y-m-d');

                // Get shift information from the database
                $stmt = $db->prepare("SELECT TIME_FORMAT(開始, '%H:%i') AS 開始, TIME_FORMAT(終了, '%H:%i') AS 終了 FROM sihuto_table WHERE バイトID = ? AND 日付 = ?");
                $stmt->execute([$id, $date]);
                $shift_data = $stmt->fetch(PDO::FETCH_ASSOC);

                $start_time = isset($shift_data['開始']) ? $shift_data['開始'] : '---';
                $end_time = isset($shift_data['終了']) ? $shift_data['終了'] : '---';

                $start_time_name = "shift[$id][$date][start_time]";
                $start_time_id = "start_time_$id" . "_" . $current_date->format('Ymd');
                $start_time_html = "<select name='$start_time_name' id='$start_time_id'>";
                $start_time_html .= "<option value='---'>--</option>";

                for ($hour = 9; $hour <= 22; $hour++) {
                    $time = str_pad($hour, 2, '0', STR_PAD_LEFT) . ":00";
                    $selected = $start_time === $time ? 'selected' : '';
                    $start_time_html .= "<option value='$time' $selected>$time</option>";
                }

                $start_time_html .= "</select>";

                $end_time_name = "shift[$id][$date][end_time]";
                $end_time_id = "end_time_$id" . "_" . $current_date->format('Ymd');
                $end_time_html = "<select name='$end_time_name' id='$end_time_id'>";
                $end_time_html .= "<option value='---'>--</option>";

                for ($hour = 9; $hour <= 22; $hour++) {
                    $time = str_pad($hour, 2, '0', STR_PAD_LEFT) . ":00";
                    $selected = $end_time === $time ? 'selected' : '';
                    $end_time_html .= "<option value='$time' $selected>$time</option>";
                }

                $end_time_html .= "</select>";

                echo "<td style='border: 1px solid black;'>$start_time_html - $end_time_html</td>";

                $current_date->add(new DateInterval('P1D'));
            }

            echo "</tr>";
        }

        echo "</table>";

        // Save shift information to the database
        if (isset($_POST['shift'])) {
            $shifts = $_POST['shift'];

            foreach ($shifts as $staff_id => $shift_data) {
                foreach ($shift_data as $date => $times) {
                    $start_time = $times['start_time'];
                    $end_time = $times['end_time'];

                    if ($start_time !== '---' && $end_time !== '---') {
                        // Delete records for the same date
                        $stmt = $db->prepare("DELETE FROM revised_sihuto_table WHERE `バイトID` = ? AND `日付` = ?");
                        $stmt->execute([$staff_id, $date]);

                        // Save to the database
                        $stmt = $db->prepare("INSERT INTO revised_sihuto_table (`バイトID`, `日付`, `開始`, `終了`) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$staff_id, $date, $start_time, $end_time]);
                    }
                }
            }

            echo "Shift information has been saved.";
        }

        ?>
        <br>

        <div style="text-align: center;">
            <button onclick="redirectToDestination()" class="btn_01">Register</button>
            <input type="button" value="Confirm" class="btn_01" onclick="location.href ='12.php'">
        </div>
        <script>
            function redirectToDestination() {
                window.location.href = "9.php";
            }
        </script>
    </form>
</body>

</html>
