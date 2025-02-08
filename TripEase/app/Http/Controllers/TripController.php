<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;//追加
use Illuminate\Support\Facades\Validator;//バリデーション追加
use Illuminate\Support\Facades\Auth;//ユーザー情報
use Illuminate\Support\Facades\DB; // DBファサードをインポート
use App\Models\User;
use App\Models\CandidateDate;
use App\Models\DateVote;
use App\Models\TripRequest;
use Illuminate\Support\Str;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trips = Trip::all();
        return view('trips.dashboard', compact('trips'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        return view('trips.tripplanning', ['user' => $user]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // トランザクション開始
        DB::beginTransaction();
        try {
            // 旅行プランの作成
            $trip = Trip::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'creator_id' => Auth::id(),
            ]);

            // 作成者を参加者としても追加（必要な場合）
            $trip->users()->attach(Auth::id());

            DB::commit();
            return redirect()->route('trips.show', $trip)
                           ->with('success', '旅行プランを作成しました。');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', '旅行プランの作成に失敗しました。')
                        ->withInput();
        }
    }

    public function storeRequest(Request $request, Trip $trip)
    {
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);
    
        $tripRequest = new TripRequest([
            'trip_id' => $trip->id,
            'user_id' => Auth::id(),
            'content' => $request->content
        ]);
    
        $tripRequest->save();
    
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Trip $trip)
    {
        // ユーザーがこの旅行に参加しているか確認
        if (!$trip->users->contains(auth()->id()) && $trip->creator_id !== auth()->id()) {
            return redirect()->route('trips.index')
                ->with('error', 'この旅行計画にアクセスする権限がありません。');
        }
    
        // 作成者と参加者を結合して一意のユーザーリストを作成
        $allUsers = collect([$trip->creator])->concat($trip->users)->unique('id');
    
        // 要望（trip_requests）とそれに関連するコメントといいねを取得
        $userRequests = $trip->tripRequests()
            ->with(['user', 'comments.user', 'likes.user'])
            ->get();
    
        // 候補日とその投票を取得
        $candidateDates = $trip->candidateDates()
            ->with(['user', 'dateVotes.user'])
            ->orderBy('proposed_date')
            ->get();
    
        // 日程の投票を取得
        $dateVotes = DateVote::where('trip_id', $trip->id)
            ->with(['user', 'candidateDate'])
            ->get();
    
        return view('trips.eachplanning', [
            'trip' => $trip,
            'users' => $allUsers,  // 作成者と参加者を含む全ユーザー
            'user' => auth()->user(),
            'userRequests' => $userRequests ?? collect(),
            'candidateDates' => $candidateDates ?? collect(),
            'dateVotes' => $dateVotes ?? collect(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trip $trip)
    {
        // 権限チェック
        if (!$trip->users->contains(auth()->id()) && $trip->creator_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'この旅行計画を編集する権限がありません。'
            ], 403);
        }
    
        // バリデーション
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
    
        // データ更新
        $trip->update($validated);
    
        return response()->json([
            'success' => true,
            'message' => '更新しました'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function voteDateJudgement(Request $request, Trip $trip)
    {
        \Log::info('Request all:', $request->all());

        try {
            $validated = $request->validate([
                'date_id' => 'required|exists:candidate_dates,id',  // テーブル名を修正
                'judgement' => 'required|in:〇,△,×'
            ]);

            $vote = DateVote::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'trip_id' => $trip->id,  // trip_idも追加
                    'date_id' => $validated['date_id']
                ],
                [
                    'judgement' => $validated['judgement']
                ]
            );

            return response()->json([
                'success' => true,
                'message' => '判定を保存しました。',
                'data' => $vote
            ]);

        } catch (ValidationException $e) {
            \Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showJoinConfirmation($token)
    {
        $trip = Trip::where('share_token', $token)->firstOrFail();
    
        // 未ログインの場合、現在のURLをセッションに保存
        if (!auth()->check()) {
            session(['url.intended' => request()->fullUrl()]);
        }
    
        return view('trips.join-confirmation', compact('trip'));
    }
    
    public function joinByToken($token)
    {
        $trip = Trip::where('share_token', $token)->firstOrFail();
        
        // 既に参加している場合
        if ($trip->users->contains(auth()->id())) {
            return redirect()->route('trips.each.planning', ['trip' => $trip->id])
                ->with('info', 'すでにこの旅行計画に参加しています');
        }
        
        // 参加処理
        $trip->users()->attach(auth()->id());
        
        return redirect()->route('trips.each.planning', ['trip' => $trip->id])
            ->with('success', '旅行計画に参加しました！');
    }

    // 共有リンクを生成するメソッド
    public function generateShareLink(Trip $trip)
    {
        if (!$trip->share_token) {
            $trip->generateShareToken();
        }
        
        return response()->json([
            'share_url' => route('trips.join', $trip->share_token)
        ]);
    }

    public function eachPlanning(Trip $trip)
    {
        // ユーザーがこの旅行に参加しているか確認
        if (!$trip->users->contains(auth()->id()) && $trip->creator_id !== auth()->id()) {
            return redirect()->route('trips.index')
                ->with('error', 'この旅行計画にアクセスする権限がありません。');
        }

        // 作成者と参加者を結合して一意のユーザーリストを作成
        $allUsers = collect([$trip->creator])->concat($trip->users)->unique('id');

        // 要望（trip_requests）とそれに関連するコメントといいねを取得
        $userRequests = $trip->tripRequests()
            ->with(['user', 'comments.user', 'likes.user'])
            ->get();

        // 候補日とその投票を取得
        $candidateDates = $trip->candidateDates()
            ->with(['user', 'dateVotes.user'])
            ->orderBy('proposed_date')
            ->get();

        // 日程の投票を取得
        $dateVotes = DateVote::where('trip_id', $trip->id)
            ->with(['user', 'candidateDate'])
            ->get();

        return view('trips.eachplanning', [
            'trip' => $trip,
            'users' => $allUsers,  // 修正：作成者と参加者を含む全ユーザー
            'user' => auth()->user(),
            'userRequests' => $userRequests ?? collect(),
            'candidateDates' => $candidateDates ?? collect(),
            'dateVotes' => $dateVotes ?? collect(),
        ]);
    }

    /**
     * 参加中の旅行計画一覧を表示
     */
    public function participating()
    {
        try {
            \Log::info('Participating method called'); // デバッグ用
            
            // ユーザーの取得を確認
            $user = auth()->user();
            if (!$user) {
                \Log::error('User not authenticated');
                return redirect()->route('login');
            }
            \Log::info('User found', ['id' => $user->id]);
    
            // リレーションのロードを確認
            try {
                $user->load(['trips' => function($query) {
                    $query->orderBy('updated_at', 'desc');
                }]);
                \Log::info('Trips loaded', ['count' => $user->trips->count()]);
            } catch (\Exception $e) {
                \Log::error('Error loading trips: ' . $e->getMessage());
                throw $e;
            }
    
            // ビューの存在確認
            if (!view()->exists('trips.participating')) {
                \Log::error('View trips.participating does not exist');
                throw new \Exception('View not found');
            }
            
            return view('trips.participating', compact('user'));
        } catch (\Exception $e) {
            \Log::error('Error in participating method: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            // 開発環境でのみ詳細なエラーを表示
            if (config('app.debug')) {
                throw $e;
            }
            
            return response()->view('errors.500', [], 500);
        }
    }

    public function updateDates(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'confirmed_start_date' => 'required|date',
            'confirmed_end_date' => 'required|date|after_or_equal:confirmed_start_date',
        ]);
    
        // 日帰り旅行の場合は、end_dateをstart_dateと同じ値に設定
        if ($request->has('isDayTrip') && $request->isDayTrip === 'on') {
            $validated['confirmed_end_date'] = $validated['confirmed_start_date'];
        }
    
        $trip->update($validated);
    
        return redirect()->back()->with('success', '日程を更新しました');
    }
}
