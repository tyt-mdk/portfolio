<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><!-- レスポンシブ -->
    <meta name="csrf-token" content="{{ csrf_token() }}"><!-- csrf-token -->
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
            margin-bottom: 1.5rem !important;
        }
    
        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: normal;
            color: #1f2937;
            text-align: left;
            padding-left: 0.5rem;
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
            height: 45px !important;
            max-height: 45px !important;
            padding: 0 !important;
        }
    
        .fc .fc-daygrid-day-frame {
            height: 45px !important;
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
            flex: none !important;
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
        document.addEventListener('DOMContentLoaded', function() {
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
                console.log('Applying styles for dates:', candidateDates); // デバッグ用

                // 既存のスタイルをクリア
                document.querySelectorAll('.date-bg').forEach(el => {
                    el.remove();
                });

                // 今日の日付のスタイル
                document.querySelectorAll('.fc-day-today').forEach(el => {
                    // 既存のtoday用のクラスを削除
                    el.classList.remove('fc-day-today');
                    // グレーの円形背景を追加
                    el.classList.add('relative');
                    const todayCircle = document.createElement('div');
                    todayCircle.className = 'date-bg absolute inset-0 m-1 bg-gray-100 rounded-full -z-10';
                    el.insertBefore(todayCircle, el.firstChild);
                });

                // 候補日のスタイル適用
                candidateDates.forEach(date => {
                    console.log('Processing date:', date); // デバッグ用
                    const cells = document.querySelectorAll('.fc-daygrid-day');
                    cells.forEach(cell => {
                        const cellDate = cell.getAttribute('data-date');
                        if (cellDate === date.date) {
                            cell.classList.add('relative');
                            
                            // 既存の背景要素を削除
                            const existingBg = cell.querySelector('.date-bg');
                            if (existingBg) {
                                existingBg.remove();
                            }
                            
                            // 基本の候補日スタイル（判定なし）
                            if (!date.judgement) {
                                const circle = document.createElement('div');
                                circle.className = 'date-bg absolute inset-0 m-1 bg-sky-300/30 rounded-full -z-10';
                                cell.insertBefore(circle, cell.firstChild);
                            }
                            
                            // 判定に応じたスタイル
                            if (date.judgement) {
                                console.log('Applying judgement style:', date.judgement); // デバッグ用
                                const shape = document.createElement('div');
                                shape.className = 'date-bg absolute inset-0 m-1 -z-10';
                                
                                switch (date.judgement) {
                                    case '〇':
                                        shape.innerHTML = `
                                            <div class="absolute inset-0 bg-emerald-200/50 rounded-full"></div>
                                        `;
                                        break;
                                    case '△':
                                        shape.innerHTML = `
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <div class="w-4/5 h-4/5 bg-amber-200/50"
                                                    style="clip-path: polygon(50% 0%, 100% 100%, 0% 100%);">
                                                </div>
                                            </div>
                                        `;
                                        break;
                                    case '×':
                                        shape.innerHTML = `
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <div class="w-4/5 h-4/5 relative">
                                                    <div class="absolute inset-0 bg-rose-200/50"
                                                        style="clip-path: polygon(
                                                            20% 0%, 50% 30%, 80% 0%, 100% 20%, 70% 50%, 
                                                            100% 80%, 80% 100%, 50% 70%, 20% 100%, 
                                                            0% 80%, 30% 50%, 0% 20%
                                                        );">
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                        break;
                                }
                                
                                cell.insertBefore(shape, cell.firstChild);
                            }
                        }
                    });
                });
            }

            function createJudgementBox(candidateDate) {
                const box = document.createElement('div');
                box.classList.add('judgement-box', 
                    'fixed', 'top-1/2', 'left-1/2', 'transform', '-translate-x-1/2', '-translate-y-1/2',
                    'bg-white', 'rounded-lg', 'shadow-lg', 'p-6', 'z-50'
                );

                box.innerHTML = `
                    <div class="flex gap-4 justify-center">
                        <button type="button" data-judgement="〇" 
                            class="px-6 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors">〇</button>
                        <button type="button" data-judgement="△" 
                            class="px-6 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-colors">△</button>
                        <button type="button" data-judgement="×" 
                            class="px-6 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors">×</button>
                    </div>
                `;

                box.querySelectorAll('button').forEach(button => {
                    button.addEventListener('click', async () => {
                        try {
                            const postData = {
                                date_id: candidateDate.id,
                                judgement: button.dataset.judgement,
                                trip_id: {{ $trip->id }}
                            };

                            console.log('Sending data:', postData);

                            const response = await fetch(`/trips/{{ $trip->id }}/vote-date`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(postData)
                            });

                            const data = await response.json();
                            console.log('Response data:', data);

                            if (!response.ok) {
                                throw new Error(data.message || 'エラーが発生しました');
                            }

                            // candidateDatesの該当データを更新
                            const targetDate = candidateDates.find(d => d.id === candidateDate.id);
                            if (targetDate) {
                                targetDate.judgement = button.dataset.judgement;
                            }

                            // スタイルを再適用
                            applyDateStyles();
                            box.remove();

                        } catch (error) {
                            console.error('Error details:', error);
                            alert('判定の保存に失敗しました。');
                        }
                    });
                });

                return box;
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
                    const candidateDate = candidateDates.find(date => date.date === info.dateStr);
                    if (!candidateDate) return;

                    const existingBox = document.querySelector('.judgement-box');
                    if (existingBox) {
                        existingBox.remove();
                    }

                    const box = createJudgementBox(candidateDate);
                    document.body.appendChild(box);

                    const closeOnClickOutside = function(e) {
                        if (!box.contains(e.target)) {
                            box.remove();
                            document.removeEventListener('click', closeOnClickOutside);
                        }
                    };

                    setTimeout(() => {
                        document.addEventListener('click', closeOnClickOutside);
                    }, 100);
                },
                datesSet: function() {
                    setTimeout(applyDateStyles, 0);
                }
            });

            calendar.render();
            setTimeout(applyDateStyles, 0);
        });
    </script>

</body>
</html>