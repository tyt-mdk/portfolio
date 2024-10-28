<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tripease</title>
</head>
<body>
    <header>
    </header>
    <main>
        <p>旅行計画作成ページ</p>
        <form action="{{ route('trips.store') }}" method="post">
            @csrf
            <div>
                <p><label>旅行タイトル<input type="text" name="trip_title"></label></p>
                @error('trip_title')
                    <div><p>{{  $message }}</p></div>
                @enderror
                <p><label>概要メモ<input type="text" name="description"></label></p>
                <button type="submit">作成する</button>
            </div>
        </form>
    </main>
    <footer>
    </footer>
</body>
</html>