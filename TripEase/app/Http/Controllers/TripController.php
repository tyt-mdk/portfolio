<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;//追加
use Illuminate\Support\Facades\Validator;//バリデーション追加
use Illuminate\Support\Facades\Auth;//ユーザー情報

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
            'trip_title' => 'required',
        ];
        $messages = [
            'required' => '必須項目です',
        ];
        Validator::make($request->all(),$rules,$messages)->validate();

        $trip = new Trip;//モデルをインスタンス化
        $trip->trip_title = $request->input('trip_title');//モデル->カラム名=値で、データを割り当てる
        $trip->description = $request->input('description');//上記と同様
        $trip->creator_id = Auth::id();//現在認証しているユーザーのIDを取得
        $trip->start_date = 
        $trip->save();//データベースに保存
        return redirect()->route('trips.show', ['trip' => $trip->id])->with('success', '旅行計画が作成されました！');//リダイレクト
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $trip_id <- $id;
        $trip = Trip::findOrFail($trip_id); //該当IDがなければ404エラーを返す
        return view('trips.eachplanning', ['trip' => $trip]);
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
}
