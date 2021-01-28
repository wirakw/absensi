<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Mood;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use DB;

class MoodController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index']]);
    }

    /**
     * chat.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $id = $request->get("id");

        if (isset($id)) {
            $mood = Mood::where('id', $id)->first();
            if (!isset($mood)) {
                return response()->json([
                    "success" => false,
                    "message" => "tidak ada data",
                ], 200);
            }

            return response()->json([
                "success" => true,
                "message" => "success",
                "data" => $mood,
            ], 200);
        }

        $moods = Mood::get();

        if (!isset($moods)) {
            return response()->json([
                "success" => false,
                "message" => "tidak ada data",
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $moods,
        ], 200);
    }

    /**
     * merchant.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postMood(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'mood_id' => 'required',
        ]);
        $data = [];
        $data['mood_id'] = $request->input("mood_id");
        $data['user_id'] = $user->id;
        $recent = DB::table('mood_user')->insertOrIgnore($data);

        return response()->json([
            "success" => true,
            "message" => 'success add user moood',
        ], 201);
    }
}
