<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="4.css">
    <title>アルバイト情報　登録・消去</title>

</head>

<body>
    <div style="text-align: center;">
        <h1>アルバイト情報 登録・消去</h1>
<form action="4-1.php" method="post">
    <label for="name" style="font-size: 30px;">名前:</label>
    <input name="name" id="name" style="font-size: 30px;">
    <br>
    
    <label for="phone" style="font-size: 30px;">電話番号:</label>
    <input type="tel" name="phone" id="phone" required style="font-size: 30px;">
    <br>

    <label for="hourly-rate" style="font-size: 30px;">時給:</label>
    <input type="number" name="hourly-rate" id="hourly-rate" required style="font-size: 30px;">
    <br>

    <input type="submit" name="register" value="登録" class="btn_01">
    <input type="submit" name="delete" value="消去" class="btn_02">
</form>
    </div>
</body>

</html>
<?php

// データベース接続設定
require_once ("db.php");


// 登録ボタンが押された場合の処理
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $hourlyRate = $_POST['hourly-rate'];

    // プリペアドステートメントの準備
    $stmt = $db->prepare("INSERT INTO arubaito_table (バイトID, 名前, 電話番号, 時給) VALUES (null, ?, ?, ?)");
    $stmt->bindParam(1, $name);
    $stmt->bindParam(2, $phone);
    $stmt->bindParam(3, $hourlyRate);

    // プリペアドステートメントの実行
    if ($stmt->execute()) {
        echo "アルバイト情報が登録されました。";
    } else {
        echo "アルバイト情報の登録に失敗しました。";
    }
}

// 消去ボタンが押された場合の処理
if (isset($_POST['delete'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];

    // プリペアドステートメントの準備
    $stmt = $db->prepare("DELETE FROM arubaito_table WHERE 名前=? AND 電話番号=?");
    $stmt->bindParam(1, $name);
    $stmt->bindParam(2, $phone);

    // プリペアドステートメントの実行
    if ($stmt->execute()) {
        echo "アルバイト情報が削除されました。";
    } else {
        echo "アルバイト情報の削除に失敗しました。";
    }
}

// データベース接続を閉じる
$db = null;
?>

