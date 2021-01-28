<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ScheduleController extends Controller
{
     /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
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
            $data = Schedule::where('id', $id)->first();
            if (!isset($data)) {
                return response()->json([
                    "success" => false,
                    "message" => "data tidak ditemukan",
                ], 200);
            }
            return response()->json([
                "success" => true,
                "message" => "success",
                "data" => $data,
            ], 200);
        }

        $datas = Schedule::get();
        
        if (!isset($datas)) {
            return response()->json([
                "success" => false,
                "message" => "data tidak ditemukan",
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $datas,
        ], 200);
    }
}