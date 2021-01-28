<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Diagnose;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DiagnoseController extends Controller
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

        $user = Auth::user();
        if (isset($id)) {
            $diagnose = Diagnose::where('id', $id)->first();
            if (!isset($diagnose)) {
                return response()->json([
                    "success" => false,
                    "message" => "tidak ada data",
                ], 200);
            }

            return response()->json([
                "success" => true,
                "message" => "success",
                "data" => $diagnose,   
            ], 200);
        }

        $diagnose = Diagnose::where('user_id', $user->id)->get();

        if (!isset($diagnose)) {
            return response()->json([
                "success" => false,
                "message" => "tidak ada data",
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $diagnose,
        ], 200);
    }

    public function postDiagnose(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'user_id' => 'required',
            'psikolog_id' => 'required',
            'diagnosa_1' => 'required',
            'chat_room_id' => 'required',
        ]);

        $input = $request->all();

        $diagnose = Diagnose::create($input);

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $diagnose,
        ], 200);
    }
}
