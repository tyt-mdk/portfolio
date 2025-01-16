<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CandidateDate;
use App\Models\DateVote;

class CandidateDateController extends Controller
{
    public function getCandidateDates(Request $request)
    {
        $tripId = $request->query('trip_id');
        $dates = CandidateDate::where('trip_id', $tripId)
            ->with(['dateVotes' => function ($query) {
                $query->select('date_id', 'judgement');
            }])
            ->get();

        $events = $dates->map(function ($date) {
            return [
                'title' => '', // 必要に応じてタイトルを変更
                'start' => $date->date,
                'extendedProps' => [
                    'judgement' => $date->dateVotes->first()->judgement ?? ''
                ]
            ];
        });

        return response()->json($events);
    }

    public function setJudgement(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'judgement' => 'required|string'
        ]);

        $userId = auth()->id();
        $dateVote = DateVote::updateOrCreate(
            [
                'user_id' => $userId,
                'date_id' => CandidateDate::where('date', $validated['date'])->first()->id
            ],
            ['judgement' => $validated['judgement']]
        );

        return response()->json(['success' => true, 'data' => $dateVote]);
    }
}
