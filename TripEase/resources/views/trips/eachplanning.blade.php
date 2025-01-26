<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
    <!-- フラッシュメッセージ（固定位置） -->
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-4xl px-4">
        @if(session('success'))
            <div id="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 transition-opacity duration-500" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div id="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 transition-opacity duration-500" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
    </div>
    <header>
    </header>
    <main class="flex-1 max-w-4xl mx-auto w-full px-4 py-6 space-y-6 pb-32">
        <!-- タイトルと目的セクション -->
        <section class="bg-white rounded-lg shadow-sm p-4 space-y-4 relative">
            <form id="tripEditForm" method="POST" action="{{ route('trips.update', $trip) }}" class="space-y-4">
                @csrf
                @method('PUT')
                
                <!-- タイトル -->
                <div class="space-y-1">
                    <p class="text-slate-400 text-sm">タイトル</p>
                    <div class="view-mode-only">
                        <h1 class="text-lg font-medium text-slate-800">{{ $trip->title }}</h1>
                    </div>
                    <div class="edit-mode-only">
                        <input type="text" 
                            name="title" 
                            value="{{ $trip->title }}" 
                            class="w-full px-3 py-2 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500"
                            required>
                    </div>
                </div>

                <!-- 区切り線 -->
                <div class="border-t border-slate-200"></div>

                <!-- 目的 -->
                <div class="space-y-1">
                    <p class="text-slate-400 text-sm">目的</p>
                    <div class="view-mode-only">
                        <p class="text-slate-700 whitespace-pre-wrap">{{ $trip->description }}</p>
                    </div>
                    <div class="edit-mode-only">
                        <textarea name="description" 
                                rows="4" 
                                class="w-full px-3 py-2 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500"
                                required>{{ $trip->description }}</textarea>
                    </div>
                </div>
            </form>
        </section>

        <!-- 共有リンク作成ボタン -->
        <div x-data="{ 
            showShareLink: false, 
            shareUrl: '',
            generateShare() {
                console.log('Generating share link...');
                fetch('{{ route('trips.generateShareLink', $trip) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    this.shareUrl = data.share_url;
                    this.showShareLink = true;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('共有リンクの生成に失敗しました');
                });
            }
        }" class="absolute top-4 right-4">
            <!-- 共有ボタン -->
            <button @click="generateShare()"
                    class="inline-flex items-center justify-center space-x-2 px-3 py-2 bg-white text-slate-600 text-sm font-medium rounded-full border border-slate-200 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-share-nodes"></i>
                <span>共有</span>
            </button>

            <!-- 共有リンクのモーダル -->
            <template x-if="showShareLink">
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                    @click.self="showShareLink = false">
                    <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full mx-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-slate-900">共有リンク</h3>
                            <button @click="showShareLink = false" class="text-slate-400 hover:text-slate-500">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <input type="text" 
                                    x-model="shareUrl" 
                                    readonly 
                                    class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-slate-600 text-sm focus:outline-none">
                                <button @click="navigator.clipboard.writeText(shareUrl)"
                                        class="inline-flex items-center justify-center px-3 py-2 bg-sky-500 text-white text-sm font-medium rounded-md hover:bg-sky-600 transition-colors">
                                    <i class="fa-regular fa-copy mr-2"></i>
                                    コピー
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- 候補日一覧テーブル -->
        <section class="bg-white rounded-lg shadow-sm overflow-hidden">
            @php
                // 投票済みのユーザーのみを取得
                $votedUsers = $users->filter(function($user) use ($dateVotes) {
                    return $dateVotes->where('user_id', $user->id)->count() > 0;
                });
                
                $hasAnyVotes = $dateVotes->count() > 0;
                $loginUserVoted = $dateVotes->where('user_id', auth()->id())->count() > 0;
                $otherUsersVoted = $dateVotes->where('user_id', '!=', auth()->id())->count() > 0;
                $allUsersVoted = $users->every(function($user) use ($dateVotes, $candidateDates) {
                    return $dateVotes->where('user_id', $user->id)->count() === $candidateDates->unique('proposed_date')->count();
                });
                
                // 全参加者が投票済みかどうかを確認
                $allParticipantsVoted = $votedUsers->count() === $users->count();
            @endphp

            @if($candidateDates->count() > 0 && $votedUsers->count() > 0)
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
                            @foreach($votedUsers as $user)
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

                <!-- 状態メッセージ -->
                <div class="p-4 text-center text-slate-500">
                    @if($allParticipantsVoted)
                        結果を表示しています。
                    @elseif($loginUserVoted && !$otherUsersVoted)
                        他の参加者の登録待ちです。
                    @elseif(!$loginUserVoted && $otherUsersVoted)
                        候補日はまだ登録されていません。
                    @elseif($loginUserVoted && $otherUsersVoted)
                        他の参加者の登録待ちです。
                    @endif
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

    <!-- フッター -->
    <footer class="fixed bottom-0 left-0 right-0 bg-slate-50 shadow-lg">
        <div class="max-w-4xl mx-auto px-4">
            <!-- モード切り替えタブ -->
            <div class="flex justify-center -mt-8 -mx-4">
                <div class="flex w-full bg-slate-50 overflow-hidden">
                    <button 
                        onclick="switchMode('view')" 
                        class="flex-1 px-6 py-2 text-sm font-medium mode-tab flex items-center justify-center" 
                        id="viewTab"
                    >
                        <div class="flex items-center justify-center">
                            <i class="fa-regular fa-eye mr-2"></i>表示モード
                        </div>
                    </button>
                    <button 
                        onclick="switchMode('edit')" 
                        class="flex-1 px-6 py-2 text-sm font-medium mode-tab active flex items-center justify-center" 
                        id="editTab"
                    >
                        <div class="flex items-center justify-center">
                            <i class="fa-solid fa-pen mr-2"></i>編集モード
                        </div>
                    </button>
                </div>
            </div>

            <!-- フッターの本体部分 -->
            <div class="grid grid-cols-3 items-start h-20 text-sm">
                <!-- 戻るボタン（左） -->
                <div class="justify-self-start">
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-center w-10 h-10 bg-slate-200 rounded-full hover:bg-slate-300 transition-colors">
                        <i class="fa-solid fa-chevron-left text-slate-600"></i>
                    </a>
                </div>

                <!-- 確定ボタン（中央） -->
                <div class="justify-self-center w-full px-2">
                    <button type="submit" 
                            form="tripEditForm" 
                            class="flex items-center justify-center mx-auto w-32 h-10 bg-sky-500 hover:bg-sky-600 text-white rounded-full transition-colors edit-mode-only">
                        <i class="fa-solid fa-check"></i>
                    </button>
                </div>

                <!-- 右側の空のスペース -->
                <div class="justify-self-end"></div>
            </div>
        </div>
    </footer>

    <script>
        // フラッシュメッセージを自動的に消す
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');

            if (successMessage) {
                // 3秒後にフェードアウトを開始
                setTimeout(() => {
                    successMessage.style.opacity = '0';
                    // フェードアウトが完了したら要素を削除
                    setTimeout(() => {
                        successMessage.remove();
                    }, 500);
                }, 3000);
            }

            if (errorMessage) {
                setTimeout(() => {
                    errorMessage.style.opacity = '0';
                    setTimeout(() => {
                        errorMessage.remove();
                    }, 500);
                }, 3000);
            }
        });

        function switchMode(mode) {
            const viewTab = document.getElementById('viewTab');
            const editTab = document.getElementById('editTab');
            const viewElements = document.querySelectorAll('.view-mode-only');
            const editElements = document.querySelectorAll('.edit-mode-only');
            
            if (mode === 'view') {
                viewTab.classList.add('active');
                editTab.classList.remove('active');
                viewElements.forEach(el => el.classList.remove('hidden'));
                editElements.forEach(el => el.classList.add('hidden'));
            } else {
                editTab.classList.add('active');
                viewTab.classList.remove('active');
                viewElements.forEach(el => el.classList.add('hidden'));
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