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
    <main class="flex-1 pb-20 md:pb-10">
        <h1 class="text-xl md:text-2xl text-slate-950 mt-4 md:mt-6 mx-4 md:mx-6">{{ $trip->title }}の日程調整</h1>

        <!-- カレンダー -->
        <div class="rounded shadow-md bg-slate-50 p-3 md:p-4 my-6 md:my-10 mx-4 md:mx-5">
            <div id="calendar" class="text-sm md:text-base"></div>
        </div>

        <!-- 候補日追加フォーム -->
        <div class="max-w-[400px] mx-auto px-4 md:px-0 font-sans">
            <section class="mb-5">
                <h2 class="text-base md:text-lg mb-2 md:mb-2.5">候補日を追加</h2>
                <form method="POST" action="{{ route('schedule.addDate', $trip->id) }}" class="flex gap-2 md:gap-2.5">
                    @csrf
                    <input 
                        type="date" 
                        name="proposed_date" 
                        required 
                        class="flex-1 px-2 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base"
                    >
                    <button 
                        type="submit" 
                        class="px-4 py-2.5 md:py-2 bg-sky-300 text-white rounded hover:bg-sky-400 transition-colors text-sm md:text-base"
                    >
                        追加
                    </button>
                </form>
            </section>
        </div>
    </main>

    <!-- フッター -->
    <footer class="fixed md:static bottom-0 left-0 right-0 bg-slate-50 shadow-lg">
        <div class="max-w-4xl mx-auto px-4">
            <div class="grid grid-cols-3 items-start h-16 md:h-20 text-sm pt-1">
                <!-- 戻るボタン -->
                <div class="justify-self-start">
                    <a href="{{ route('trips.each.planning', ['trip' => $trip->id]) }}" 
                       class="flex items-center justify-center w-9 h-9 md:w-10 md:h-10 bg-slate-200 rounded-full hover:bg-slate-300 transition-colors">
                        <i class="fa-solid fa-chevron-left text-slate-600 text-sm md:text-base"></i>
                    </a>
                </div>
                <div></div>
                <div></div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 候補日データの初期化
            const candidateDates = [
                @foreach ($candidateDates as $date)
                    {
                        date: '{{ $date->proposed_date }}',
                        id: {{ $date->id }},
                        judgement: '{{ $dateVotes->where("date_id", $date->id)->first()?->judgement ?? "" }}'
                    },
                @endforeach
            ];

            // 判定ボックスの作成
            function createJudgementBox(candidateDate) {
                const box = document.createElement('div');
                box.classList.add('judgement-box', 
                    'fixed', 'top-1/2', 'left-1/2', 'transform', '-translate-x-1/2', '-translate-y-1/2',
                    'bg-white', 'rounded-lg', 'shadow-lg', 'p-6', 'z-50'
                );

                // 判定ボタンのHTML
                box.innerHTML = `
                    <div class="flex gap-4 justify-center">
                        <button type="button" data-judgement="〇" 
                            class="px-6 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors">〇</button>
                        <button type="button" data-judgement="△" 
                            class="px-6 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-colors">△</button>
                        <button type="button" data-judgement="×" 
                            class="px-6 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors">×</button>
                        <button type="button" data-judgement="" 
                            class="px-6 py-2 bg-slate-300 text-white rounded-md hover:bg-slate-400 transition-colors">クリア</button>
                    </div>
                `;

                setupJudgementBoxEvents(box, candidateDate);
                return box;
            }

            // 判定ボックスのイベント設定
            function setupJudgementBoxEvents(box, candidateDate) {
                // クリック外での閉じる処理
                setTimeout(() => {
                    document.addEventListener('click', function closeBox(e) {
                        if (!box.contains(e.target)) {
                            box.remove();
                            document.removeEventListener('click', closeBox);
                        }
                    });
                }, 0);

                // ボタンクリックの処理
                box.querySelectorAll('button').forEach(button => {
                    button.addEventListener('click', () => handleJudgementClick(button, candidateDate, box));
                });
            }

            // 判定クリック時の処理
            async function handleJudgementClick(button, candidateDate, box) {
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

                    if (!response.ok) throw new Error('Network response was not ok');

                    const data = await response.json();
                    updateCandidateDate(candidateDate, button.dataset.judgement);
                    box.remove();

                } catch (error) {
                    console.error('Error:', error);
                    alert('判定の保存に失敗しました。');
                }
            }

            // 候補日データの更新
            function updateCandidateDate(candidateDate, judgement) {
                const targetDate = candidateDates.find(d => d.id === candidateDate.id);
                if (targetDate) {
                    targetDate.judgement = judgement;
                    applyDateStyles();
                }
            }

            // 日付スタイルの適用
            function applyDateStyles() {
                // 既存のスタイルをクリア
                document.querySelectorAll('.date-bg').forEach(el => el.remove());

                // カレンダーの全セルに対して処理
                document.querySelectorAll('.fc-daygrid-day').forEach(el => {
                    const cellDate = el.getAttribute('data-date');
                    const candidateDate = candidateDates.find(date => date.date === cellDate);
                    
                    if (candidateDate) {
                        const mark = createDateMark(candidateDate.judgement);
                        el.appendChild(mark);
                    }
                });
            }

            // 日付マークの作成
            function createDateMark(judgement) {
                const mark = document.createElement('div');
                mark.className = 'date-bg absolute inset-0 flex items-center justify-center pointer-events-none';

                if (!judgement || judgement === '') {
                    mark.style.backgroundColor = 'rgb(219 234 254 / 0.5)';  // bg-blue-100/50
                    return mark;
                }

                mark.innerHTML = getJudgementMarkHTML(judgement);
                return mark;
            }

            // 判定マークのHTML取得
            function getJudgementMarkHTML(judgement) {
                const markStyles = {
                    '〇': `<div class="w-8 h-8 border-2 border-emerald-300/70 rounded-full"></div>`,
                    '△': `<div class="w-8 h-8 flex items-center justify-center">
                            <div class="w-6 h-6 bg-orange-200/50"
                                style="clip-path: polygon(50% 0%, 100% 100%, 0% 100%);">
                            </div>
                        </div>`,
                    '×': `<div class="w-8 h-8 flex items-center justify-center">
                            <div class="relative w-6 h-6">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-full h-[2px] bg-rose-300/50 transform rotate-45"></div>
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-full h-[2px] bg-rose-300/50 transform -rotate-45"></div>
                                </div>
                            </div>
                        </div>`
                };
                return markStyles[judgement] || '';
            }

            // カレンダーの初期化と設定
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'ja',
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'prev,next'
                },
                height: 'auto',
                viewDidMount: function(view) {
                    view.el.style.fontSize = window.innerWidth < 768 ? '0.875rem' : '1rem';
                },
                dayCellContent: arg => arg.dayNumberText.replace('日', ''),
                buttonText: {
                    prev: '▼',
                    next: '▲'
                },
                buttonIcons: false,
                nowIndicator: false,
                now: null,
                dayMaxEvents: true,
                dateClick: function(info) {
                    const candidateDate = candidateDates.find(date => date.date === info.dateStr);
                    if (candidateDate) {
                        document.querySelector('.judgement-box')?.remove();
                        document.body.appendChild(createJudgementBox(candidateDate));
                    }
                },
                datesSet: applyDateStyles
            });

            // カレンダーの描画
            calendar.render();
            setTimeout(applyDateStyles, 100);
        });
    </script>

</body>
</html>