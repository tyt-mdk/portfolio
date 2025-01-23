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
</body>
</html>