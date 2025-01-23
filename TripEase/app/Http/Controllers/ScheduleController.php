<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CandidateDate;
use App\Models\DateVote;
use App\Models\Trip;

class ScheduleController extends Controller
{
    public function showDatePlanning(Trip $trip)
    {
        $candidateDates = CandidateDate::select('candidate_dates.*', 'date_votes.judgement')
            ->leftJoin('date_votes', function($join) {
                $join->on('candidate_dates.id', '=', 'date_votes.date_id')
                    ->where('date_votes.user_id', '=', auth()->id());
            })
            ->where('candidate_dates.trip_id', $trip->id)  // テーブル名を明示的に指定
            ->get();

        return view('trips.dateplanning', compact('trip', 'candidateDates'));
    }

    public function addCandidateDate(Request $request, $tripId)
    {
        $request->validate(['proposed_date' => 'required|date']);

        CandidateDate::create([
            'user_id' => auth()->id(),
            'trip_id' => $tripId,
            'proposed_date' => $request->proposed_date,
        ]);

        return redirect()->route('schedule.show', $tripId);
    }

    public function voteDate(Request $request, Trip $trip)
    {
        \Log::info('Request all:', $request->all());
        \Log::info('Trip ID:', ['id' => $trip->id]); // デバッグ用

        try {
            $validated = $request->validate([
                'date_id' => 'required|exists:candidate_dates,id',
                'judgement' => 'required|in:〇,△,×'
            ]);

            // trip_idを明示的に設定
            $vote = DateVote::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'trip_id' => $trip->id,  // ここが重要
                    'date_id' => $validated['date_id']
                ],
                [
                    'judgement' => $validated['judgement']
                ]
            );

            \Log::info('Vote created:', $vote->toArray());

            return response()->json([
                'success' => true,
                'message' => '判定を保存しました。',
                'data' => $vote
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in voteDate:', [
                'message' => $e->getMessage(),
                'trip_id' => $trip->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    public function finalizeSchedule(Request $request, $tripId)
    {
        // 確定処理（例: 最も支持された日を取得するなど）
        return redirect()->route('schedule.show', $tripId)->with('message', 'スケジュールが確定しました。');
    }
}