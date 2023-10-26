<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="9-1.css">
    <title>Shift Registration</title>
</head>

<body>
    <form action="9-1.php" method="post">
        <h2 style="text-align:left">Create and Register Shifts</h2>

        <?php
        // Database connection settings
        require_once("db.php");

        // Display the name of the logged-in user
        session_start();
        if (isset($_SESSION["name"])) {
            $name = $_SESSION["name"];
            echo "<p style='text-align:center'>Logged-in User: $name</p>";
        } else {
            // If not logged in, redirect to the login page
            header("Location: 6.php");
            exit;
        }

        // Shift Data (Placeholder Data)
        $shift_data = [];

        // Fetch staff ID, name, phone number, and hourly rate from the database
        $stmt = $db->query("SELECT * FROM part_time_employee_table WHERE Name = '$name'");
        $staff_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Set the start and end dates for the shift table
        $start_date = new DateTime();
        if (isset($_GET['date'])) {
            $start_date = new DateTime($_GET['date']);
        } else {
            // Get the date for the next Sunday one month from now
            $next_sunday = strtotime('next Sunday +1 month');
            $next_sunday_date = date('Y-m-d', $next_sunday);
            $start_date = new DateTime($next_sunday_date);
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
            $id = $staff['ID'];
            $name = $staff['Name'];

            echo "<tr>";
            echo "<td style='border: 1px solid black;'>$id</td>";
            echo "<td style='border: 1px solid black;'>$name</td>";

            $current_date = clone $start_date;

            while ($current_date <= $end_date) {
                $date = $current_date->format('Y-m-d');
            
                // Get shift information from the database
                $stmt = $db->prepare("SELECT TIME_FORMAT(Start, '%H:%i') AS Start, TIME_FORMAT(End, '%H:%i') AS End FROM shift_table WHERE ID = ? AND Date = ?");
                $stmt->execute([$id, $date]);
                $shift_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
                $start_time = isset($shift_data['Start']) ? $shift_data['Start'] : '---';
                $end_time = isset($shift_data['End']) ? $shift_data['End'] : '---';
            
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

        if (isset($_POST['shift'])) {
            $shifts = $_POST['shift'];

            foreach ($shifts as $staff_id => $shift_data) {
                foreach ($shift_data as $date => $times) {
                    $start_time = $times['start_time'];
                    $end_time = $times['end_time'];

                    if ($start_time !== '---' && $end_time !== '---') {
                        // Example of validating and saving shift information to the database
                        $stmt = $db->prepare("INSERT INTO shift_table (`ID`, `Date`, `Start`, `End`) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$staff_id, $date, $start_time, $end_time]);
                    }
                }
            }

            echo "Shift information has been saved.";
        }
        ?>

        <div style="text-align: center;">
            <br>
            <button onclick="redirectToDestination()" class="btn_01">Register</button>
            <input type="button" value="Confirm" class="btn_01" onclick="location.href ='11.php'">
        </div>
    </form>
    <br>

    <script>
        function redirectToDestination() {
            var destinationUrl = "11.php";
            window.location.href = destinationUrl;
        }
    </script>

    <?php
    // Fetch staff ID, name, phone number, and hourly rate from the database
    $stmt = $db->query("SELECT * FROM part_time_employee_table");
    $staff_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set the start and end dates for the shift table
    $start_date = new DateTime();
    if (isset($_GET['date'])) {
        $start_date = a new DateTime($_GET['date']);
    } else {
        // Get the date for the next Sunday one month from now
        $next_sunday = strtotime('next Sunday +1 month');
        $next_sunday_date = date('Y-m-d', $next_sunday);
        $start_date = new DateTime($next_sunday_date);
    }

    // Move to the next Sunday if the start date is not a Sunday
    if ($start_date->format('w') !== '0') {
        $start_date->modify('next Sunday');
    }

    $end_date = clone $start_date;
    $end_date->add(new DateInterval('P13D'));

    // Days of the week array
    $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    // Create the header for the shift table
    echo "<table>";
    echo "<tr><th>ID</th><th>Name>";

    $current_date = clone $start_date;

    for ($i = 0; $i < 14; $i++) {
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

    foreach ($staff_data as $staff) {
        $id = $staff['ID'];
        $name = $staff['Name'];

        echo "<tr>";
        echo "<td style='border: 1px solid black;'>$id</td>";
        echo "<td style='border: 1px solid black;'>$name</td>";

        $current_date = clone $start_date;

        for ($i = 0; $i < 14; $i++) {
            $date = $current_date->format('Y-m-d');

            $stmt = $db->prepare("SELECT TIME_FORMAT(Start, '%H:%i') AS Start, TIME_FORMAT(End, '%H:%i') AS End FROM shift_table WHERE ID = ? AND Date = ?");
            $stmt->execute([$id, $date]);
            $shift_data = $stmt->fetch(PDO::FETCH_ASSOC);

            $start_time = isset($shift_data['Start']) ? $shift_data['Start'] : '';
            $end_time = isset($shift_data['End']) ? $shift_data['End'] : '';

            echo "<td style='border: 1px solid black;'>$start_time - $end_time</td>";

            $current_date->add(new DateInterval('P1D'));
        }

        echo "</tr>";
    }

    echo "</table>";
    ?>
</body>

</html>
