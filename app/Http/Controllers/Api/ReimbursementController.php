<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use App\Models\ReimbursementDetail;
use App\Models\ReimbursementPhoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReimbursementController extends Controller
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
     * reimbursement.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $queryStrings = $request->except('limit', 'order_by', 'order', 'page', 'count', 'current_page', 'last_page', 'next_page_url', 'per_page', 'previous_page_url', 'total', 'url', 'from', 'to');

        $limit = ($request->get('limit') ? $request->get('limit') : '10');
        $order_by = ($request->get('order') ? 'users' . $request->get('order') : 'users.created_at');
        $order = ($request->get('order_by') ? $request->get('order_by') : 'desc');
        $page = ($request->get('page') ? $request->get('page') : '1');
        $id = $request->get("id");

        if (isset($id)) {
            $data = Reimbursement::select('reimbursements.*', 'users.id as user_id', 'users.name', 'users.email', 'users.phone_number', 'users.role', 'users.photo')->where('users.id', $user->id)
                ->where('reimbursements.id', $id)
                ->leftJoin('users', 'users.id', '=', 'reimbursements.user_id')->first();
            if (!isset($data)) {
                return response()->json([
                    "success" => false,
                    "message" => "data tidak ditemukan",
                ], 200);
            }

            if (!isset($data->photo)) {
                $data->photo = 'default.jpg';
            }
            $data->photo_url = url('app/user/' . $data->photo);

            return response()->json([
                "success" => true,
                "message" => "success",
                "data" => $data,
            ], 200);
        }

        if ($limit >= 100) {
            $limit = 100;
        }
        $query = Reimbursement::select('reimbursements.*', 'users.id as user_id', 'users.name', 'users.email', 'users.phone_number', 'users.role', 'users.photo')->where('users.id', $user->id)
            ->leftJoin('users', 'users.id', '=', 'reimbursements.user_id');
        foreach ($queryStrings as $key => $value) {
            $query->where($key, '=', $value);
        }

        $query->orderBy($order_by, $order);
        $datas = $query->simplePaginate($limit);
        foreach ($datas as &$data) {
            if (!isset($data->photo)) {
                $data->photo = 'default.jpg';
            }
            $data->photo_url = url('app/user/' . $data->photo);
        }
        return response()->json($datas);
    }

    /**
     * reimbursement.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'reimbursement_date' => 'required',
            'reimbursement_type_id' => 'required',
            'description' => 'required',
        ]);
        $input = $request->all();
        $input['user_id'] = $user->id;
        $create = Reimbursement::create($input);
        if ($create) {
            foreach ($input['items'] as $item) {
                ReimbursementDetail::insert(
                    [
                        'reimbursement_id' => $create['id'],
                        'reimbursement_item_id' => $item['reimbursement_item_id'],
                        'pengajuan' => $item['pengajuan'],
                    ]);
            }
            $create->items = $input['items'];
            return response()->json([
                "success" => true,
                "message" => 'success reimbursement',
                "data" => $create,
            ], 201);
        } else {
            return response()->json([
                "success" => false,
                "message" => "gagal reimbursement",
            ], 200);
        }
    }

    /**
     * reimbursement.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addPhoto(Request $request)
    {
        $user = Auth::user();
        $post = $request->all();
        $input = json_decode($post['request'], true);
        $input['user_id'] = $user->id;
        $create = Reimbursement::create($input);
        if ($create) {
            if ($files = $request->file('images')) {
                foreach ($files as $file) {
                    $name = $create['id'] . '-' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move('app/user/reimbursement', $name);

                    ReimbursementPhoto::insert(
                        [
                            'reimbursement_id' => $create['id'],
                            'reimbursement_photo' => $name,
                        ]);
                }
            }

            foreach ($input['items'] as $item) {
                ReimbursementDetail::insert(
                    [
                        'reimbursement_id' => $create['id'],
                        'reimbursement_item_id' => $item['reimbursement_item_id'],
                        'pengajuan' => $item['pengajuan'],
                    ]);
            }
            $create->items = $input['items'];
            return response()->json([
                "success" => true,
                "message" => 'success reimbursement',
                "data" => $create,
            ], 201);
        }

        return response()->json([
            "success" => false,
            "message" => "gagal reimbursement",
        ], 200);

    }
}
