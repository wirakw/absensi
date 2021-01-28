<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Psikolog;
use App\Models\ChatRoom;
use App\Models\Topic;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ConsultationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'paymentNotification']]);
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

        if (isset($id)) {
            $consultation = Consultation::where('id', $id)->first();
            if (!isset($consultation)) {
                return response()->json([
                    "success" => false,
                    "message" => "tidak ada data",
                ], 200);
            }

            return response()->json([
                "success" => true,
                "message" => "success",
                "data" => $consultation,
            ], 200);
        }

        $consultations = Consultation::orderBy('consultation_name', 'asc')->get();

        if (!isset($consultations)) {
            return response()->json([
                "success" => false,
                "message" => "tidak ada data",
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $consultations,
        ], 200);
    }

    /**
     * chat.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopic(Request $request)
    {
        $id = $request->get("id");
        $consultation_id = $request->get("consultation_id");

        if (isset($id)) {
            $topic = Topic::where('id', $id)->first();
            if (!isset($topic)) {
                return response()->json([
                    "success" => false,
                    "message" => "tidak ada data",
                ], 200);
            }

            return response()->json([
                "success" => true,
                "message" => "success",
                "data" => $topic,
            ], 200);
        }

        $query = Topic::orderBy('topic_name', 'desc');
        if (isset($consultation_id)) {
            $query->where('consultation_id', $consultation_id);
        }
        $topic = $query->get();

        if (!isset($topic)) {
            return response()->json([
                "success" => false,
                "message" => "tidak ada data",
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $topic,
        ], 200);
    }


    public function paymentNotification(Request $request)
    {
        $input = $request->all();

        $meta = json_encode($input);
        $status_code = false;
        if (($input['transaction_status'] == 'capture') || ($input['transaction_status'] == 'settlement')) {
            $status_code = true;
        }

        $transaction = Transaction::where('no_transaction', $input['order_id'])->first();
        if ($transaction->status_bayar == 0) {
            $update = $transaction->update([
                'status_bayar' => $status_code,
                'meta' => $meta,
            ]);

            if ($update) {
                $client = new \GuzzleHttp\Client(["base_uri" => url()]);
                $options = [
                    'form_params' => [
                        "chat_room_id" => $transaction->chat_room_id,
                        "psikolog_id" => $transaction->psikolog_id,
                        "client_id" => $transaction->user_id,
                    ],
                ];
                $response = $client->post("/api/v1/acceptSession", $options);
                // echo $response->getBody();die;

                return response()->json([
                    "success" => true,
                    "message" => "success",
                    // "data" => $response->getBody(),
                ], 200);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "failed",
                ], 500);
            }
        } else {
            return response()->json([
                "success" => true,
                "message" => "success",
            ], 200);
        }
    }
}
