<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><!-- レスポンシブ -->
    <script src="https://kit.fontawesome.com/ef96165231.js" crossorigin="anonymous"></script><!-- FontAwesome -->
    <title>TripEase</title>

    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-[100vh] text-[0.65rem] bg-slate-100 text-slate-800 font-notosans">
    <header>
    </header>
    <main class="flex-grow">
        <div class="rounded shadow-md bg-slate-50 grid grid-flow-col justify-stretch p-4 my-10 mx-5 h-40">
            <div class="flex flex-col justify-between">
                <p class="text-2xl text-slate-950">Create New Trip</p>
                <div class="flex">
                    <div class="flex-none box-content h-15 w-15 p-1">
                        <p class="text-lg font-poppins text-center">2</p>
                        <p>未タスク</p>
                    </div>
                    <div class="flex-none box-content h-15 w-15 p-1">
                        <p class="text-lg font-poppins text-center">5</p>
                        <p>計画参加中</p>
                    </div>
                    <div class="flex-none box-content h-15 w-15 p-1">
                        <p class="text-lg font-poppins text-center">3</p>
                        <p>管理中</p>
                    </div>
                </div>
            </div>
            <div class="place-content-end">
                <p class="text-center text-sm">{{ $user->name }}さん</p>
            </div>
        </div>
        <div class="h-30 grid grid-cols-2 gap-2 content-evenly box-border p-5 font-semibold">
            <a href="{{ route('trips.create') }}"><p class="text-center shadow-md rounded-full box-content p-2 bg-sky-300 text-white"><i class="fa-solid fa-suitcase-rolling box-content w-6"></i>旅行を新しく計画する</p></a>
            <a href=""><p class="text-center shadow-md rounded-full box-content p-2 bg-slate-50"><i class="fa-solid fa-ticket box-content w-6"></i>URLで参加する</p></a>
            <a href=""><p class="text-center shadow-md rounded-full box-content p-2 bg-slate-500 text-white"><i class="fa-solid fa-pen-nib box-content w-6"></i>管理中の計画を編集する</p></a>
            <a href=""><p class="text-center shadow-md rounded-full box-content p-2 bg-slate-50">コンテンツ</p></a>
        </div>
        <div>
            <p class="box-content p-4 text-lg font-semibold">通知一覧</p>
            @if ($trips->isNotEmpty())
            <div class="mx-4 divide-y divide-dashed">
                @foreach($trips as $trip)
                <div class="flex flex-row justify-between">
                    <div class="mb-4">
                        <p class="text-base">{{ $trip->title }}</p>
                        <p>"日程調整"の期限が迫っているよ！</p>
                        <div class="flex flex-row">
                            <div class="flex flex-row bg-sky-200 justify-around h-4 w-8 text-center">
                                <p><i class="fa-solid fa-clipboard-check text-sky-600"></i></p>
                                <p>8</p>
                            </div>
                            <p class="">{{ $trip->updated_at }}(編集済み)</p>
                        </div>
                    </div>
                    <div class="place-content-end text-sm">
                        <a href="{{ route('trips.show', ['trip' => $trip->id]) }}"><i class="fa-solid fa-ellipsis"></i></a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </main>
    <footer class="bg-slate-50">
        <div class="flex justify-around text-center h-20 text-sm">
                <a href="">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <p>探す</p>
                </a>
                <a href="">
                    <i class="fa-solid fa-user-group"></i>
                    <p>フレンド</p>
                </a>
                <a href="">
                    <i class="fa-regular fa-comment-dots"></i>
                    <p>チャット</p>
                </a>
                <a href="">
                    <i class="fa-solid fa-bars"></i>
                    <p>メニュー</p>
                </a>
        </div>
    </footer>
</body>
</html>