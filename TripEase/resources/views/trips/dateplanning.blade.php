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
        /* 今日の日付のスタイル */
        .fc .fc-daygrid-day {
            position: relative !important;
        }

        /* FullCalendarのデフォルトの今日のハイライトを無効化 */
        .fc .fc-day-today {
            background: none !important;
        }

        .today-bg {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            background-color: rgb(219 234 254 / 0.5) !important;  /* bg-blue-100 with opacity */
            margin: 0 !important;  /* マージンを0に */
            z-index: 0 !important;
            pointer-events: none !important;
        }

        /* 日付の数字を前面に */
        .fc .fc-daygrid-day-top {
            position: relative !important;
            z-index: 1 !important;
        }

        /* 曜日ヘッダーのスタイル */
        .fc .fc-col-header-cell {
            background-color: rgb(100 116 139) !important;  /* bg-slate-500 */
        }

        .fc .fc-col-header-cell-cushion {
            color: rgb(248 250 252) !important;  /* text-slate-50 */
            font-weight: normal !important;
            padding: 8px 0 !important;
        }
    </style>

    <title>Tripease</title>
    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-[100vh] text-[0.65rem] bg-slate-100 text-slate-800 font-notosans">
    <header>
    </header>
    <main class="flex-1 pb-20">
        <h1 class="text-2xl text-slate-950 mt-6 ml-6">{{ $trip->title }}の日程調整</h1>

        <!-- カレンダー -->
        <div class="rounded shadow-md bg-slate-50 grid grid-flow-col justify-stretch p-4 my-10 mx-5">
            <div id="calendar"></div>
        </div>

        <div class="max-w-[400px] mx-auto font-sans">
            <!-- 候補日追加 -->
            <section class="mb-5">
                <h2 class="text-lg mb-2.5">候補日を追加</h2>
                <form method="POST" action="{{ route('schedule.addDate', $trip->id) }}" class="flex gap-2.5">
                    @csrf
                    <input 
                        type="date" 
                        name="proposed_date" 
                        required 
                        class="flex-1 px-2 py-2 border border-gray-300 rounded"
                    >
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-sky-300 text-white rounded hover:bg-sky-400 transition-colors"
                    >
                        追加
                    </button>
                </form>
            </section>
        </div>
    </main>
    <footer  class="fixed bottom-0 left-0 right-0 bg-slate-50">
        <div class="flex justify-around text-center h-20 text-sm">
            <!-- 戻るボタン -->
            <a href="javascript:void(0)" onclick="history.back()" class="absolute left-4 top-1/2 -translate-y-1/2">
                <i class="fa-solid fa-chevron-left"></i>
                <p>戻る</p>
            </a>
        </div>
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

                // createJudgementBox関数を追加
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

                // クリック外での閉じる処理を追加
                setTimeout(() => {
                    document.addEventListener('click', function closeBox(e) {
                        if (!box.contains(e.target)) {
                            box.remove();
                            document.removeEventListener('click', closeBox);
                        }
                    });
                }, 0);

                // ボタンクリックのイベントリスナーを追加
                box.querySelectorAll('button').forEach(button => {
                    button.addEventListener('click', async () => {
                        try {
                            const response = await fetch(`/trips/{{ $trip->id }}/vote-date`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    date_id: candidateDate.id,
                                    judgement: button.dataset.judgement,
                                    trip_id: {{ $trip->id }}
                                })
                            });

                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }

                            const data = await response.json();
                            
                            // candidateDatesの該当データを更新
                            const targetDate = candidateDates.find(d => d.id === candidateDate.id);
                            if (targetDate) {
                                targetDate.judgement = button.dataset.judgement;
                            }

                            // スタイルを再適用
                            applyDateStyles();
                            box.remove();

                        } catch (error) {
                            console.error('Error:', error);
                            alert('判定の保存に失敗しました。');
                        }
                    });
                });

                return box;
            }

            function applyDateStyles() {
                // 既存のスタイルをクリア
                document.querySelectorAll('.date-bg').forEach(el => {
                    el.remove();
                });

                // カレンダーの全セルに対して処理
                document.querySelectorAll('.fc-daygrid-day').forEach(el => {
                    const cellDate = el.getAttribute('data-date');
                    
                    // 候補日のスタイリング
                    const candidateDate = candidateDates.find(date => date.date === cellDate);
                    if (candidateDate) {
                        const mark = document.createElement('div');
                        mark.className = 'date-bg absolute inset-0 flex items-center justify-center pointer-events-none';
                        
                        if (!candidateDate.judgement) {
                            // 未判定の候補日は薄い青の背景
                            mark.style.backgroundColor = 'rgb(219 234 254 / 0.5)';  // bg-blue-100/50
                        } else {
                            // 判定に応じたスタイル
                            switch (candidateDate.judgement) {
                                case '〇':
                                    mark.innerHTML = `
                                        <div class="w-8 h-8 border-2 border-emerald-300/70 rounded-full"></div>
                                    `;
                                    break;
                                case '△':
                                    mark.innerHTML = `
                                        <div class="w-8 h-8 flex items-center justify-center">
                                            <div class="w-6 h-6 bg-orange-200/50"
                                                style="clip-path: polygon(50% 0%, 100% 100%, 0% 100%);">
                                            </div>
                                        </div>
                                    `;
                                    break;
                                case '×':
                                    mark.innerHTML = `
                                        <div class="w-8 h-8 flex items-center justify-center">
                                            <div class="relative w-6 h-6">
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <div class="w-full h-[2px] bg-rose-300/50 transform rotate-45"></div>
                                                </div>
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <div class="w-full h-[2px] bg-rose-300/50 transform -rotate-45"></div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    break;
                            }
                        }
                        
                        el.appendChild(mark);
                    }
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
                height: 'auto',
                dayCellContent: function(arg) {
                    return arg.dayNumberText.replace('日', '');
                },
                buttonText: {
                    prev: '▼',
                    next: '▲'
                },
                buttonIcons: false,
                // today関連の設定を無効化
                nowIndicator: false,
                now: null,
                // 今日の日付のハイライトを無効化
                highlightToday: false,
                // 今日の日付の背景を無効化
                dayMaxEvents: true,
                dateClick: function(info) {
                    const candidateDate = candidateDates.find(date => date.date === info.dateStr);
                    if (candidateDate) {
                        const existingBox = document.querySelector('.judgement-box');
                        if (existingBox) {
                            existingBox.remove();
                        }
                        const box = createJudgementBox(candidateDate);
                        document.body.appendChild(box);
                    }
                },
                datesSet: function() {
                    applyDateStyles();
                }
            });

            calendar.render();
            setTimeout(applyDateStyles, 100);  // タイミングを少し遅らせる
            applyDateStyles();
        });
    </script>

</body>
</html>