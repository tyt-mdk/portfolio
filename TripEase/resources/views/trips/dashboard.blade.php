<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TripEase</title>

    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-[100vh]">
    <header>
    </header>
    <main>
        <div class="shadow-md bg-slate-50">
            <p class="text-2xl">Create New Trip</p>
            <div class="flex">
                <div class="flex-none box-content h-15 w-15 p-1">
                    <p class="text-xl font-poppins text-center">2</p>
                    <p class="text-sm">未タスク</p>
                </div>
                <div class="flex-none box-content h-15 w-15 p-1">
                    <p class="text-xl font-poppins text-center">5</p>
                    <p class="text-sm">計画参加中</p>
                </div>
                <div class="flex-none box-content h-15 w-15 p-1">
                    <p class="text-xl font-poppins text-center">3</p>
                    <p class="text-sm">管理中</p>
                </div>
            </div>
        </div>
        <div class="h-20 grid grid-cols-2 gap-2 content-evenly">
            <p class="text-center"><a href="{{ route('trips.index') }}">旅行を新しく計画する</a></p>
            <p class="text-center"><a href="">URLで参加する</a></p>
            <p class="text-center"><a href="">管理中の計画を編集する</a></p>
            <p class="text-center"><a href="">コンテンツ</a></p>
        </div>
        <div>
            <p>通知一覧</p>
            <p>「○○旅行計画」の"日程調整"の期限が迫っているよ！</p>
            <p>「××旅行計画」の"宿泊地"の投票が終わったみたい！</p>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>