<?php

namespace App\Http\Controllers\Api;

use App\Events\ChatEvent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatRoom;
use App\Models\Topic;
use App\Models\ChatRoomMember;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\ServiceAccount;
use Morrislaptop\Firestore\Factory;

class ChatController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getParent', 'acceptConsultation', 'startSession']]);
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
        $chat_room_id = $request->get("chat_room_id");
        $chats = Chat::where('chat_room_id', $chat_room_id)->orderBy('created_at', 'asc')->get();
        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $chats,
        ], 200);
    }

    /**
     * chat.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChatRoom(Request $request)
    {
        $user = Auth::user();
        $id = $request->get("id");
        $status = $request->get("status");
        if (!isset($status)) {
            $status = true;
        }
        if (isset($id)) {
            $chatRoom = ChatRoom::select('chat_rooms.*')->where('chat_room_members.user_id', $user->id)
                ->whereNotNull('chat_room_members.joined_at')
                ->where('chat_rooms.id', $id)
                ->leftJoin('chat_room_members', 'chat_room_members.chat_room_id', '=', 'chat_rooms.id')->first();
            if (!isset($chatRoom)) {
                return response()->json([
                    "success" => false,
                    "message" => "data tidak ditemukan",
                    "data" => $chatRoom,
                ], 200);
            }
            return response()->json([
                "success" => true,
                "message" => "success",
                "data" => $chatRoom,
            ], 200);
        }

        $chatRoom = ChatRoom::select('chat_rooms.*')
            ->where('chat_room_members.user_id', $user->id)
            ->where('chat_rooms.status', $status)
            ->whereNotNull('chat_room_members.joined_at')
            ->leftJoin('chat_room_members', 'chat_room_members.chat_room_id', '=', 'chat_rooms.id')->get();

        if (!isset($chatRoom)) {
            return response()->json([
                "success" => false,
                "message" => "data tidak ditemukan",
                "data" => $chatRoom,
            ], 200);
        }
        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $chatRoom,
        ], 200);
    }

    public function startSession(Request $request)
    {
        $serviceAccount = ServiceAccount::fromJsonFile(base_path() . '/fcmtest-626e5-firebase-adminsdk-zsjsa-6970eee312.json');

        $this->validate($request, [
            'client_id' => 'required',
            'psikolog_id' => 'required',
        ]);
        $input = $request->all();
        $client = User::where('id', $input['client_id'])->first();
        // $psikolog = User::where('id', $input['psikolog_id'])->first();
        // $chatRoom = ChatRoom::select('chat_rooms.*')->whereIn('chat_room_members.user_id', [$input['client_id'], $input['psikolog_id']])
        // // ->where('chat_rooms.status', '!=', null)
        //     ->leftJoin('chat_room_members', 'chat_room_members.chat_room_id', '=', 'chat_rooms.id')->first();
        //     // echo json_encode($chatRoom);
        // if (!isset($chatRoom)) {
        $chatRoom = ChatRoom::create([
            "name" => substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(32))), 0, 32),
            "last_conversation_at" => date('Y-m-d H:i:s'),
            "status" => false,
        ]);

        $psikologJoin = ChatRoomMember::create([
            "chat_room_id" => $chatRoom->id,
            "user_id" => $input['psikolog_id'],
        ]);

        $clientJoin = ChatRoomMember::create([
            "chat_room_id" => $chatRoom->id,
            "user_id" => $input['client_id'],
            "joined_at" => date('Y-m-d H:i:s'),
        ]);

        // $recipients = User::select('device_token')->whereIn('id', [$input['client_id'], $input['psikolog_id']])->get()->pluck()->toArray();
        //firestore
        $firestore = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->createFirestore();

        $collection = $firestore->collection('ROOM-DEVELOPMENT');
        $chatRoomFirestore = $collection->document($chatRoom->name);
        $objClient = json_decode(json_encode($client), false);
        // $objPsikolog = json_decode(json_encode($psikolog), false);
        // var_dump($b);die;
        // Save a document
        $chatRoomFirestore->set([
            'id' => $chatRoom->id,
            'client_id' => $client->id,
            // 'psikolog_id' => $psikolog_data->id,
            'name' => $chatRoom->name,
            'created_at' => $chatRoom->created_at,
            'user_data' => $objClient,
            // 'psikolog_data' => $objPsikolog,
        ]);

        $response = [
            "success" => true,
            "message" => "success startSession",
            "data" => $chatRoom,
        ];

        return response()->json($response, 201);
        // }

        // fcm()
        // ->to([$client->device_token]) // $recipients must an array
        // ->priority('high')
        // ->timeToLive(60)
        // ->data([
        //     'title' => 'pembayaran',
        //     'body' => 'lunasi pembayaran',
        //     'chat_room_id' => $chatRoom->id,
        // ])
        // ->notification([
        //     'title' => 'permintaan konsultasi',
        //     'body' => 'permintaan konsultasi dari ' . $client->name,
        // ])
        // ->send();

        // $response = [
        //     "success" => true,
        //     "message" => "success startSession",
        //     "data" => $chatRoom,
        // ];

        // return response()->json($response, 201);
    }

    public function acceptConsultation(Request $request)
    {
        $serviceAccount = ServiceAccount::fromJsonFile(base_path() . '/fcmtest-626e5-firebase-adminsdk-zsjsa-6970eee312.json');

        $this->validate($request, [
            'chat_room_id' => 'required',
            'psikolog_id' => 'required',
            'client_id' => 'required',
        ]);
        $input = $request->all();

        $psikologJoin = ChatRoomMember::where('chat_room_id', $input['chat_room_id'])
            ->where('user_id', $input['psikolog_id'])
            ->update([
                "joined_at" => date('Y-m-d H:i:s'),
            ]);

        $updateStatusChatRoom = ChatRoom::where('id', $input['chat_room_id'])->update([
            "status" => true,
        ]);

        $chatRoom = ChatRoom::where('id', $input['chat_room_id'])->first();

        $psikolog = User::where('id', $input['psikolog_id'])->first();
        $client = User::where('id', $input['client_id'])->first();
        //firestore
        $firestore = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->createFirestore();

        $collection = $firestore->collection('ROOM-DEVELOPMENT');
        $chatRoomFirestore = $collection->document($chatRoom->name);
        $objClient = json_decode(json_encode($client), false);
        $objPsikolog = json_decode(json_encode($psikolog), false);
        // Save a document
        $chatRoomFirestore->set([
            'id' => $chatRoom->id,
            'client_id' => $client->id,
            'psikolog_id' => $psikolog->id,
            'name' => $chatRoom->name,
            'created_at' => $chatRoom->created_at,
            'user_data' => $objClient,
            'psikolog_data' => $objPsikolog,
        ]);

        fcm()
            ->to([$psikolog->device_token]) // $recipients must an array
            ->priority('high')
            // ->timeToLive(60)
            ->data([
                'title' => 'permintaan konsultasi',
                'body' => 'permintaan konsultasi dari ' . $client->name,
                'chat_room_id' => $chatRoom->id,
            ])
            ->notification([
                'title' => 'permintaan konsultasi',
                'body' => 'permintaan konsultasi dari ' . $client->name,
            ])
            ->send();

        fcm()
            ->to([$client->device_token]) // $recipients must an array
            ->priority('high')
            // ->timeToLive(60)
            ->data([
                'title' => 'psikolog join room chat',
                'body' => 'psikolog ' . $client->name . '  join room chat',
                'chat_room_id' => $chatRoom->id,
            ])
            ->notification([
                'title' => 'psikolog join room chat',
                'body' => 'psikolog ' . $client->name . '  join room chat',
            ])
            ->send();

        $response = [
            "success" => true,
            "message" => "sukses create konsultasi",
            "data" => $chatRoom,
        ];

        return response()->json($response, 200);
    }

    /**
     * chat.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'chat_room_id' => 'required',
            'user_id' => 'required',
            'message' => 'required',
        ]);
        $input = $request->all();

        $create = Chat::create($input);

        $data = ChatRoomMember::select('users.*')->where('chat_room_members.chat_room_id', $input['chat_room_id'])
            ->where('chat_room_members.user_id', '!=', $input['user_id'])
            ->leftJoin('users', 'users.id', '=', 'chat_room_members.user_id')->first();

        $recipients = [$data->token];

        fcm()
            ->to($recipients) // $recipients must an array
            ->priority('high')
            ->timeToLive(0)
            ->data([
                'title' => 'New Message',
                'body' => $input['message'],
            ])
            ->notification([
                'title' => 'New Message',
                'body' => substr($input['message'], 0, 20),
            ])
            ->send();

        $message = $create;
        event(new ChatEvent($input['chat_room_id'], $message));
        $response = [
            "success" => true,
            "message" => "pesan terkirim",
        ];
        return response()->json($response, 201);
    }

    public function endChat(Request $request)
    {
        $this->validate($request, [
            'chat_room_id' => 'required',
            // 'psikolog_id' => 'required',
            // 'client_id' => 'required',
        ]);
        $input = $request->all();
        $chatRoomMember = ChatRoomMember::where('chat_room_id', $input['chat_room_id'])->update(
            [
                "left_at" => date('Y-m-d H:i:s'),
            ]
        );

        // $delChatRoomMember = ChatRoomMember::where('chat_room_id', $input['chat_room_id'])->delete();
        $chatRoom = ChatRoom::where('id', $input['chat_room_id'])->update(
            [
                "status" => false,
            ]
        );
        $psikolog = User::where('id', $input['psikolog_id'])->first();
        $client = User::where('id', $input['client_id'])->first();

        $transaction = Transaction::where('chat_room_id', $input['chat_room_id'])->first();
        $topic = Topic::where('id', $transaction->topic_id)->first();

        fcm()
            ->to([$psikolog->device_token]) // $recipients must an array
            ->priority('high')
            // ->timeToLive(60)
            ->data([
                'title' => 'form diagnose',
                'body' => 'pengisian from diagnose ke pasien ' . $client->name,
                'type' => 'diagnose',
                'psikolog_id' => $psikolog->id,
                'psikolog_name' => $psikolog->name,
                'client_id' => $client->id,
                'chat_room_id' => $input['chat_room_id'],
                'topic' => $topic->topic_name,
            ])
            ->notification([
                'title' => 'form diagnose',
                'body' => 'pengisian from diagnose ke pasien ' . $client->name,
            ])
            ->send();

        fcm()
            ->to([$client->device_token]) // $recipients must an array
            ->priority('high')
            // ->timeToLive(60)
            ->data([
                'title' => 'Ayo isi feedback',
                'body' => 'form feedback atau riview',
                'type' => 'riview',
                'psikolog_id' => $psikolog->id,
                'psikolog_name' => $psikolog->name,
                'client_id' => $client->id,
                'chat_room_id' => $input['chat_room_id'],
                'topic' => $topic->topic_name,
            ])
            ->notification([
                'title' => 'Ayo isi feedback',
                'body' => 'form feedback atau riview',
            ])
            ->send();

        $response = [
            "success" => true,
            "message" => "sukses endchat",
        ];

        return response()->json($response, 200);
    }
}
