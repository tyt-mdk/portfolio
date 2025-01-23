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
    <main class="flex-1 pb-20">
        <p>{{ $trip->title }}のノート</p>
        <a href=""><p class="">編集</p></a>
        <p>{{ $trip->description }}</p>
        <a href=""><p class="">編集</p></a>

        <!-- 候補日一覧テーブル -->
        @if(isset($candidateDates) && $candidateDates->count() > 0)
            <div class="overflow-x-auto mb-8">
                <table class="w-full min-w-[600px] border-collapse">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="p-2 border-b border-slate-200 text-left">参加者</th>
                            @foreach($candidateDates->sortBy('proposed_date')->unique('proposed_date') as $date)
                                <th class="p-2 border-b border-slate-200 text-center whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($date->proposed_date)->format('n/j') }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr class="hover:bg-slate-50">
                                <td class="p-2 border-b border-slate-200">
                                    {{ $user->name }}
                                </td>
                                @foreach($candidateDates->sortBy('proposed_date')->unique('proposed_date') as $date)
                                    <td class="p-2 border-b border-slate-200 text-center">
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
            <p class="text-slate-500 text-center my-4">候補日がまだ登録されていません</p>
        @endif

        <a href="{{ route('trips.schedule', $trip->id) }}"><p class="">日程調整する</p></a>
    </main>
    <footer class="fixed bottom-0 left-0 right-0 bg-slate-50">
        <div class="flex justify-around text-center h-20 text-sm">
            <!-- 戻るボタン -->
            <a href="javascript:void(0)" onclick="history.back()" class="absolute left-4 top-1/2 -translate-y-1/2">
                <i class="fa-solid fa-chevron-left"></i>
                <p>戻る</p>
            </a>
        </div>
    </footer>
</body>
</html>