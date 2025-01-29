<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><!-- レスポンシブ -->
    <script src="https://kit.fontawesome.com/ef96165231.js" crossorigin="anonymous"></script><!-- FontAwesome -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script><!-- Alpine.js -->
    <title>TripEase</title>

    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-screen bg-slate-100 text-slate-800 font-notosans text-sm md:text-base">
    <header>
    </header>
    <main class="flex-1 pb-20 md:pb-0"> <!-- PCではフッターが固定されないので余白不要 -->
        <div class="max-w-4xl mx-auto px-4">
            <!-- ユーザー情報カード -->
            <div class="rounded shadow-md bg-slate-50 grid grid-flow-col justify-stretch p-4 my-6 md:my-10 h-40">
                <div class="flex flex-col justify-between">
                    <p class="text-xl md:text-2xl text-slate-950">Create New Trip</p>
                    <div class="flex gap-4">
                        <div class="flex-none box-content p-1">
                            <p class="text-base md:text-lg font-poppins text-center">2</p>
                            <p class="text-xs md:text-sm">未タスク</p>
                        </div>
                        <div class="flex-none box-content p-1">
                            <p class="text-base md:text-lg font-poppins text-center">5</p>
                            <p class="text-xs md:text-sm">計画参加中</p>
                        </div>
                        <div class="flex-none box-content p-1">
                            <p class="text-base md:text-lg font-poppins text-center">3</p>
                            <p class="text-xs md:text-sm">管理中</p>
                        </div>
                    </div>
                </div>
                <div class="place-content-end">
                    <p class="text-center text-sm md:text-base">{{ $user->name }}さん</p>
                </div>
            </div>

            <!-- アクションボタン群 -->
            <div class="h-30 grid grid-cols-2 gap-2 content-evenly box-border p-5 font-semibold">
                <a href="{{ route('trips.create') }}"><p class="text-center shadow-md rounded-full box-content p-3 md:p-4 bg-sky-300 text-white text-sm md:text-base"><i class="fa-solid fa-suitcase-rolling box-content w-6"></i>旅行を新しく計画する</p></a>
                <a href=""><p class="text-center shadow-md rounded-full box-content p-3 md:p-4 bg-slate-50 text-sm md:text-base"><i class="fa-solid fa-ticket box-content w-6"></i>URLで参加する</p></a>
                <a href=""><p class="text-center shadow-md rounded-full box-content p-3 md:p-4 bg-slate-500 text-white text-sm md:text-base"><i class="fa-solid fa-pen-nib box-content w-6"></i>管理中の計画を編集する</p></a>
                <a href=""><p class="text-center shadow-md rounded-full box-content p-3 md:p-4 bg-slate-50 text-sm md:text-base">コンテンツ</p></a>
            </div>

            <!-- 通知一覧 -->
            <div class="mb-8">
                <p class="box-content p-4 text-base md:text-lg font-semibold">通知一覧</p>
                @if ($user->trips->isNotEmpty())
                <div class="mx-4 divide-y divide-dashed">
                    @foreach($user->trips as $trip)
                    <div class="flex flex-row justify-between py-4">
                        <div>
                            <p class="text-sm md:text-base">{{ $trip->title }}</p>
                            <p class="text-xs md:text-sm">"日程調整"の期限が迫っているよ！</p>
                            <div class="flex flex-row items-center gap-2 mt-1">
                                <div class="flex flex-row bg-sky-200 justify-around h-5 w-10 text-center rounded">
                                    <p><i class="fa-solid fa-clipboard-check text-sky-600"></i></p>
                                    <p class="text-xs md:text-sm">8</p>
                                </div>
                                <p class="text-xs md:text-sm">{{ $trip->updated_at }}(編集済み)</p>
                            </div>
                        </div>
                        <div class="text-sm md:text-base">
                            <a href="{{ route('trips.show', ['trip' => $trip->id]) }}"><i class="fa-solid fa-ellipsis"></i></a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                    <p class="text-center text-slate-500 py-4 text-sm md:text-base">参加している旅行プランはありません</p>
                @endif
            </div>
        </div>
    </main>

    <!-- フッター -->
    <footer class="fixed md:static bottom-0 left-0 right-0 bg-slate-50 shadow-lg">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-around text-center h-20 pt-1">
                <!-- 探すボタン -->
                <a href="" class="flex flex-col items-center p-2 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fa-solid fa-magnifying-glass text-lg md:text-xl"></i>
                    <p class="mt-1 text-xs md:text-sm">探す</p>
                </a>

                <!-- フレンドボタン -->
                <a href="" class="flex flex-col items-center p-2 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fa-solid fa-user-group text-lg md:text-xl"></i>
                    <p class="mt-1 text-xs md:text-sm">フレンド</p>
                </a>

                <!-- チャットボタン -->
                <a href="" class="flex flex-col items-center p-2 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fa-regular fa-comment-dots text-lg md:text-xl"></i>
                    <p class="mt-1 text-xs md:text-sm">チャット</p>
                </a>

                <!-- メニューボタンとドロップダウン -->
                <div class="relative" x-data="{ open: false }">
                    <!-- メニューボタン -->
                    <button @click="open = !open" 
                            class="flex flex-col items-center p-2 hover:bg-slate-100 rounded-lg transition-colors">
                        <i class="fa-solid fa-bars text-lg md:text-xl"></i>
                        <p class="mt-1 text-xs md:text-sm">メニュー</p>
                    </button>
                    
                    <!-- ドロップダウンメニュー -->
                    <div x-show="open" 
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute bottom-full right-0 mb-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <!-- ログアウトボタン -->
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" 
                                        class="block w-full text-left px-4 py-2 text-sm md:text-base text-slate-700 hover:bg-slate-100 transition-colors">
                                    <i class="fa-solid fa-right-from-bracket mr-2"></i>
                                    ログアウト
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>