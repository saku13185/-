<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="4.css">
    <title>Part-Time Employee Information Registration and Deletion</title>

</head>

<body>
    <div style="text-align: center;">
        <h1>Part-Time Employee Information Registration and Deletion</h1>
        <form action="4-1.php" method="post">
            <label for="name" style="font-size: 30px;">Name:</label>
            <input name="name" id="name" style="font-size: 30px;">
            <br>

            <label for="phone" style="font-size: 30px;">Phone Number:</label>
            <input type="tel" name="phone" id="phone" required style="font-size: 30px;">
            <br>

            <label for="hourly-rate" style="font-size: 30px;">Hourly Rate:</label>
            <input type="number" name="hourly-rate" id="hourly-rate" required style="font-size: 30px;">
            <br>

            <input type="submit" name="register" value="Register" class="btn_01">
            <input type="submit" name="delete" value="Delete" class="btn_02">
        </form>
    </div>
</body>

</html>

<?php

// Database connection settings
require_once("db.php");

// Processing when the registration button is pressed
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $hourlyRate = $_POST['hourly-rate'];

    // Prepare the prepared statement
    $stmt = $db->prepare("INSERT INTO part_time_employee_table (EmployeeID, Name, Phone, HourlyRate) VALUES (null, ?, ?, ?)");
    $stmt->bindParam(1, $name);
    $stmt->bindParam(2, $phone);
    $stmt->bindParam(3, $hourlyRate);

    // Execute the prepared statement
    if ($stmt->execute()) {
        echo "Part-time employee information has been registered.";
    } else {
        echo "Failed to register part-time employee information.";
    }
}

// Processing when the delete button is pressed
if (isset($_POST['delete'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];

    // Prepare the prepared statement
    $stmt = $db->prepare("DELETE FROM part_time_employee_table WHERE Name=? AND Phone=?");
    $stmt->bindParam(1, $name);
    $stmt->bindParam(2, $phone);

    // Execute the prepared statement
    if ($stmt->execute()) {
        echo "Part-time employee information has been deleted.";
    } else {
        echo "Failed to delete part-time employee information.";
    }
}

// Close the database connection
$db = null;
?>
