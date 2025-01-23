<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;//追加
use Illuminate\Support\Facades\Validator;//バリデーション追加
use Illuminate\Support\Facades\Auth;//ユーザー情報
use App\Models\User;
use App\Models\CandidateDate;
use App\Models\DateVote;

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
        $rules = [
            'title' => 'required',
        ];
        $messages = [
            'required' => '必須項目です',
        ];
        Validator::make($request->all(),$rules,$messages)->validate();

        $trip = new Trip;//モデルをインスタンス化
        $trip->title = $request->input('title');//モデル->カラム名=値で、データを割り当てる
        $trip->description = $request->input('description');//上記と同様
        $trip->creator_id = Auth::id();//現在認証しているユーザーのIDを取得
        $trip->save();//データベースに保存
        return redirect()->route('trips.show', ['trip' => $trip->id])->with('success', '旅行計画が作成されました！');//リダイレクト
    }

    /**
     * Display the specified resource.
     */
    public function show(Trip $trip)
    {
        // 候補日を取得
        $candidateDates = CandidateDate::where('trip_id', $trip->id)
            ->orderBy('proposed_date')
            ->get();
    
        // この旅行に関連するすべてのユーザーを取得
        $userIds = collect();
        
        // DateVotesからユーザーIDを取得
        $voteUserIds = DateVote::where('trip_id', $trip->id)
            ->pluck('user_id');
        $userIds = $userIds->concat($voteUserIds);
    
        // CandidateDatesからユーザーIDを取得
        $candidateUserIds = CandidateDate::where('trip_id', $trip->id)
            ->pluck('user_id');
        $userIds = $userIds->concat($candidateUserIds);
    
        // 重複を除去してユーザーを取得
        $users = User::whereIn('id', $userIds->unique())->get();
    
        // 投票データを取得
        $dateVotes = DateVote::where('trip_id', $trip->id)->get();
    
        return view('trips.eachplanning', compact(
            'trip',
            'candidateDates',
            'users',
            'dateVotes'
        ));
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
    public function update(Request $request, string $id)
    {
        //
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
}
