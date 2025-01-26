<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;//追加
use Illuminate\Support\Facades\Validator;//バリデーション追加
use Illuminate\Support\Facades\Auth;//ユーザー情報
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
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            // 他のバリデーションルール...
        ]);
    
        // トランザクション開始
        DB::beginTransaction();
        
        try {
            // 旅行プランを作成
            $trip = Trip::create([
                'title' => $validatedData['title'],
                'creator_id' => auth()->id(),
                // 他のフィールド...
            ]);
    
            // 作成者を参加者として追加
            $trip->users()->attach(auth()->id());
    
            DB::commit();
            
            return redirect()->route('trips.show', $trip)
                ->with('success', '旅行プランを作成しました');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Trip creation failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', '旅行プランの作成に失敗しました');
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
            return redirect()->route('trips.index')
                ->with('error', 'この旅行計画を編集する権限がありません。');
        }
    
        // バリデーション
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
    
        // データ更新
        $trip->update($validated);
    
        // リダイレクト（同じページに戻る）
        return redirect()->route('trips.show', $trip)
            ->with('success', '旅行計画が更新されました。');
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
}
