<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="8.css">
    <title>シフト提出・シフト確認　選択</title>

</head>

<body>
    <h1 style="text-align:center">シフト提出とシフト確認</h1>
    <br>
    <?php
    // データベース接続設定
    require_once("db.php");

    // ログインしたユーザーの名前を表示
    session_start();
    if (isset($_SESSION["name"])) {
        $name = $_SESSION["name"];
        echo "<p style='text-align:center'>ログインユーザー：$name</p>";
    } else {
        // ログインしていない場合はログイン画面にリダイレクト
        header("Location: 6.php");
        exit;
    }
    ?>
    <br>
    <a href="9-1.php" class="btn_01">シフト提出</a>
    <br>
    <a href="11.php" class="btn_01">シフト確認</a>
    <br>
</body><!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="8.css">
    <title>Shift Submission and Shift Confirmation Selection</title>

</head>

<body>
    <h1 style="text-align:center">Shift Submission and Shift Confirmation</h1>
    <br>
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
    ?>
    <br>
    <a href="9-1.php" class="btn_01">Submit Shift</a>
    <br>
    <a href="11.php" class="btn_01">Shift Confirmation</a>
    <br>
</body>

</html>


</html>

