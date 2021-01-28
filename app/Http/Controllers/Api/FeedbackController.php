<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FeedbackController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * article.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $id = $request->get("id");
        $psikolog_id = $request->get("psikolog_id");

        $user = Auth::user();
        if (isset($id)) {
            $feedback = Feedback::where('id', $id)->first();
            if (!isset($feedback)) {
                return response()->json([
                    "success" => false,
                    "message" => "tidak ada data",
                ], 200);
            }

            return response()->json([
                "success" => true,
                "message" => "success",
                "data" => $feedback,   
            ], 200);
        }

        $feedback = Feedback::where('psikolog_id', $psikolog_id)->get();

        if (!isset($feedback)) {
            return response()->json([
                "success" => false,
                "message" => "tidak ada data",
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $feedback,
        ], 200);
    }

    public function postFeedback(Request $request)
    {
        // $user = Auth::user();
        $this->validate($request, [
            'user_id' => 'required',
            'psikolog_id' => 'required',
            'chat_room_id' => 'required',
            'rate' => 'required',
            'message' => 'required',
        ]);

        $input = $request->all();

        $feedback = Feedback::create($input);

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $feedback,
        ], 200);
    }

}
