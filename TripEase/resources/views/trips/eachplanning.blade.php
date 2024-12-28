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
<body>
    <header>
    </header>
    <main>
        <p>{{ $trip->title }}のノート</p>
        <a href=""><p class="">編集</p></a>
        <p>{{ $trip->description }}</p>
        <a href=""><p class="">編集</p></a>
        <p>{{ $trip->start_date }}～{{ $trip->end_date }}</p>
        <a href="{{ route('schedule.show', $trip->id) }}"><p class="">日程調整する</p></a>
    </main>
    <footer>
    </footer>
</body>
</html>