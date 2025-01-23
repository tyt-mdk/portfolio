<?php

namespace App\Http\Controllers;

use App\Models\TripRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RequestComment;  // RequestCommentモデルをインポート

class TripRequestController extends Controller
{
    public function storeComment(Request $request, $requestId)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment = RequestComment::create([
            'trip_request_id' => $requestId,
            'user_id' => Auth::id(),
            'content' => $validated['content']
        ]);

        return back();
    }

    public function toggleLike(TripRequest $tripRequest)
    {
        $existing = $tripRequest->likes()
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            $tripRequest->likes()->create([
                'user_id' => Auth::id()
            ]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'count' => $tripRequest->likes()->count()
        ]);
    }
}