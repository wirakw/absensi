<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Psikolog;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['paymentNotification']]);
    }

    public function transactionProduct(Request $request)
    {
        $user = Auth::user();
        if ($user->role == 3) {
            $queryStrings = $request->except('limit', 'user_id', 'order_by', 'order', 'page', 'count', 'current_page', 'last_page', 'next_page_url', 'per_page', 'previous_page_url', 'total', 'url', 'from', 'to');

            $limit = ($request->get('limit') ? $request->get('limit') : '10');
            $order_by = ($request->get('order') ? 'transaction_product' . $request->get('order') : 'transaction_product.id');
            $order = ($request->get('order_by') ? $request->get('order_by') : 'desc');
            $page = ($request->get('page') ? $request->get('page') : '1');

            $id = $request->get("id");
            // if (isset($user)) {
            //     $userId = $user->id;
            // } else {
            //     $userId = 0;
            // }

            if (isset($id)) {
                $query = TransactionProduct::select('transaction_product.*', 'users.name', 'products.product_name', 'products.gambar')
                    ->where('transaction_product.id', $id)
                    ->leftJoin('users', 'users.id', '=', 'transaction_product.user_id')
                    ->leftJoin('products', 'products.id', '=', 'transaction_product.product_id')->first();

                if (!isset($query)) {
                    return response()->json([
                        "success" => false,
                        "message" => "product tidak ditemukan",
                    ], 200);
                }
                $query->gambar_url = 'https://staging-merchant.dompetaman.com/assets/product-image/' . $query->gambar;

                return response()->json([
                    "success" => true,
                    "message" => "success",
                    "data" => $query,
                ], 200);
            }

            if ($limit >= 100) {
                $limit = 100;
            }

            $query = TransactionProduct::select('transaction_product.*', 'users.name', 'products.product_name', 'products.gambar')
            // ->where('user_id', $user->id)
                ->leftJoin('users', 'users.id', '=', 'transaction_product.user_id')
                ->leftJoin('products', 'products.id', '=', 'transaction_product.product_id');

            foreach ($queryStrings as $key => $value) {
                $query->where('transaction_product' . $key, '=', $value);
            }
            $query->orderBy($order_by, $order);

            $datas = $query->simplePaginate($limit);
            foreach ($datas as &$data) {
                $data->gambar_url = 'https://staging-merchant.dompetaman.com/assets/product-image/' . $data->gambar;
            }
            return response()->json($datas);
        } else {
            return response()->json([
                "success" => false,
                "message" => 'tidak ada otoritas',
            ], 200);
        }
    }

    public function transactionPsikolog(Request $request)
    {
        $user = Auth::user();
        if ($user->role == 3) {
            $queryStrings = $request->except('limit', 'user_id', 'order_by', 'order', 'page', 'count', 'current_page', 'last_page', 'next_page_url', 'per_page', 'previous_page_url', 'total', 'url', 'from', 'to');

            $limit = ($request->get('limit') ? $request->get('limit') : '10');
            $order_by = ($request->get('order') ? 'transactions' . $request->get('order') : 'transactions.id');
            $order = ($request->get('order_by') ? $request->get('order_by') : 'desc');
            $page = ($request->get('page') ? $request->get('page') : '1');

            $id = $request->get("id");
            // if (isset($user)) {
            //     $userId = $user->id;
            // } else {
            //     $userId = 0;
            // }

            if (isset($id)) {
                $query = Transaction::select('transactions.*', 'client.name', 'psikolog.name')
                    ->where('transactions.id', $id)
                    ->leftJoin('users as client', 'client.id', '=', 'transactions.user_id')
                    ->leftJoin('users as psikolog', 'psikolog.id', '=', 'transactions.psikolog_id')->first();

                if (!isset($query)) {
                    return response()->json([
                        "success" => false,
                        "message" => "psikolog tidak ditemukan",
                    ], 200);
                }

                return response()->json([
                    "success" => true,
                    "message" => "success",
                    "data" => $query,
                ], 200);
            }

            if ($limit >= 100) {
                $limit = 100;
            }

            $query = Transaction::select('transactions.*', 'client.name', 'psikolog.name')
                ->leftJoin('users as client', 'client.id', '=', 'transactions.user_id')
                ->leftJoin('users as psikolog', 'psikolog.id', '=', 'transactions.psikolog_id');

            foreach ($queryStrings as $key => $value) {
                $query->where('transactions' . $key, '=', $value);
            }
            $query->orderBy($order_by, $order);

            $datas = $query->simplePaginate($limit);

            return response()->json($datas);
        } else {
            return response()->json([
                "success" => false,
                "message" => 'tidak ada otoritas',
            ], 200);
        }
    }

    public function getTransactionProduct(Request $request)
    {
        $user = Auth::user();

        $queryStrings = $request->except('limit', 'user_id', 'order_by', 'order', 'page', 'count', 'current_page', 'last_page', 'next_page_url', 'per_page', 'previous_page_url', 'total', 'url', 'from', 'to');

        $limit = ($request->get('limit') ? $request->get('limit') : '10');
        $order_by = ($request->get('order') ? 'transaction_product' . $request->get('order') : 'transaction_product.id');
        $order = ($request->get('order_by') ? $request->get('order_by') : 'desc');
        $page = ($request->get('page') ? $request->get('page') : '1');

        $id = $request->get("id");
        if (isset($user)) {
            $userId = $user->id;
        } else {
            $userId = 0;
        }

        if (isset($id)) {
            $query = TransactionProduct::select('transaction_product.*', 'users.name', 'products.product_name', 'products.gambar')
                ->where('transaction_product.id', $id)
                ->leftJoin('users', 'users.id', '=', 'transaction_product.user_id')
                ->leftJoin('products', 'products.id', '=', 'transaction_product.product_id')->first();

            if (!isset($query)) {
                return response()->json([
                    "success" => false,
                    "message" => "product tidak ditemukan",
                ], 200);
            }
            $query->gambar_url = 'https://staging-merchant.dompetaman.com/assets/product-image/' . $query->gambar;

            return response()->json([
                "success" => true,
                "message" => "success",
                "data" => $query,
            ], 200);
        }

        if ($limit >= 100) {
            $limit = 100;
        }

        $query = TransactionProduct::select('transaction_product.*', 'users.name', 'products.product_name', 'products.gambar')
            ->where('user_id', $user->id)
            ->leftJoin('users', 'users.id', '=', 'transaction_product.user_id')
            ->leftJoin('products', 'products.id', '=', 'transaction_product.product_id');

        foreach ($queryStrings as $key => $value) {
            $query->where('transaction_product' . $key, '=', $value);
        }
        $query->orderBy($order_by, $order);

        $datas = $query->simplePaginate($limit);
        foreach ($datas as &$data) {
            $data->gambar_url = 'https://staging-merchant.dompetaman.com/assets/product-image/' . $data->gambar;
        }
        return response()->json($datas);
    }

    public function paymentRequest(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'psikolog_id' => 'required',
            'topic_id' => 'required',
            'voucher_id' => 'nullable',
            'chat_room_id' => 'required',
        ]);

        $input = $request->all();

        $psikologUser = User::where('id', $input['psikolog_id'])->first();
        $psikolog = Psikolog::where('user_id', $input['psikolog_id'])->first();
        $no_transaction = '1' . $user->id . $input['psikolog_id'] . Str::random(10) . (int) (time() - 999999999);

        $transaction = Transaction::create([
            "user_id" => $user->id,
            "no_transaction" => $no_transaction,
            "psikolog_id" => $input['psikolog_id'],
            "chat_room_id" => $input['chat_room_id'],
            "topic_id" => $input['topic_id'],
            "voucher_id" => $input['voucher_id'],
            "cost" => $psikolog->tarif,
        ]);

        $barang[] = [
            'id_barang' => $transaction->id,
            'jumlah' => 1,
            'harga' => $psikolog->tarif,
            'nama_barang' => 'Konsultasi dengan ' . $psikologUser->name,
        ];

        // $barang[] = [
        //     'id_barang' => -2,
        //     'jumlah' => 1,
        //     'harga' => $data['belanja']->nilai_potongan * -1,
        //     'nama_barang' => 'Voucher',
        // ];

        $payload = [
            'no_transaksi' => $no_transaction,
            'jumlah_total_transaksi' => $psikolog->tarif,
            'barang' => $barang,
            'billing' => [
                'nama_pembeli' => $user->name,
                'no_telp_penerima' => $user->phone_number,
                'alamat' => '',
                'kota' => '',
                'kode_pos' => '',
                'country_code' => '',
            ],
            'shipping' => null,
            'nama_pembeli' => $user->name,
            'email' => $user->email,
            'no_telp_penerima' => $user->phone_number,
        ];
        // echo json_encode($payload);die;

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $payload,
        ], 200);
    }

    public function paymentRequestProduct(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'product_id' => 'required',
            'voucher_id' => 'nullable',
        ]);

        $input = $request->all();

        $product = Product::where('id', $input['product_id'])->first();
        $no_transaction = '2' . $user->id . $input['product_id'] . Str::random(10) . (int) (time() - 999999999);

        $transaction = TransactionProduct::create([
            "user_id" => $user->id,
            "product_id" => $product->id,
            "no_transaction" => $no_transaction,
            "voucher_id" => $input['voucher_id'],
            "cost" => $product->price,
        ]);

        $barang[] = [
            'id_barang' => $transaction->id,
            'jumlah' => 1,
            'harga' => $product->price,
            'nama_barang' => 'Konsultasi dengan ' . $product->product_name,
        ];
        $price = $product->price;
        if ($transaction->diskon != 0) {
            $barang[] = [
                'id_barang' => -2,
                'jumlah' => 1,
                'harga' => $transaction->diskon * -1,
                'nama_barang' => 'Voucher',
            ];
            $price = $product->price - $transaction->diskon;
        }

        $payload = [
            'no_transaksi' => $no_transaction,
            'jumlah_total_transaksi' => $price,
            'barang' => $barang,
            'billing' => [
                'nama_pembeli' => $user->name,
                'no_telp_penerima' => $user->phone_number,
                'alamat' => '',
                'kota' => '',
                'kode_pos' => '',
                'country_code' => '',
            ],
            'shipping' => null,
            'nama_pembeli' => $user->name,
            'email' => $user->email,
            'no_telp_penerima' => $user->phone_number,
        ];
        // echo json_encode($payload);die;

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $payload,
        ], 200);
    }

    public function paymentNotification(Request $request)
    {
        $input = $request->all();

        $meta = json_encode($input);
        $status_code = false;
        if ($input['transaction_status'] == 'settlement') {
            $status_code = true;
        }
        if (substr($input['order_id'], 0, 1) == '1') {
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
        } else if (substr($input['order_id'], 0, 1) == '2') {
            $transaction = TransactionProduct::where('no_transaction', $input['order_id'])->first();
            if ($transaction->status_bayar == 0) {
                $product = DB::table('products')->select('*')->where('id', $transaction->product_id)->first();
                $update = $transaction->update([
                    'detail' => $product->detail,
                    'status_bayar' => $status_code,
                    'meta' => $meta,
                ]);

                if ($update) {
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
}
