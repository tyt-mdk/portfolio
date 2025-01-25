<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://kit.fontawesome.com/ef96165231.js" crossorigin="anonymous"></script>

    <style>
        .mode-tab {
            transition: all 0.3s ease;
            color: #64748b;  /* text-slate-500相当 */
        }
        .mode-tab:hover {
            color: #1e293b;  /* text-slate-900相当 */
        }
        .mode-tab.active > div {
            background-color: white;
            padding: 0.5rem 1rem;  /* py-2 px-4相当 */
            border-radius: 9999px;  /* rounded-full相当 */
            color: #1e293b;  /* text-slate-900相当 */
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);  /* shadow-sm相当 */
        }
    </style>

    <title>Tripease</title>
    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-screen bg-slate-100 text-slate-800 font-notosans text-[0.65rem]">  <!-- text-[0.65rem]を追加 -->
    <header>
    </header>
    <main class="flex-1 max-w-4xl mx-auto w-full px-4 py-6 space-y-6 pb-32">
        <!-- タイトルセクション -->
        <section class="bg-white rounded-lg shadow-sm p-4">
            <div class="relative">
                <div>
                    <p class="text-slate-400">タイトル</p>
                    <p class="text-slate-600">{{ $trip->title }}</p>
                </div>
                <a href="#" class="absolute bottom-0 right-0 inline-flex items-center justify-center px-6 py-2.5 bg-sky-500 text-white font-medium rounded-md shadow-sm hover:bg-sky-600 transition-colors edit-mode-only">
                    <i class="fa-solid fa-pen-to-square mr-2"></i>
                    編集
                </a>
            </div>
        </section>

        <!-- 説明文セクション -->
        <section class="bg-white rounded-lg shadow-sm p-4">
            <div class="relative">
                <div>
                    <p class="text-slate-400">目的</p>
                    <p class="text-slate-600">{{ $trip->description }}</p>
                </div>
                <a href="#" class="absolute bottom-0 right-0 inline-flex items-center justify-center px-6 py-2.5 bg-sky-500 text-white font-medium rounded-md shadow-sm hover:bg-sky-600 transition-colors edit-mode-only">
                    <i class="fa-solid fa-pen-to-square mr-2"></i>
                    編集
                </a>
            </div>
        </section>

        <!-- 候補日一覧テーブル -->
        <section class="bg-white rounded-lg shadow-sm overflow-hidden">
            @if(isset($candidateDates) && $candidateDates->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="p-3 text-center font-medium text-slate-600 min-w-[100px]">参加者</th>
                                @foreach($candidateDates->sortBy('proposed_date')->unique('proposed_date') as $date)
                                    <th class="p-3 text-center font-medium text-slate-600 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($date->proposed_date)->format('n/j') }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-b border-slate-200 last:border-0">
                                    <td class="p-3 text-center font-medium min-w-[100px]">
                                        {{ $user->name }}
                                    </td>
                                    @foreach($candidateDates->sortBy('proposed_date')->unique('proposed_date') as $date)
                                        <td class="p-3 text-center">
                                            @php
                                                $vote = $dateVotes->where('user_id', $user->id)
                                                             ->where('date_id', $date->id)
                                                             ->first();
                                            @endphp
                                            <span class="
                                                @if($vote && $vote->judgement === '〇') text-emerald-500
                                                @elseif($vote && $vote->judgement === '△') text-orange-500
                                                @elseif($vote && $vote->judgement === '×') text-rose-500
                                                @else text-slate-400
                                                @endif
                                            ">
                                                {{ $vote ? $vote->judgement : '未' }}
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center text-slate-500">
                    候補日はまだ登録されていません。
                </div>
            @endif
        </section>

        <!-- 日程調整ボタン -->
        <div class="text-center edit-mode-only">
            <a href="{{ route('trips.schedule', $trip->id) }}" 
               class="inline-flex items-center justify-center px-6 py-2.5 bg-sky-500 text-white font-medium rounded-md shadow-sm hover:bg-sky-600 transition-colors">
               <i class="fa-regular fa-calendar-check mr-2"></i>
               日程を調整する
            </a>
        </div>

        <!-- ユーザー要望一覧 -->
        <section class="bg-white rounded-lg shadow-sm p-4 space-y-4">
            <h2 class="font-medium text-slate-700 border-b border-slate-200 pb-2">みんなの要望</h2>
            
            <!-- 要望一覧 -->
            <div class="space-y-4">
                @foreach($userRequests as $request)
                    <div class="border-b border-slate-100 last:border-0 pb-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-2">
                                <!-- ユーザー名 -->
                                <p class="font-medium text-slate-700">{{ $request->user->name }}</p>
                                <!-- 投稿日時 -->
                                <p class="text-slate-400">
                                    {{ \Carbon\Carbon::parse($request->created_at)->format('n/j H:i') }}
                                </p>
                            </div>
                            <!-- いいねボタン -->
                            <button 
                                onclick="toggleLike({{ $request->id }}, this)" 
                                class="like-button flex items-center space-x-1 {{ $request->isLikedBy(Auth::user()) ? 'text-red-500' : 'text-slate-400' }}"
                                data-liked="{{ $request->isLikedBy(Auth::user()) ? 'true' : 'false' }}"
                            >
                                <i class="fa-heart {{ $request->isLikedBy(Auth::user()) ? 'fas' : 'far' }}"></i>
                                <span class="like-count">{{ $request->likes->count() }}</span>
                            </button>
                        </div>

                        <!-- 要望内容 -->
                        <p class="mt-2 text-slate-600">{{ $request->content }}</p>

                        <!-- コメント一覧 -->
                        <div class="mt-2 pl-4 space-y-2">
                            @foreach($request->comments as $comment)
                                <div class="flex items-start space-x-2">
                                    <p class="font-medium text-slate-700">{{ $comment->user->name }}</p>
                                    <p class="text-slate-600">{{ $comment->content }}</p>
                                </div>
                            @endforeach
                        </div>

                        <!-- コメント追加フォーム -->
                        <form method="POST" action="{{ route('requests.comment', $request->id) }}" class="mt-2 pl-4 edit-mode-only">
                            @csrf
                            <div class="flex items-center space-x-2">
                                <input 
                                    type="text" 
                                    name="content" 
                                    placeholder="コメントを追加" 
                                    class="flex-1 px-2 py-1 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500"
                                    required
                                >
                                <button 
                                    type="submit" 
                                    class="px-3 py-1 bg-sky-500 text-white rounded-md hover:bg-sky-600 transition-colors"
                                >
                                    送信
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>

            <!-- 要望追加フォーム -->
            <form method="POST" action="{{ route('trips.request', $trip->id) }}" class="mt-4 edit-mode-only">
                @csrf
                <div class="space-y-2">
                    <textarea 
                        name="content" 
                        rows="2" 
                        placeholder="要望を追加" 
                        class="w-full px-3 py-2 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500"
                    ></textarea>
                    <div class="text-right">
                        <button 
                            type="submit" 
                            class="px-4 py-1.5 bg-sky-500 text-white rounded-md hover:bg-sky-600 transition-colors"
                        >
                            投稿
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <footer class="fixed bottom-0 left-0 right-0 bg-slate-50 shadow-lg">
        <div class="max-w-4xl mx-auto px-4">
            <!-- モード切り替えタブ -->
            <div class="flex justify-center -mt-12 -mx-4">
                <div class="flex w-full bg-slate-50 overflow-hidden">
                    <button 
                        onclick="switchMode('view')" 
                        class="flex-1 px-6 py-3 text-sm font-medium mode-tab flex items-center justify-center" 
                        id="viewTab"
                    >
                        <div class="flex items-center justify-center">
                            <i class="fa-regular fa-eye mr-2"></i>表示モード
                        </div>
                    </button>
                    <button 
                        onclick="switchMode('edit')" 
                        class="flex-1 px-6 py-3 text-sm font-medium mode-tab active flex items-center justify-center" 
                        id="editTab"
                    >
                        <div class="flex items-center justify-center">
                            <i class="fa-solid fa-pen mr-2"></i>編集モード
                        </div>
                    </button>
                </div>
            </div>
    
            <!-- フッターの本体部分 -->
            <div class="flex justify-around text-center h-20 text-sm">  <!-- dateplanning.blade.phpと同じクラスに変更 -->
                <!-- 戻るボタン -->
                <a href="javascript:void(0)" onclick="history.back()" class="absolute left-4 top-1/2 -translate-y-1/2">
                    <i class="fa-solid fa-chevron-left"></i>
                    <p>戻る</p>  <!-- spanからpに変更 -->
                </a>
            </div>
        </div>
    </footer>

    <script>
        function switchMode(mode) {
            const viewTab = document.getElementById('viewTab');
            const editTab = document.getElementById('editTab');
            const editElements = document.querySelectorAll('.edit-mode-only');
            
            if (mode === 'view') {
                viewTab.classList.add('active');
                editTab.classList.remove('active');
                editElements.forEach(el => el.classList.add('hidden'));
            } else {
                editTab.classList.add('active');
                viewTab.classList.remove('active');
                editElements.forEach(el => el.classList.remove('hidden'));
            }
        }

        // 初期状態は表示モード
        document.addEventListener('DOMContentLoaded', () => {
            switchMode('view');
        });

        function toggleLike(requestId) {
            fetch(`/requests/${requestId}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                // いいねの状態を更新
                const button = event.currentTarget;
                const icon = button.querySelector('i');
                const count = button.querySelector('span');
                
                if (data.liked) {
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                } else {
                    icon.classList.remove('fa-solid');
                    icon.classList.add('fa-regular');
                }
                
                count.textContent = data.count;
            });
        }
        // いいねボタンのjava処理
        function toggleLike(requestId, button) {
            fetch(`/requests/${requestId}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    request_id: requestId
                })
            })
            .then(response => response.json())
            .then(data => {
                const icon = button.querySelector('i');
                const countSpan = button.querySelector('.like-count');
                
                if (data.liked) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    button.classList.add('text-red-500');
                    button.classList.remove('text-slate-400');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    button.classList.remove('text-red-500');
                    button.classList.add('text-slate-400');
                }
                
                // いいね数を更新
                countSpan.textContent = data.count;
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>