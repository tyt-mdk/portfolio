<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100">
    <div class="min-h-screen">
        <!-- メインコンテンツ -->
        <main class="pb-24">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- ヘッダー部分 -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">参加中の旅行計画一覧</h2>
                    <!-- ユーザー名を右上に配置 -->
                    <div class="text-sm text-gray-600">
                        {{ Auth::user()->name }}さん
                    </div>
                </div>

                <!-- 旅行計画一覧 -->
                @if ($user->trips->isNotEmpty())
                    <div class="mx-4 divide-y divide-dashed"> <!-- divideクラスを追加 -->
                        @foreach($user->trips as $trip)
                            <div class="group py-3"> <!-- 上下のpaddingを追加 -->
                                <a href="{{ route('trips.show', ['trip' => $trip->id]) }}" 
                                   class="block px-4 py-4 rounded-lg hover:bg-white hover:shadow-sm transition-all duration-200">
                                    <div class="space-y-3">
                                        <!-- ヘッダー部分 -->
                                        <div class="flex justify-between items-start">
                                            <div class="space-y-1">
                                                <h3 class="text-lg font-semibold text-gray-900 group-hover:text-sky-600 transition-colors">
                                                    {{ $trip->title }}
                                                </h3>
                                                <p class="text-sm text-gray-600">
                                                    {{ $trip->description }}
                                                </p>
                                            </div>
                                            <div class="text-gray-400 group-hover:text-sky-600 transition-colors">
                                                <i class="fa-solid fa-chevron-right"></i>
                                            </div>
                                        </div>

                                        <!-- メタ情報 -->
                                        <div class="flex items-center gap-4 text-sm text-gray-600">
                                            <div class="flex items-center gap-1">
                                                <i class="fa-solid fa-users"></i>
                                                <span>{{ $trip->users->count() }}人</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <i class="fa-regular fa-clock"></i>
                                                <span>{{ $trip->updated_at->format('Y/m/d') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-lg p-6 text-center text-gray-500 bg-white/50">
                        参加している旅行プランはありません
                    </div>
                @endif
            </div>
        </main>

        <!-- フッター -->
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
                    <!-- 中央の空のスペース -->
                    <div class="justify-self-center"></div>
                    <!-- 右側の空のスペース -->
                    <div class="justify-self-end"></div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>