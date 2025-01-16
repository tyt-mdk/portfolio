<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CandidateDate;
use App\Models\DateVote;
use App\Models\Trip;

class ScheduleController extends Controller
{
    public function showSchedule($tripId)
    {
        $trip = Trip::findOrFail($tripId);
        $candidateDates = CandidateDate::with('datevotes')->where('trip_id', $tripId)->get();

        return view('trips.dateplanning', [
            'trip' => $trip,
            'candidateDates' => $candidateDates
        ]);
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

    public function voteDate(Request $request, $tripId)
    {
        $request->validate([
            'date_id' => 'required|exists:dates,id',
            'judgement' => 'required|in:〇,△,×'
        ]);

        DateVote::updateOrCreate(
            [
                'date_id' => $request->date_id,
                'user_id' => auth()->id(),
            ],
            ['judgement' => $request->judgement]
        );

        return redirect()->route('schedule.show', $tripId);
    }

    public function finalizeSchedule(Request $request, $tripId)
    {
        // 確定処理（例: 最も支持された日を取得するなど）
        return redirect()->route('schedule.show', $tripId)->with('message', 'スケジュールが確定しました。');
    }
}