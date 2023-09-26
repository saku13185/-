<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <link rel='stylesheet' href='4-1.css' />
    <title>アルバイトデータ表示</title>
  </head>
  <body>
    <table border="1">
      <tr>
        <th>名前</th>
        <th>時給</th>
      </tr>

      <?php
      // データベース接続設定
      require_once ("db.php");

      // 登録ボタンが押された場合の処理
      if (isset($_POST['register'])) {
          $name = $_POST['name'];
          $phone = $_POST['phone'];
          $hourlyRate = $_POST['hourly-rate'];

          // プリペアドステートメントの準備
          $sql="INSERT INTO arubaito_table (バイトID, 名前, 電話番号, 時給) VALUES (NULL, ?, ?, ?)";
          $stmt = $db->prepare($sql) ;

         // プリペアドステートメントの実行
         if ($stmt->execute([$name, $phone, $hourlyRate])) {
          // データの登録が成功した場合、リダイレクトする
          header("Location: ".$_SERVER['PHP_SELF']);
          exit();
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
         if ($stmt->execute([$name, $phone])) {
          // データの削除が成功した場合、リダイレクトする
          header("Location: ".$_SERVER['PHP_SELF']);
          exit();
      } else {
          echo "アルバイト情報の削除に失敗しました。";
      }
  }

      // アルバイト情報の取得
      $sql = 'SELECT * FROM arubaito_table';
      $stmt = $db->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($result as $row) {
        $name = htmlspecialchars($row['名前'], ENT_QUOTES, 'UTF-8');
        $hourlyRate = htmlspecialchars($row['時給'], ENT_QUOTES, 'UTF-8');
        echo "<tr><td>{$name}</td><td>{$hourlyRate}</td></tr>";
      }

      // データベース接続を閉じる
      $db = null;
      ?>

    </table>

    <br>
    <a href="1.php" class="btn_01">
      <span class="vertical-text">戻る</span>
    </a>

  </body>
</html>