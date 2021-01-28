<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use \Firebase\JWT\JWT;

class MeetingController extends Controller
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
     * chat.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!isset($user)) {
            return response()->json([
                "success" => false,
                "message" => "user tidak ditemukan",
            ], 200);
        }

        $id = $request->get("id");

        if (isset($id)) {
            $data = Meeting::orderBy('created_at', 'asc')
                ->where('to', $user->id)
                ->where('id', $id)->first();

            $meta = json_decode($data->meta, true);
            $data->meta = $meta;

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

        $datas = Meeting::orderBy('created_at', 'asc')
            ->where('to', $user->id)->get();

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $datas,
        ], 200);
    }

    /**
     * meeting.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $base = config('services.meeting_base');
        $key = config('services.meeting_secret');

        $user = Auth::user();
        $this->validate($request, [
            'to' => 'required',
        ]);

        $input = $request->all();
        $input['from'] = $user->id;
        $room_id = $user->id . $input['to'] . Str::random(20);
        $user_invited = User::find($input['to']);
        $payload = array(
            "aud" => "diksiapp",
            "iss" => "diksiapp",
            "sub" => config('services.meeting_base'),
            "exp" => time() + (60 * 60),
            "room" => $room_id,
            "moderator" => true,
            "context" => array(
                "callee" => array(
                    "name" => $user->name,
                    "email" => $user->email,
                    "avatar" => "https://gravatar.com/avatar/abc123.png",
                ),
                "user" => array(
                    "name" => $user_invited->name,
                    "email" => $user_invited->email,
                    "avatar" => "https://gravatar.com/avatar/abc123.png",
                ),
            ),
        );
        $jwt = JWT::encode($payload, $key);

        $from = array(
            "aud" => "myapp",
            "iss" => "myapp",
            "sub" => "https://media.blueredsolutions.com",
            "exp" => time() + (60 * 60),
            "room" => $room_id,
            "moderator" => true,
            "context" => array(
                "user" => array(
                    "name" => $user->name,
                    "email" => $user->email,
                    "avatar" => "https://gravatar.com/avatar/abc123.png",
                ),
            ),
        );
        $jwtFrom = JWT::encode($from, $key);
        $input['link'] = $base . '/' . $room_id;
        $create = Meeting::create($input);
        $create->link = $base . '/' . $room_id . '?jwt=' . $jwtFrom;
        $toLink = $base . '/' . $room_id . '?jwt=' . $jwt;

        $recipients = [$user_invited->device_token];
        fcm()
        ->to($recipients) // $recipients must an array
        ->priority('high')
        ->timeToLive(0)
        ->data([
            'title' => $user->name,
            'body' => 'panggilan dari ' . $user->name,
            'link' => $toLink,
        ])
        ->notification([
            'title' => 'Test FCM',
            'body' => 'panggilan dari ' . $user->name,
        ])
        ->send();
        $response = [
            "success" => true,
            "message" => "berhasil membuat room meeting",
            "data" => $create,
        ];
        return response()->json($response, 201);
    }

    /**
     * meeting.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function join(Request $request, $meeting_id)
    {
        $user = Auth::user();
        $input['is_attende'] = true;
        $meetingInvite = Meeting::where('id', $meeting_id)->where('is_attende', false)->first();
        if (isset($meetingInvite)) {
            $update = Meeting::where('id', $input['meeting_id'])->update([
                "is_attende" => true,
            ]);
            $response = [
                "success" => true,
                "message" => "berhasil join room meeting",
                "data" => $meetingInvite,
            ];
            return response()->json($response, 201);
        } else {
            $response = [
                "success" => true,
                "message" => "call not found",
            ];
        }
    }
}
