<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><!-- レスポンシブ -->
    <script src="https://kit.fontawesome.com/ef96165231.js" crossorigin="anonymous"></script><!-- FontAwesome -->

    <!-- FullCalendarのCSSとJS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales-all.min.js"></script>

    <style>
        /* リセットスタイル */
        .fc * {
            border: none !important;
        }
    
        .fc table {
            border-collapse: separate !important;
            border-spacing: 2px !important;
        }
    
        /* 年月表示のスタイル */
        .fc .fc-toolbar {
            margin-bottom: 1.5rem !important;  /* 余白を増やす */
        }
    
        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: normal;
            color: #1f2937;
            text-align: left;
            padding-left: 0.5rem;  /* 左側の余白を追加 */
        }
    
        /* ナビゲーションボタン */
        .fc .fc-button-primary {
            background: transparent;
            border: none;
            color: #4b5563;
            padding: 0 12px;
            font-size: 1.2rem;
        }
    
        .fc .fc-button-primary:hover {
            color: #1f2937;
            background: transparent !important;
        }
    
        /* カレンダーセルのスタイル */
        .fc .fc-daygrid-day {
            height: 45px !important;  /* 高さを固定 */
            max-height: 45px !important;
            padding: 0 !important;
        }
    
        .fc .fc-daygrid-day-frame {
            height: 45px !important;  /* 高さを固定 */
            max-height: 45px !important;
            display: flex !important;
            justify-content: center !important;
            align-items: flex-start !important;
        }
    
        /* 日付表示の基本スタイル */
        .fc .fc-daygrid-day-top {
            display: flex !important;
            justify-content: center !important;
            padding: 4px 0 0 0 !important;
            flex: none !important;  /* flexboxの伸縮を防ぐ */
        }
    
        /* カスタム日付セル */
        .custom-date-cell {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            font-size: 0.875rem;
            position: relative;
            background: transparent;
        }
    
        /* 今日の日付 */
        .fc .fc-day-today {
            background: transparent !important;
        }
    
        .fc .fc-day-today .custom-date-cell {
            background-color: #3b82f6;
            color: white;
        }
    
        /* 候補日のスタイル */
        .candidate-date .custom-date-cell {
            border: 2px solid #fbbf24 !important;
        }

        /* 判定済み候補日のスタイル */
        .judgement-〇 .custom-date-cell {
            border: 2px solid #22c55e !important;
        }

        .judgement-△ .custom-date-cell {
            border: 2px solid #f59e0b !important;
        }

        .judgement-× .custom-date-cell {
            border: 2px solid #ef4444 !important;
        }

        /* カスタム日付セルの基本スタイル */
        .custom-date-cell {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            font-size: 0.875rem;
            margin: 0 auto;
            position: relative;
            background: transparent;
            border: 2px solid transparent;
        }

        /* 日付セルのホバー効果 */
        .candidate-date .custom-date-cell:hover {
            background-color: rgba(251, 191, 36, 0.1);
        }

        .judgement-〇 .custom-date-cell:hover {
            background-color: rgba(34, 197, 94, 0.1);
        }

        .judgement-△ .custom-date-cell:hover {
            background-color: rgba(245, 158, 11, 0.1);
        }

        .judgement-× .custom-date-cell:hover {
            background-color: rgba(239, 68, 68, 0.1);
        }
    
        /* 不要な要素を完全に非表示 */
        .fc-daygrid-day-events,
        .fc-daygrid-day-bg,
        .fc-daygrid-day-bottom {
            display: none !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    
        /* 曜日ヘッダー */
        .fc .fc-col-header-cell-cushion {
            font-size: 0.875rem;
            color: #4b5563;
            font-weight: normal;
            padding: 8px 0;
        }
    </style>

    <title>Tripease</title>
    @vite('resources/css/app.css')
</head>
<body>
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

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const candidateDates = [
                @foreach ($candidateDates as $date)
                    {
                        date: '{{ $date->proposed_date }}',
                        id: {{ $date->id }},
                        judgement: '{{ $date->judgement ?? "" }}'
                    },
                @endforeach
            ];
    
            function applyDateStyles() {
                // 一旦すべてのcandidate-dateクラスを削除
                document.querySelectorAll('.candidate-date').forEach(el => {
                    el.classList.remove('candidate-date');
                });
                document.querySelectorAll('[class*="judgement-"]').forEach(el => {
                    el.className = el.className.replace(/judgement-[^ ]*/, '');
                });
    
                // 候補日のスタイルを適用
                candidateDates.forEach(date => {
                    const cells = document.querySelectorAll('.fc-daygrid-day');
                    cells.forEach(cell => {
                        const cellDate = cell.getAttribute('data-date');
                        if (cellDate === date.date) {
                            cell.classList.add('candidate-date');
                            if (date.judgement) {
                                cell.classList.add(`judgement-${date.judgement}`);
                            }
                        }
                    });
                });
            }
    
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'ja',
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'prev,next'
                },
                titleFormat: { 
                    year: 'numeric',
                    month: 'long'
                },
                buttonText: {
                    prev: '▲',
                    next: '▼'
                },
                dayCellContent: function(arg) {
                    const dateNum = arg.dayNumberText.replace('日', '');
                    return {
                        html: `<div class="custom-date-cell">${dateNum}</div>`
                    };
                },
                dateClick: function(info) {
                    // クリックされた日付が候補日かどうかを確認
                    const candidateDate = candidateDates.find(date => date.date === info.dateStr);
                    if (!candidateDate) return;
    
                    // 既存の判定選択ボックスがあれば削除
                    const existingBox = document.querySelector('.judgement-box');
                    if (existingBox) {
                        existingBox.remove();
                    }
    
                    // 判定選択ボックスを作成
                    const box = document.createElement('div');
                    box.classList.add('judgement-box', 
                        'fixed', 'top-1/2', 'left-1/2', 'transform', '-translate-x-1/2', '-translate-y-1/2',
                        'bg-white', 'rounded-lg', 'shadow-lg', 'p-6', 'z-50'
                    );
    
                    box.innerHTML = `
                        <form method="POST" action="/trips/{{ $trip->id }}/vote-date" class="space-y-4">
                            @csrf
                            <input type="hidden" name="candidate_date_id" value="${candidateDate.id}">
                            <div class="flex gap-4 justify-center">
                                <button type="submit" name="judgement" value="〇" 
                                    class="px-6 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors">〇</button>
                                <button type="submit" name="judgement" value="△" 
                                    class="px-6 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-colors">△</button>
                                <button type="submit" name="judgement" value="×" 
                                    class="px-6 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors">×</button>
                            </div>
                        </form>
                    `;
    
                    document.body.appendChild(box);
    
                    // クリックで閉じる処理
                    const closeOnClickOutside = function(e) {
                        if (!box.contains(e.target)) {
                            box.remove();
                            document.removeEventListener('click', closeOnClickOutside);
                        }
                    };
    
                    // 即座にクリックイベントが発火しないよう、少し遅延させる
                    setTimeout(() => {
                        document.addEventListener('click', closeOnClickOutside);
                    }, 100);
                },
                datesSet: function() {
                    // 月が変更されるたびにスタイルを再適用
                    setTimeout(applyDateStyles, 0);
                }
            });
    
            calendar.render();
    
            // 初期表示時にスタイルを適用
            setTimeout(applyDateStyles, 0);
        });
    </script>

</body>
</html>