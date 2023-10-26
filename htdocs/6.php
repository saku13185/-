<!DOCTYPE html>
<html>
<head>
    <title>Part-Time Employee Interface</title>
    <link rel="stylesheet" type="text/css" href="6.css">
</head>
<body>
    <form action="" method="post">
        <h2>Part-Time Employee Personal Information Login</h2>
        <div style="text-align:center">
            <p>Select your name using the dropdown menu and enter your phone number.</p>
            <label>Name</label>
            <input type="text" name="name" placeholder="Name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>"><br>

            <label>Phone Number</label>
            <input type="password" name="bangou" placeholder="Phone Number" value="<?php echo isset($_POST['bangou']) ? $_POST['bangou'] : ''; ?>"><br>
            <button type="submit">Login</button>

            <?php
            // Check if the form has been submitted
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Get input data
                $name = $_POST["name"];
                $phone = $_POST["bangou"];

                // Form validation
                if (empty($name) || empty($phone)) {
                    // Display an error message and stop processing if required fields are empty
                    echo '<p style="color: red;">Please enter your name and phone number.</p>';
                    exit;
                }

                // Database connection settings
                require_once("db.php");

                // Prepare a statement
                $stmt = $db->prepare("SELECT * FROM part_time_employee_table WHERE Name=? AND Phone=?");
                $stmt->bindParam(1, $name);
                $stmt->bindParam(2, $phone);

                // Execute the prepared statement
                $stmt->execute();

                // Get the result
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                // Check and process login
                if ($result) {
                    // Start a session and set login information
                    session_start();
                    $_SESSION["name"] = $name;
                    // Successful login
                    echo "Login successful.";
                    // Redirect after successful login
                    header("Location: 8.php");
                    exit;
                } else {
                    // Failed login
                    echo "Incorrect name or phone number.";
                    // Display a link to return to the login page
                    echo '<a href="6.php">Return to the login page</a>';
                    exit;
                }

                // Close the database connection
                $db = null;
            }
            ?>
        </div>
    </form>
</body>
</html>
