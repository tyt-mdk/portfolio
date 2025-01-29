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
        /* プレースホルダーのスタイル */
        #request-add-form input::placeholder {
            color: #94a3b8; /* text-slate-400 */
        }

        /* フォーカス時のアウトラインを削除 */
        #request-add-form input:focus {
            outline: none;
        }
        /* プレースホルダーのスタイル */
        form[id^="comment-add-form-"] input::placeholder {
            color: #94a3b8; /* text-slate-400 */
        }

        /* フォーカス時のアウトラインを削除 */
        form[id^="comment-add-form-"] input:focus {
            outline: none;
        }
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
        .edit-mode-only {
            display: none !important;
        }
        body.edit-mode .edit-mode-only {
            display: block !important;
        }
        /* flexコンテナ用の追加定義 */
        body.edit-mode .edit-mode-only.flex {
            display: flex !important;
        }
        body:not(.edit-mode) .view-mode-only {
            display: block;
        }
        body.edit-mode .view-mode-only {
            display: none;
        }
        .edit-mode [data-editable] p {
            cursor: pointer;
        }
        [data-editable] form {
            display: none;
        }
        body.edit-mode [data-editable].editing form {
            display: block;
        }
        // 削除確認用のトースト通知のスタイルを追加
        .confirm-toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            z-index: 50;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .confirm-toast button {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
    </style>

    <title>Tripease</title>
    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-screen bg-slate-100 text-slate-800 font-notosans text-sm md:text-base">
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
    <main class="flex-1 max-w-4xl mx-auto w-full px-4 py-4 md:py-6 space-y-4 md:space-y-6 pb-32 md:pb-24">
        <!-- タイトルと目的セクション -->
        <section class="bg-white rounded-lg shadow-sm p-3 md:p-4 space-y-3 md:space-y-4 relative">
            <form id="tripEditForm" method="POST" action="{{ route('trips.update', $trip) }}" class="space-y-3 md:space-y-4">
                @csrf
                @method('PUT')
                
                <!-- タイトル -->
                <div class="space-y-1">
                    <p class="text-slate-400 text-sm md:text-base">タイトル</p>
                    <div class="view-mode-only">
                        <h1 class="text-lg md:text-xl font-medium text-slate-800">{{ $trip->title }}</h1>
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

                <!-- 概要メモ -->
                <div class="space-y-1">
                    <p class="text-slate-400 text-sm">概要メモ</p>
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
        <section class="bg-white rounded-lg shadow-sm p-3 md:p-4 space-y-3 md:space-y-4">
            <h2 class="text-base md:text-lg font-medium text-slate-700 border-b border-slate-200 pb-2">みんなの要望</h2>

            <!-- 要望追加フォーム -->
            <div class="mt-4 mb-6 edit-mode-only" style="display: none;">
                <div id="request-add-placeholder" class="cursor-pointer">
                    <div class="text-slate-400 border-b-2 border-slate-600 py-2">
                        要望を追加する
                    </div>
                </div>
            
                <form id="request-add-form" action="{{ route('trips.request', $trip->id) }}" method="POST" class="hidden">
                    @csrf
                    <div class="flex flex-col space-y-3">
                        <input type="text" 
                               name="content" 
                               class="w-full border-0 border-b-2 border-slate-600 focus:ring-0 focus:border-sky-500 py-2 text-slate-700 bg-transparent transition-colors"
                               placeholder="要望を追加する">
                        <div class="flex items-center justify-end space-x-3 pt-2">
                            <button type="button" 
                                    onclick="cancelAddRequest()"
                                    class="px-4 py-1.5 text-slate-500 hover:text-slate-600 rounded-full text-sm transition-colors">
                                キャンセル
                            </button>
                            <button type="submit" 
                                    class="px-4 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-full text-sm transition-colors">
                                投稿
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- 要望一覧 -->
            <div class="space-y-4">
                @foreach($userRequests as $request)
                    <div class="border-b border-slate-100 last:border-0 pb-4">
                        <!-- ヘッダー部分（ユーザー名、日時、いいねボタン） -->
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-2">
                                <p class="font-medium text-slate-700">{{ $request->user->name }}</p>
                                <p class="text-slate-400">
                                    {{ \Carbon\Carbon::parse($request->created_at)->format('n/j H:i') }}
                                </p>
                            </div>
                        </div>

                        <!-- 要望内容 -->
                        <div class="flex-1" data-editable data-type="request" data-id="{{ $request->id }}">
                            <!-- 表示モード -->
                            <div id="request-content-{{ $request->id }}">
                                <p class="text-slate-600 rounded px-2 py-1 transition-colors hover:bg-slate-50">
                                    {{ $request->content }}
                                </p>
                            </div>
                            <!-- 編集モード -->
                            <form id="request-edit-form-{{ $request->id }}"
                                    style="display: none;"
                                    onsubmit="return false;">
                                @csrf
                                @method('PUT')
                                <div class="flex items-start space-x-2">
                                    <textarea name="content" 
                                            class="flex-1 px-2 py-1 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500"
                                            rows="2">{{ $request->content }}</textarea>
                                    <div class="flex items-center space-x-1">
                                        <button type="button" 
                                                onclick="cancelEdit('request', {{ $request->id }})"
                                                class="p-1 text-slate-400 hover:text-slate-600">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                        <button type="button" 
                                                onclick="deleteRequest({{ $request->id }})"
                                                class="p-1 text-slate-400 hover:text-rose-500 edit-mode-only">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- アクションボタン部分 -->
                        <div class="flex items-center space-x-3 mt-2">
                            <!-- いいねボタン（常に表示） -->
                            <button 
                                onclick="toggleLike({{ $request->id }}, this)" 
                                class="like-button flex items-center space-x-1 {{ $request->isLikedBy(Auth::user()) ? 'text-red-500' : 'text-slate-400' }}"
                            >
                                <i class="fa-heart {{ $request->isLikedBy(Auth::user()) ? 'fas' : 'far' }}"></i>
                                <span class="like-count">{{ $request->likes->count() }}</span>
                            </button>

                            <!-- 返信ボタン（編集モード時のみ表示） -->
                            <button type="button" 
                                    onclick="showCommentForm({{ $request->id }})"
                                    class="edit-mode-only text-slate-400 hover:text-slate-600 text-sm transition-colors"
                                    style="display: none;">
                                <i class="far fa-comment"></i>
                            </button>

                            <!-- コメント数表示/折りたたみボタン -->
                            <button type="button"
                                    onclick="toggleComments({{ $request->id }})"
                                    class="text-slate-400 hover:text-slate-600 text-sm transition-colors">
                                <i class="fa-solid fa-caret-down comment-icon-{{ $request->id }} {{ $request->comments->count() > 0 ? '' : 'hidden' }}"></i>
                                <span class="comment-count-{{ $request->id }}">{{ $request->comments->count() > 0 ? $request->comments->count().'件の返信' : '' }}</span>
                            </button>
                        </div>

                        <!-- コメント追加フォーム -->
                        <div id="comments-section-{{ $request->id }}" class="mt-2 hidden">
                            <!-- コメント入力フォーム -->
                            <form id="comment-add-form-{{ $request->id }}" 
                                action="{{ route('requests.comment', $request->id) }}" 
                                method="POST" 
                                class="hidden mb-4">
                                @csrf
                                <div class="flex flex-col space-y-3">
                                    <input type="text" 
                                        name="content" 
                                        class="w-full border-0 border-b-2 border-slate-600 focus:ring-0 focus:border-sky-500 py-2 text-slate-700 bg-transparent transition-colors text-sm"
                                        placeholder="コメントを追加">
                                    <div class="flex items-center justify-end space-x-3 pt-2">
                                        <button type="button" 
                                                onclick="cancelCommentForm({{ $request->id }})"
                                                class="px-4 py-1.5 text-slate-500 hover:text-slate-600 rounded-full text-sm transition-colors">
                                            キャンセル
                                        </button>
                                        <button type="submit" 
                                                class="px-4 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-full text-sm transition-colors">
                                            送信
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <div class="mt-4 pl-4 space-y-2">
                                @foreach($request->comments as $comment)
                                    <div class="group">
                                        <!-- コメントヘッダー -->
                                        <div class="flex items-start space-x-2">
                                            <p class="font-medium text-slate-700">{{ $comment->user->name }}</p>
                                            <p class="text-slate-400">
                                                {{ \Carbon\Carbon::parse($comment->created_at)->format('n/j H:i') }}
                                            </p>
                                        </div>
                                        
                                        <!-- コメント内容 -->
                                        @if($comment->user_id === Auth::id())
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1" data-editable data-type="comment" data-id="{{ $comment->id }}">
                                                    <!-- 表示モード -->
                                                    <div id="comment-content-{{ $comment->id }}">
                                                        <p class="text-slate-600 rounded px-2 py-1 transition-colors hover:bg-slate-50">
                                                            {{ $comment->content }}
                                                        </p>
                                                    </div>
                                                    <!-- コメントの編集フォーム -->
                                                    <form action="{{ route('request.comments.update', $comment->id) }}" 
                                                        method="POST" 
                                                        style="display: none;"
                                                        id="comment-edit-form-{{ $comment->id }}"
                                                        onsubmit="return false;">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="flex items-start space-x-2">
                                                        <input type="text" 
                                                                name="content" 
                                                                value="{{ $comment->content }}"
                                                                class="flex-1 px-2 py-1 border border-slate-200 rounded-md focus:outline-none focus:border-sky-500">
                                                        <div class="flex items-center space-x-1">
                                                            <button type="button" 
                                                                    onclick="cancelEdit('comment', {{ $comment->id }})"
                                                                    class="p-1 text-slate-400 hover:text-slate-600">
                                                                <i class="fa-solid fa-xmark"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    </form>
                                                </div>
                                                <!-- 削除ボタン（編集モードのみ表示） -->
                                                <div class="edit-mode-only opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <button type="button"
                                                            onclick="deleteComment({{ $comment->id }})" 
                                                            class="p-1 text-slate-400 hover:text-rose-500">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-slate-600 px-2 py-1">{{ $comment->content }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </main>

    <!-- フッター -->
    <footer class="fixed md:static bottom-0 left-0 right-0 bg-slate-50 shadow-lg">
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
            <div class="grid grid-cols-3 items-start h-20 text-sm pt-1">
                <!-- 戻るボタン（左） -->
                <div class="justify-self-start">
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-center w-10 h-10 bg-slate-200 rounded-full hover:bg-slate-300 transition-colors">
                        <i class="fa-solid fa-chevron-left text-slate-600"></i>
                    </a>
                </div>

                <!-- 確定ボタン（中央） -->
                <div class="justify-self-center w-full px-2 edit-mode-only" style="display: none;">
                    <button type="submit" 
                            onclick="submitAllForms()"
                            class="flex items-center justify-center mx-auto w-32 h-10 bg-sky-500 hover:bg-sky-600 text-white rounded-full transition-colors">
                        <i class="fa-solid fa-check"></i>
                    </button>
                </div>

                <!-- 右側の空のスペース -->
                <div class="justify-self-end"></div>
            </div>
        </div>
    </footer>

    <script>
        function showCommentForm(requestId) {
            const section = document.getElementById(`comments-section-${requestId}`);
            const form = document.getElementById(`comment-add-form-${requestId}`);
            
            section.classList.remove('hidden');
            form.classList.remove('hidden');
            form.querySelector('input').focus();
        }

        function cancelCommentForm(requestId) {
            const form = document.getElementById(`comment-add-form-${requestId}`);
            form.classList.add('hidden');
            form.reset();
        }

        document.querySelectorAll('form[id^="comment-add-form-"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const requestId = this.id.match(/\d+/)[0];
                const formData = new FormData(this);
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    if (response.ok) {
                        // 成功時は画面をリロード
                        location.reload();
                    } else {
                        throw new Error('コメントの投稿に失敗しました');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('コメントの投稿に失敗しました', 'error');
                });
            });
        });

        document.getElementById('request-add-placeholder').addEventListener('click', function() {
            this.style.display = 'none';
            const form = document.getElementById('request-add-form');
            form.style.display = 'block';
            const input = form.querySelector('input');
            input.focus();
            
            // プレースホルダーテキストの位置を調整するためのスタイル
            input.style.height = 'auto';
        });

        function cancelAddRequest() {
            document.getElementById('request-add-form').style.display = 'none';
            document.getElementById('request-add-placeholder').style.display = 'block';
            document.getElementById('request-add-form').reset();
        }

        // スタイルの定義
        const toastStyles = {
            confirm: `
                fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 
                bg-white/95 backdrop-blur-sm p-6 rounded-lg shadow-lg z-50 
                flex flex-col items-center space-y-4 min-w-[300px]
            `,
            message: `
                flex flex-col items-center text-center space-y-1
            `,
            buttonContainer: `
                flex items-center justify-center space-x-3 w-full
            `,
            button: {
                cancel: 'px-6 py-2 bg-slate-100 text-slate-600 rounded-full hover:bg-slate-200 transition-colors flex-1',
                delete: 'px-6 py-2 bg-rose-500 text-white rounded-full hover:bg-rose-600 transition-colors flex-1'
            }
        };

        function createDeleteConfirmation(type, id) {
            // 既存のトーストがあれば削除
            document.querySelector('.confirm-toast')?.remove();
            
            const toast = document.createElement('div');
            toast.className = `confirm-toast ${toastStyles.confirm}`;
            toast.innerHTML = `
                <div class="${toastStyles.message}">
                    <p class="text-slate-700">この${type === 'comment' ? 'コメント' : '要望'}を削除します。</p>
                    <p class="text-slate-500 text-sm">削除された${type === 'comment' ? 'コメント' : '要望'}は復旧できません。</p>
                </div>
                <div class="${toastStyles.buttonContainer}">
                    <button type="button" class="${toastStyles.button.cancel}" data-action="cancel">キャンセル</button>
                    <button type="button" class="${toastStyles.button.delete}" data-action="delete">削除</button>
                </div>
            `;
            
            // イベントリスナーを追加
            toast.querySelector('[data-action="delete"]').addEventListener('click', () => {
                confirmDelete(type, id);
            });
            
            toast.querySelector('[data-action="cancel"]').addEventListener('click', () => {
                toast.remove();
            });
            
            document.body.appendChild(toast);
        }

        // トースト通知の表示関数
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-20 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded-lg text-sm ${
                type === 'success' ? 'bg-sky-500/90' : 'bg-rose-500/90'
            } text-white shadow-sm backdrop-blur-sm z-50`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // いいね機能の実装
        function toggleLike(requestId, button) {
            fetch(`/requests/${requestId}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const icon = button.querySelector('i');
                const countSpan = button.querySelector('.like-count');
                
                if (data.liked) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    button.classList.remove('text-slate-400');
                    button.classList.add('text-red-500');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    button.classList.remove('text-red-500');
                    button.classList.add('text-slate-400');
                }
                
                countSpan.textContent = data.count;
            })
            .catch(error => console.error('Error:', error));
        }

        function toggleComments(requestId) {
            const section = document.getElementById(`comments-section-${requestId}`);
            const icon = document.querySelector(`.comment-icon-${requestId}`);
            
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                icon.classList.remove('fa-caret-down');
                icon.classList.add('fa-caret-up');
            } else {
                section.classList.add('hidden');
                icon.classList.remove('fa-caret-up');
                icon.classList.add('fa-caret-down');
            }
        }

        function submitAllForms() {
            // タイトルと概要の更新
            const tripEditForm = document.getElementById('tripEditForm');
            const formData = new FormData(tripEditForm);

            // 要望とコメントの更新
            const promises = [];
            
            // タイトルと概要の更新
            promises.push(
                fetch(tripEditForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
            );

            // 要望の更新
            document.querySelectorAll('[data-type="request"] textarea').forEach(textarea => {
                const requestId = textarea.closest('form').id.match(/\d+/)[0];
                promises.push(
                    fetch(`/trip-requests/${requestId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ content: textarea.value })
                    })
                );
            });

            // コメントの更新
            document.querySelectorAll('[data-type="comment"] input[type="text"]').forEach(input => {
                const commentId = input.closest('form').id.match(/\d+/)[0];
                promises.push(
                    fetch(`/request-comments/${commentId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ content: input.value })
                    })
                );
            });

            Promise.all(promises)
                .then(() => {
                    showToast('更新しました', 'success');
                    setTimeout(() => location.reload(), 1000);
                })
                .catch(error => {
                    showToast('更新に失敗しました', 'error');
                });
        }

        // 編集モードの切り替え
        function switchMode(mode) {
            console.log('Switching to mode:', mode);
            
            const viewTab = document.getElementById('viewTab');
            const editTab = document.getElementById('editTab');
            const requestAddContainer = document.getElementById('request-add-placeholder').closest('.edit-mode-only');
            
            if (mode === 'edit') {
                console.log('Enabling edit mode');
                document.body.classList.add('edit-mode');
                editTab.classList.add('active');
                viewTab.classList.remove('active');
                
                // 要望追加フォームを表示
                if (requestAddContainer) {
                    requestAddContainer.style.display = 'block';
                }
                
                // 編集可能な要素にイベントリスナーを追加
                document.querySelectorAll('[data-editable]').forEach(el => {
                    const contentDiv = el.querySelector('div[id^="request-content-"], div[id^="comment-content-"]');
                    if (contentDiv) {
                        contentDiv.style.cursor = 'pointer';
                        contentDiv.onclick = function() {
                            const type = el.dataset.type;
                            const id = el.dataset.id;
                            startEdit(type, id);
                        };
                    }
                });
                // 返信ボタンの表示制御を追加
                document.querySelectorAll('.edit-mode-only').forEach(el => {
                    el.style.display = 'block';
                });
            } else {
                console.log('Disabling edit mode');
                document.body.classList.remove('edit-mode');
                viewTab.classList.add('active');
                editTab.classList.remove('active');
                
                // 要望追加フォームを非表示にし、リセット
                if (requestAddContainer) {
                    requestAddContainer.style.display = 'none';
                    cancelAddRequest(); // フォームをリセット
                }
                
                // イベントリスナーを削除し、すべての要素を表示モードに戻す
                document.querySelectorAll('[data-editable]').forEach(el => {
                    const contentDiv = el.querySelector('div[id^="request-content-"], div[id^="comment-content-"]');
                    const formEl = el.querySelector('form');
                    if (contentDiv) {
                        contentDiv.style.cursor = 'default';
                        contentDiv.onclick = null;
                        contentDiv.style.display = 'block';
                    }
                    if (formEl) {
                        formEl.style.display = 'none';
                    }
                });
                // 返信ボタンの非表示制御を追加
                document.querySelectorAll('.edit-mode-only').forEach(el => {
                    el.style.display = 'none';
                });
            }
        }
    
        // 編集の開始
        function startEdit(type, id) {
            if (!document.body.classList.contains('edit-mode')) return;
            
            const contentDiv = document.getElementById(`${type}-content-${id}`);
            const formEl = document.getElementById(`${type}-edit-form-${id}`);
            
            if (!contentDiv || !formEl) return;

            contentDiv.style.display = 'none';
            formEl.style.display = 'block';

            // そのコメントの編集モード要素を表示
            if (type === 'comment') {
                const commentGroup = contentDiv.closest('.group');
                const editModeElements = commentGroup.querySelectorAll('.edit-mode-only');
                editModeElements.forEach(el => {
                    el.style.opacity = '1';
                });
            }
            
            const input = formEl.querySelector('textarea, input[type="text"]');
            if (input) {
                input.focus();
                input.selectionStart = input.selectionEnd = input.value.length;
            }
        }
    
        // フォームのイベントリスナー設定
        function setupFormListeners(form, type, id) {
            // キャンセルボタンのイベント
            const cancelBtn = form.querySelector('button[type="button"]');
            if (cancelBtn) {
                cancelBtn.onclick = () => cancelEdit(type, id);
            }
    
            // フォームの送信イベント
            form.onsubmit = function(e) {
                e.preventDefault();
                submitEdit(form, type, id);
            };
        }
    
        // 編集のキャンセル
        function cancelEdit(type, id) {
            const contentDiv = document.getElementById(`${type}-content-${id}`);
            const formEl = document.getElementById(`${type}-edit-form-${id}`);
            
            if (contentDiv && formEl) {
                contentDiv.style.display = 'block';
                formEl.style.display = 'none';

                // そのコメントの編集モード要素（ゴミ箱アイコンなど）を非表示
                if (type === 'comment') {
                    const commentGroup = contentDiv.closest('.group');
                    const editModeElements = commentGroup.querySelectorAll('.edit-mode-only');
                    editModeElements.forEach(el => {
                        el.style.opacity = '0';
                    });
                }
            }
        }
    
        // 編集内容の送信
        function submitEdit(form, type, id) {
            const formData = new FormData(form);
            formData.append('_method', 'PUT');
    
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const contentEl = document.getElementById(`${type}-content-${id}`);
                    if (contentEl) {
                        contentEl.textContent = formData.get('content');
                        cancelEdit(type, id);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('更新に失敗しました');
            });
        }
    
        // 要望の削除
        function deleteRequest(requestId) {
            createDeleteConfirmation('request', requestId);
        }

        // コメントの削除
        function deleteComment(commentId) {
            createDeleteConfirmation('comment', commentId);
        }

        // 実際の削除処理を行う関数
        function confirmDelete(type, id) {
            console.log('Confirming delete:', type, id);
            const url = type === 'comment' ? `/request-comments/${id}` : `/trip-requests/${id}`;
            console.log('Delete URL:', url);

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('削除に失敗しました');
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    if (type === 'comment') {
                        const commentElement = document.querySelector(`[data-type="comment"][data-id="${id}"]`);
                        if (commentElement) {
                            commentElement.closest('.group').remove();
                        }
                    } else {
                        const requestElement = document.querySelector(`[data-type="request"][data-id="${id}"]`);
                        if (requestElement) {
                            requestElement.closest('.border-b').remove();
                        }
                    }
                    showToast('削除しました', 'success');
                } else {
                    throw new Error(data.message || '削除に失敗しました');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                showToast(error.message || '削除に失敗しました', 'error');
            })
            .finally(() => {
                document.querySelector('.confirm-toast')?.remove();
            });
        }

        // 結果表示用のトースト
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-20 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded-full text-sm ${
                type === 'success' ? 'bg-sky-500/90' : 'bg-rose-500/90'
            } text-white shadow-sm backdrop-blur-sm z-50`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    
        // 初期設定
        document.addEventListener('DOMContentLoaded', () => {
            switchMode('view');
        });
    </script>
</body>
</html>