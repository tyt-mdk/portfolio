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
        <div  class="rounded shadow-md bg-slate-50 grid grid-flow-col justify-stretch p-4 my-10 mx-5 h-20">
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
                            <input type="text" name="description" class="rounded shadow-md border p-2">
                        </label>
                    </div>
                    <div class="border-t-4 border-slate-100 place-items-end mt-5 pt-3">
                        <p class="rounded box-content p-2 bg-sky-300 text-white"><button type="submit" class="font-medium">作成する</button></p>
                    </div>
                </div>
            </form>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>