<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['resetAttendance']]);
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
            $data = Attendance::select('attendances.*', 'users.id as user_id', 'users.name', 'users.email', 'users.phone_number', 'users.role', 'users.photo')->where('users.id', $user->id)
                ->leftJoin('users', 'users.id', '=', 'attendances.user_id')->first();

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
        $query = Attendance::select('attendances.*', 'users.id as user_id', 'users.name', 'users.email', 'users.phone_number', 'users.role', 'users.photo')->where('users.id', $user->id)
            ->leftJoin('users', 'users.id', '=', 'attendances.user_id');
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
     * attendance.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'latitude' => 'required',
            'longitude' => 'required',
            'attendance_photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:in,out',
        ]);
        $file = $request->file('attendance_photo');
        $filename = $user->id . '-' . time() . '.' . $file->getClientOriginalExtension();
        $file->move('app/user/attendance', $filename);
        $input = $request->all();
        if ((($input['status'] == 'in') && ($user->status_absen == 'not_yet')) || (($input['status'] == 'out') && ($user->status_absen == 'clock_in'))) {
            $input['user_id'] = $user->id;
            $input['attendance_photo'] = $filename;

            $create = Attendance::create($input);
            if ($create) {
                $status_absen = 'clock_in';
                if ($input['status'] == 'out') {
                    $status_absen = 'clock_out';
                }
                $updateUser = User::where('id', $user->id)->update([
                    'status_absen' => $status_absen,
                ]);
                return response()->json([
                    "success" => true,
                    "message" => 'success attendance',
                    "data" => $create,
                ], 201);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "gagal attendance",
                ], 200);
            }
        } else {
            if ($user->status_absen == 'not_yet') {
                return response()->json([
                    "success" => false,
                    "message" => "anda belum clock in hari ini",
                ], 200);
            } else if ($user->status_absen == 'clock_in') {
                return response()->json([
                    "success" => false,
                    "message" => "anda sudah clock in hari ini",
                ], 200);
            } else if ($user->status_absen == 'clock_out') {
                return response()->json([
                    "success" => false,
                    "message" => "anda sudah clock out hari ini",
                ], 200);
            }
        }
    }

    /**
     * attendance.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetAttendance(Request $request)
    {
        $updateUser = User::where('status_absen', 'clock_out')->update([
            'status_absen' => 'not_yet',
        ]);
        return response()->json([
            "success" => true,
            "message" => 'success reset attendance',
        ], 200);
    }
}
