<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="1.css">
    <style>
        /* ヘッダーのスタイル */
        .fixed-header {
            position: sticky;  /* 固定位置 */
            top: 0;  /* ヘッダーをページ上端に固定 */
            background-color: white;
            z-index: 1;  /* 他の要素より手前に表示 */
        }

        /* テーブルのスタイル */
        .shift-table {
            overflow: auto;  /* スクロール可能にする */
            height: 400px;  /* 表示する高さを指定 */
        }

        /* IDと名前のスタイル */
        .fixed-column {
            position: sticky;  /* 固定位置 */
            left: 0;  /* IDと名前をページ左端に固定 */
            background-color: white;
            z-index: 1;  /* 他の要素より手前に表示 */
        }
    </style>
    <title>シフト作成者シフト確認</title>
</head>

<body>
    <h1>シフト作成者シフト確認</h1>

    <form method="GET" action="12.php">
        <label for="date">日付選択：</label>
        <input type="month" name="date" id="date" value="<?php echo isset($_GET['date']) ? $_GET['date'] : ''; ?>">
        <input type="submit" value="表示">
    </form>

    <?php
    // データベース接続設定
    require_once("db.php");

    // バイトID、名前、電話番号、時給のデータをデータベースから取得
    $stmt = $db->query("SELECT * FROM arubaito_table");
    $staff_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // シフト表の開始日と終了日を設定します
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

    // 曜日の配列
    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

    // シフト表のヘッダーを作成します
    echo "<div class='shift-table'>";
    echo "<table>";
    echo "<thead class='fixed-header'>";
    echo "<tr><th class='fixed-column'>ID</th><th class='fixed-column'>名前</th>";

    $current_date = clone $start_date;

    // 指定された日付から月の初めから月末までの日付を表示します
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

    echo "<th style='border: 1px solid black;'>労働時間</th>";
    echo "<th style='border: 1px solid black;'>休憩時間</th>";
    echo "<th style='border: 1px solid black; background-color: orange;'>給料</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    // スタッフごとにループしてシフト情報を表示します
    foreach ($staff_data as $staff) {
        $id = $staff['バイトID'];
        $name = $staff['名前'];

        echo "<tr>";
        echo "<td class='fixed-column' style='border: 1px solid black;'>$id</td>";
        echo "<td class='fixed-column' style='border: 1px solid black;'>$name</td>";

        $current_date = clone $start_date;

        // 指定された日付から月の初めから月末までのシフト情報を表示します
        while ($current_date <= $end_date) {
            $date = $current_date->format('Y-m-d');

            // シフト情報をデータベースから取得します
            $stmt = $db->prepare("SELECT TIME_FORMAT(開始, '%H:%i') AS 開始, TIME_FORMAT(終了, '%H:%i') AS 終了 FROM revised_sihuto_table WHERE バイトID = ? AND 日付 = ?");
            $stmt->execute([$id, $date]);
            $shift_data = $stmt->fetch(PDO::FETCH_ASSOC);

            // 開始時間と終了時間を取得します
            $start_time = isset($shift_data['開始']) ? $shift_data['開始'] : '';
            $end_time = isset($shift_data['終了']) ? $shift_data['終了'] : '';

            echo "<td style='border: 1px solid black;'>$start_time - $end_time</td>";

            $current_date->add(new DateInterval('P1D'));
        }

        // 労働時間と休憩時間と給料を計算します
        $total_work_hours = 0;
        $total_break_hours = 0;
        $total_pay = 0;

        $current_date = clone $start_date;

        // 指定された日付から月の初めから月末までのシフト情報を計算します
        while ($current_date <= $end_date) {
            $date = $current_date->format('Y-m-d');

            // シフト情報をデータベースから取得します
            $stmt = $db->prepare("SELECT TIME_FORMAT(開始, '%H:%i') AS 開始, TIME_FORMAT(終了, '%H:%i') AS 終了 FROM revised_sihuto_table WHERE バイトID = ? AND 日付 = ?");
            $stmt->execute([$id, $date]);
            $shift_data = $stmt->fetch(PDO::FETCH_ASSOC);

            // 開始時間と終了時間を取得します
            $start_time = isset($shift_data['開始']) ? $shift_data['開始'] : '';
            $end_time = isset($shift_data['終了']) ? $shift_data['終了'] : '';

            if ($start_time && $end_time) {
                $start_datetime = new DateTime($date . ' ' . $start_time);
                $end_datetime = new DateTime($date . ' ' . $end_time);
                $interval = $start_datetime->diff($end_datetime);

                // 労働時間を計算します
                $work_hours = $interval->format('%H:%I');
                $total_work_hours += strtotime($work_hours) - strtotime('00:00');

                // 休憩時間を計算します
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

        // 給料を計算します
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
        <span class="vertical-text">戻る</span>
    </a>

</body>

</html>
