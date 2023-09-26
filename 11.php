<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="11.css">
    <title>アルバイトシフト確認</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <?php
        // データベース接続設定
        require_once("db.php");

        // ログインしたユーザーの名前を表示
        session_start();
        if (isset($_SESSION["name"])) {
            $name = $_SESSION["name"];
            echo "<p style='text-align:center'>ログインユーザー：$name</p>";

            // バイトIDを取得
            $stmt = $db->prepare("SELECT バイトID FROM arubaito_table WHERE 名前 = ?");
            $stmt->execute([$name]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $baitoID = $result['バイトID'];

            // タイムゾーンを設定
            date_default_timezone_set('Asia/Tokyo');

            // 前月・次月リンクが押された場合は、GETパラメーターから年月を取得
            if (isset($_GET['ym'])) {
                $ym = $_GET['ym'];
            } else {
                // 今月の年月を表示
                $ym = date('Y-m');
            }

            // タイムスタンプを作成し、フォーマットをチェックする
            $timestamp = strtotime($ym . '-01');
            if ($timestamp === false) {
                $ym = date('Y-m');
                $timestamp = strtotime($ym . '-01');
            }

            // 今日の日付 フォーマット　例）2021-06-3
            $today = date('Y-m-j');

            // カレンダーのタイトルを作成　例）2021年6月
            $html_title = date('Y年n月', $timestamp);

            // 前月・次月の年月を取得
            $prev = date('Y-m', strtotime('-1 month', $timestamp));
            $next = date('Y-m', strtotime('+1 month', $timestamp));

            // 該当月の日数を取得
            $day_count = date('t', $timestamp);

            // １日が何曜日か　0:日 1:月 2:火 ... 6:土
            $youbi = date('w', $timestamp);

            // カレンダー作成の準備
            $weeks = [];
            $week = '';
            $total_work_hours = 0;
            $total_break_hours = 0;

            // 第１週目：空のセルを追加
            $week .= str_repeat('<td></td>', $youbi);

            for ($day = 1; $day <= $day_count; $day++, $youbi++) {

                $date = $ym . '-' . $day;

                if ($today == $date) {
                    // 今日の日付の場合は、class="today"をつける
                    $week .= '<td class="today">' . $day;
                } else {
                    $week .= '<td>' . $day;
                }

                // シフト情報をデータベースから取得します
                $stmt = $db->prepare("SELECT TIME_FORMAT(開始, '%H:%i') AS 開始, TIME_FORMAT(終了, '%H:%i') AS 終了 FROM revised_sihuto_table WHERE バイトID = ? AND 日付 = ?");
                $stmt->execute([$baitoID, $date]);
                $shift_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($shift_data)) {
                    $week .= '<div class="shift-info">';

                    $daily_work_hours = 0;
                    $daily_break_hours = 0;

                    foreach ($shift_data as $shift) {
                        $start_time = isset($shift['開始']) ? $shift['開始'] : '';
                        $end_time = isset($shift['終了']) ? $shift['終了'] : '';

                        // シフトの時間データを表示
                        $week .= '<div class="shift-time">' . $start_time . ' ～ ' . $end_time . '</div>';

                        // 時間の差を計算して給料を算出
                        $start_datetime = DateTime::createFromFormat('H:i', $start_time);
                        $end_datetime = DateTime::createFromFormat('H:i', $end_time);
                        $interval = $start_datetime->diff($end_datetime);
                        $hours = $interval->h + ($interval->i / 60); // 時間単位で計算

                        $daily_work_hours += $hours;

                        // 休憩時間の計算
                        if ($daily_work_hours >= 8) {
                            $daily_break_hours = 60;
                        } elseif ($daily_work_hours >= 6) {
                            $daily_break_hours = 45;
                        } else {
                            $daily_break_hours = 0;
                        }
                    }

                    $week .= '</div>';

                    // 日ごとの労働時間と休憩時間を合計
                    $total_work_hours += $daily_work_hours;
                    $total_break_hours += $daily_break_hours;
                }

                // 週終わり、または、月終わりの場合
                if ($youbi % 7 == 6 || $day == $day_count) {

                    if ($day == $day_count) {
                        // 月の最終日の場合、空セルを追加
                        $week .= str_repeat('<td></td>', 6 - $youbi % 7);
                    }

                    // weeks配列にtrと$weekを追加する
                    $weeks[] = '<tr>' . $week . '</tr>';

                    // weekをリセット
                    $week = '';
                }
            }

            // 給料計算
            $hourly_wage = 1000; // 時給（例として1000円とします）
            $total_work_hours_without_break = $total_work_hours - ($total_break_hours / 60);
            $total_salary = floor($total_work_hours_without_break * $hourly_wage);
        } else {
            // ログインしていない場合はログイン画面にリダイレクト
            header("Location: 6.php");
            exit;
        }
        ?>

        <h3 class="mb-5">
            <a href="?ym=<?php echo $prev; ?>">&lt;</a>
            <?php echo $html_title; ?>
            <a href="?ym=<?php echo $next; ?>">&gt;</a>
        </h3>

        <div class="summary">
            <p style="display: inline-block; margin-right: 20px;">労働時間：<?php echo $total_work_hours; ?>時間</p>
            <p style="display: inline-block; margin-right: 20px;">休憩時間：<?php echo $total_break_hours; ?>分</p>
            <p style="display: inline-block;">給料合計：<?php echo $total_salary; ?>円</p>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>日</th>
                    <th>月</th>
                    <th>火</th>
                    <th>水</th>
                    <th>木</th>
                    <th>金</th>
                    <th>土</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($weeks as $week) {
                    echo "<tr>$week</tr>";
                }
                ?>
            </tbody>
        </table>

        <br>
        <a href="8.php" class="btn_01">
            <span class="vertical-text">戻る</span>
        </a>
    </div>
</body>

</html>
