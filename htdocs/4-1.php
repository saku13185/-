<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <link rel='stylesheet' href='4-1.css' />
    <title>Part-Time Employee Data Display</title>
  </head>
  <body>
    <table border="1">
      <tr>
        <th>Name</th>
        <th>Hourly Rate</th>
      </tr>

      <?php
      // Database connection settings
      require_once ("db.php");

      // Processing when the registration button is pressed
      if (isset($_POST['register'])) {
          $name = $_POST['name'];
          $phone = $_POST['phone'];
          $hourlyRate = $_POST['hourly-rate'];

          // Prepare the prepared statement
          $sql="INSERT INTO part_time_employee_table (EmployeeID, Name, Phone, HourlyRate) VALUES (NULL, ?, ?, ?)";
          $stmt = $db->prepare($sql) ;

         // Execute the prepared statement
         if ($stmt->execute([$name, $phone, $hourlyRate])) {
          // If data registration is successful, redirect
          header("Location: ".$_SERVER['PHP_SELF']);
          exit();
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
         if ($stmt->execute([$name, $phone])) {
          // If data deletion is successful, redirect
          header("Location: ".$_SERVER['PHP_SELF']);
          exit();
      } else {
          echo "Failed to delete part-time employee information.";
      }
  }

      // Get part-time employee information
      $sql = 'SELECT * FROM part_time_employee_table';
      $stmt = $db->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($result as $row) {
        $name = htmlspecialchars($row['Name'], ENT_QUOTES, 'UTF-8');
        $hourlyRate = htmlspecialchars($row['HourlyRate'], ENT_QUOTES, 'UTF-8');
        echo "<tr><td>{$name}</td><td>{$hourlyRate}</td></tr>";
      }

      // Close the database connection
      $db = null;
      ?>

    </table>

    <br>
    <a href="1.php" class="btn_01">
      <span class="vertical-text">Back</span>
    </a>

  </body>
</html>
