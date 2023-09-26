<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="9.css">
    <title>シフト確定</title>
</head>

<body>
    <form action="9.php" method="post">
        <h2 style="text-align:left">シフト作成・登録</h2>

        <?php
        // データベース接続設定
        require_once("db.php");

        // シフトデータ（仮のデータ）
        $shift_data = [];

        // バイトID、名前、電話番号、時給のデータをデータベースから取得
        $stmt = $db->query("SELECT * FROM arubaito_table");
        $staff_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // シフト表の開始日と終了日を設定します
        if (isset($_GET['date'])) {
            $start_date = new DateTime($_GET['date']);
        } else {
            // 2週間後の日曜日の日付を取得します
            $two_weeks_later = strtotime('+2 weeks next Sunday');
            $two_weeks_later_date = date('Y-m-d', $two_weeks_later);
            $start_date = new DateTime($two_weeks_later_date);
        }

        // 開始日が日曜日でない場合、次の日曜日まで移動します
        if ($start_date->format('w') !== '0') {
            $start_date->modify('next Sunday');
        }

        $end_date = clone $start_date;
        $end_date->add(new DateInterval('P13D'));

        // 前の2週間の開始日と終了日を計算します
        $prev_start_date = clone $start_date;
        $prev_start_date->sub(new DateInterval('P14D'));
        $prev_end_date = clone $prev_start_date;
        $prev_end_date->add(new DateInterval('P13D'));

        // 次の2週間の開始日と終了日を計算します
        $next_start_date = clone $start_date;
        $next_start_date->add(new DateInterval('P14D'));
        $next_end_date = clone $next_start_date;
        $next_end_date->add(new DateInterval('P13D'));

        // 曜日の配列
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

        // 前の2週間へのリンクを表示します
        echo "<a href='?date={$prev_start_date->format('Y-m-d')}'>前の2週間</a> | ";

        // 次の2週間へのリンクを表示します
        echo "<a href='?date={$next_start_date->format('Y-m-d')}'>次の2週間</a>";

        // シフト表のヘッダーを作成します
        echo "<table>";
        echo "<tr><th>ID</th><th>名前</th>";

        $current_date = clone $start_date;

        // シフト表の各日についてループします
        while ($current_date <= $end_date) {
            $date = $current_date->format('Y-m-d');
            $weekday = $weekdays[$current_date->format('w')];
            $cell_style = '';

            if ($weekday === '土') {
                $cell_style = 'background-color: blue; color: white;';
            } elseif ($weekday === '日') {
                $cell_style = 'background-color: red; color: white;';
            }

            echo "<th style='border: 1px solid black; $cell_style'>$date ($weekday)</th>";
            $current_date->add(new DateInterval('P1D'));
        }

        echo "</tr>";

        // スタッフごとにループしてシフト情報を表示します
        foreach ($staff_data as $staff) {
            $id = $staff['バイトID'];
            $name = $staff['名前'];

            echo "<tr>";
            echo "<td style='border: 1px solid black;'>$id</td>";
            echo "<td style='border: 1px solid black;'>$name</td>";

            $current_date = clone $start_date;

            while ($current_date <= $end_date) {
                $date = $current_date->format('Y-m-d');

              // シフト情報をデータベースから取得します
$stmt = $db->prepare("SELECT TIME_FORMAT(開始, '%H:%i') AS 開始, TIME_FORMAT(終了, '%H:%i') AS 終了 FROM sihuto_table WHERE バイトID = ? AND 日付 = ?");
$stmt->execute([$id, $date]);
$shift_data = $stmt->fetch(PDO::FETCH_ASSOC);

// 開始時間と終了時間を取得します
$start_time = isset($shift_data['開始']) ? $shift_data['開始'] : '---';
$end_time = isset($shift_data['終了']) ? $shift_data['終了'] : '---';

// シフト情報の開始時間のセレクトボックスの生成
$start_time_name = "shift[$id][$date][start_time]";
$start_time_id = "start_time_$id" . "_" . $current_date->format('Ymd');
$start_time_html = "<select name='$start_time_name' id='$start_time_id'>";
$start_time_html .= "<option value='---'>--</option>";

// 開始時間の選択肢を生成します（9時から22時まで）
for ($hour = 9; $hour <= 22; $hour++) {
    $time = str_pad($hour, 2, '0', STR_PAD_LEFT) . ":00";
    $selected = $start_time === $time ? 'selected' : '';
    $start_time_html .= "<option value='$time' $selected>$time</option>";
}

$start_time_html .= "</select>";

// シフト情報の終了時間のセレクトボックスの生成
$end_time_name = "shift[$id][$date][end_time]";
$end_time_id = "end_time_$id" . "_" . $current_date->format('Ymd');
$end_time_html = "<select name='$end_time_name' id='$end_time_id'>";
$end_time_html .= "<option value='---'>--</option>";

// 終了時間の選択肢を生成します（開始時間以降の時間）
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

        // データベースにシフト情報を保存する処理
        if (isset($_POST['shift'])) {
            $shifts = $_POST['shift'];

            foreach ($shifts as $staff_id => $shift_data) {
                foreach ($shift_data as $date => $times) {
                    $start_time = $times['start_time'];
                    $end_time = $times['end_time'];

                    if ($start_time !== '---' && $end_time !== '---') {
                        // 同じ日付のレコードを削除する
                        $stmt = $db->prepare("DELETE FROM revised_sihuto_table WHERE `バイトID` = ? AND `日付` = ?");
                        $stmt->execute([$staff_id, $date]);

                        // データベースに保存する
                        $stmt = $db->prepare("INSERT INTO revised_sihuto_table (`バイトID`, `日付`, `開始`, `終了`) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$staff_id, $date, $start_time, $end_time]);
                    }
                }
            }

            echo "シフト情報が保存されました。";
        }

        ?>
        <br>
    
        <div style="text-align: center;">
    <button onclick="redirectToDestination()" class="btn_01">登録</button>
    <input type="button" value="確認" class="btn_01" onclick="location.href ='12.php'">
</div>
        <script>
            function redirectToDestination() {
                window.location.href = "9.php";
            }
            
        </script>
    </form>
</body>

</html>
