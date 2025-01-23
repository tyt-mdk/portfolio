<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://kit.fontawesome.com/ef96165231.js" crossorigin="anonymous"></script>
    <title>Tripease</title>
    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-screen bg-slate-100 text-slate-800 font-notosans text-[0.65rem]">  <!-- text-[0.65rem]を追加 -->
    <header>
    </header>
    <main class="flex-1 max-w-4xl mx-auto w-full px-4 py-6 space-y-6 pb-20">
        <section class="bg-white rounded-lg shadow-sm p-4">
            <p class="text-slate-400">タイトル</p>
            <p class="text-slate-600">{{ $trip->title }}</p>
            <a href="#" class="inline-block mt-2 text-sky-500 hover:text-sky-600">
                <i class="fa-solid fa-pen-to-square mr-1"></i>編集
            </a>
        </section>
        <!-- 説明文 -->
        <section class="bg-white rounded-lg shadow-sm p-4">
            <p class="text-slate-400">目的</p>
            <p class="text-slate-600">{{ $trip->description }}</p>
            <a href="#" class="inline-block mt-2 text-sky-500 hover:text-sky-600">
                <i class="fa-solid fa-pen-to-square mr-1"></i>編集
            </a>
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
        <div class="text-center">
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
                                class="flex items-center space-x-1 text-slate-400 hover:text-rose-500 transition-colors"
                                onclick="toggleLike({{ $request->id }})"
                            >
                                <i class="fa-{{ $request->likes->contains('user_id', Auth::id()) ? 'solid' : 'regular' }} fa-heart"></i>
                                <span>{{ $request->likes->count() }}</span>
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
                        <form method="POST" action="{{ route('requests.comment', $request->id) }}" class="mt-2 pl-4">
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
            <form method="POST" action="{{ route('trips.request', $trip->id) }}" class="mt-4">
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

    <footer  class="fixed bottom-0 left-0 right-0 bg-slate-50">
        <div class="flex justify-around text-center h-20 text-sm">
            <!-- 戻るボタン -->
            <a href="javascript:void(0)" onclick="history.back()" class="absolute left-4 top-1/2 -translate-y-1/2">
                <i class="fa-solid fa-chevron-left"></i>
                <p>戻る</p>
            </a>
        </div>
    </footer>

    <script>
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
    </script>
</body>
</html>