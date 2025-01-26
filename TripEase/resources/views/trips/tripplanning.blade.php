<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><!-- レスポンシブ -->
    <script src="https://kit.fontawesome.com/ef96165231.js" crossorigin="anonymous"></script><!-- FontAwesome -->
    <title>Tripease</title>

    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-[100vh] text-[0.65rem] bg-slate-100 text-slate-800 font-notosans">
    <header>
    </header>
    <main>
        <div class="rounded shadow-md bg-slate-50 grid grid-flow-col justify-stretch p-4 my-10 mx-5 h-20">
            <p class="text-2xl text-slate-950">旅行計画作成ページ</p>
        </div>
        <div class="rounded shadow-md bg-slate-50 grid grid-flow-col justify-stretch p-4 my-10 mx-5 h-50 text-xs">
            <form action="{{ route('trips.store') }}" method="post">
                @csrf
                <div>
                    <div class="m-2">
                        <label>
                            <p class="font-medium p-1">旅行タイトル</p>
                            <input type="text" name="title" class="rounded shadow-md border p-2">
                        </label>
                        @error('title')
                            <div><p>{{  $message }}</p></div>
                        @enderror
                    </div>
                    <div class="m-2">
                        <label class="m-2">
                            <p class="font-medium p-1">概要メモ</p>
                            <textarea 
                                name="description" 
                                rows="4" 
                                class="w-full rounded shadow-md border p-2 resize-none"
                            ></textarea>
                        </label>
                    </div>
                    <div class="border-t-4 border-slate-100 place-items-end mt-5 pt-3">
                        <p class="rounded box-content p-2 bg-sky-300 text-white"><button type="submit" class="font-medium">作成する</button></p>
                    </div>
                </div>
            </form>
        </div>
    </main>
    <footer class="fixed bottom-0 left-0 right-0 bg-slate-50 shadow-lg">
        <div class="max-w-4xl mx-auto px-4">
            <!-- フッターの本体部分 -->
            <div class="grid grid-cols-3 items-start h-20 text-sm pt-1">
                <!-- 戻るボタン（左） -->
                <div class="justify-self-start">
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-center w-10 h-10 bg-slate-200 rounded-full hover:bg-slate-300 transition-colors">
                        <i class="fa-solid fa-chevron-left text-slate-600"></i>
                    </a>
                </div>
                <!-- 中央と右側は空 -->
                <div></div>
                <div></div>
            </div>
        </div>
    </footer>
</body>
</html>