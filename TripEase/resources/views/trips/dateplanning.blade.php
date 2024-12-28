<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><!-- レスポンシブ -->
    <script src="https://kit.fontawesome.com/ef96165231.js" crossorigin="anonymous"></script><!-- FontAwesome -->
    <title>Tripease</title>
    @vite('resources/css/app.css')
</head>
<body>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                dateClick: function (info) {
                    document.querySelector('input[name="proposed_date"]').value = info.dateStr;
                }
            });
            calendar.render();
        });
    </script>
    
    <header>
    </header>
    <main>
        <h1>{{ $trip->title }}の日程調整</h1>

        <!-- カレンダー -->
        <div id="calendar"></div>

        <!-- 候補日リスト -->
        <h2>候補日</h2>
        <ul>
            @foreach ($candidateDates as $date)
                <li>
                    {{ $date->proposed_date }}
                    <form method="POST" action="{{ route('schedule.voteDate', $trip->id) }}">
                        @csrf
                        <input type="hidden" name="candidate_date_id" value="{{ $date->id }}">
                        <button name="judgement" value="〇">〇</button>
                        <button name="judgement" value="△">△</button>
                        <button name="judgement" value="×">×</button>
                    </form>
                </li>
            @endforeach
        </ul>

        <!-- 候補日追加 -->
        <h2>候補日を追加する</h2>
        <form method="POST" action="{{ route('schedule.addDate', $trip->id) }}">
            @csrf
            <input type="date" name="proposed_date" required>
            <button type="submit">追加</button>
        </form>

        <!-- 確定ボタン -->
        <form method="POST" action="{{ route('schedule.finalize', $trip->id) }}">
            @csrf
            <button type="submit">確定</button>
        </form>
    </main>
    <footer>
    </footer>
</body>
</html>