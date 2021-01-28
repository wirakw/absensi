<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Psikolog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PsikologController extends Controller
{
    /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * chat.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $queryStrings = $request->except('limit', 'topic_id', 'order_by', 'order', 'page', 'count', 'current_page', 'last_page', 'next_page_url', 'per_page', 'previous_page_url', 'total', 'url', 'from', 'to');

        $limit = ($request->get('limit') ? $request->get('limit') : '10');
        $order_by = ($request->get('order') ? 'users' . $request->get('order') : 'users.created_at');
        $order = ($request->get('order_by') ? $request->get('order_by') : 'desc');
        $page = ($request->get('page') ? $request->get('page') : '1');
        $topic_id = $request->get("topic_id");
        $id = $request->get("id");
        $approve = $request->get("is_approve");
        $is_approve = true;
        if ((isset($approve)) && ($approve == 'true')) {
            $is_approve == true;
        } else if ((isset($approve)) && ($approve == 'false')) {
            $is_approve = false;
        } else {
            $is_approve == true;
        }

        if (isset($id)) {
            $data = User::select('users.*', 'psikolog.tarif')->where('users.id', $id)->where('users.role', 2)
                ->leftJoin('psikolog_topic', 'psikolog_topic.user_id', '=', 'users.id')
                ->leftJoin('psikolog', 'psikolog.user_id', '=', 'users.id')->first();
            if (!isset($data)) {
                return response()->json([
                    "success" => false,
                    "message" => "psikolog tidak ditemukan",
                ], 200);
            }
            return response()->json([
                "success" => true,
                "message" => "success",
                "data" => $data,
            ], 200);
        }

        if ($limit >= 100) {
            $limit = 100;
        }
        $query = User::select('users.*', 'psikolog.tarif')->where('users.role', 2)
            ->leftJoin('psikolog_topic', 'psikolog_topic.user_id', '=', 'users.id')
            ->leftJoin('psikolog', 'psikolog.user_id', '=', 'users.id');
        // $query->where('users.email', 'azm@khan.com');
        // ->leftJoin('topics', 'topics.id', '=', 'psikolog_topic.topic_id');

        foreach ($queryStrings as $key => $value) {
            $query->where($key, '=', $value);
        }
        if (isset($topic_id)) {
            $query->where('psikolog_topic.topic_id', $topic_id);
        }
        if (isset($is_approve)) {
            $query->where('psikolog.is_approve', $is_approve);
        }

        $query->orderBy($order_by, $order);
        // $query->simplePaginate($limit);
        // $data = array();
        $data = $query->simplePaginate($limit);
        // $data = $query->get();

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $data,
        ], 200);
        // return response()->json($data);
    }

    /**
     * psikolog.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function approvePsikolog(Request $request)
    {
        $user = Auth::user();
        if ($user->role == 3) {
            $this->validate($request, [
                'psikolog_id' => 'required',
            ]);
            $data = [];
            $data['user_id'] = $request->input("psikolog_id");
            $data['is_approve'] = true;
            $recent = Psikolog::where('user_id', $data['user_id'])->update($data);

            return response()->json([
                "success" => true,
                "message" => 'success approve psikolog',
            ], 201);
        } else {
            return response()->json([
                "success" => false,
                "message" => 'tidak ada otoritas',
            ], 200);
        }
    }
}
