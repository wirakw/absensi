<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Midtrans\ApiRequestor;
use App\Http\Controllers\Midtrans\Config;
use App\Http\Controllers\Midtrans\CoreApi;
use App\Http\Controllers\Midtrans\Notification;
use App\Http\Controllers\Midtrans\Sanitizer;
use App\Http\Controllers\Midtrans\Snap;
use App\Http\Controllers\Midtrans\SnapApiRequestor;
use App\Http\Controllers\Midtrans\Transaction;

class MidtransController extends Controller
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
    //
    public function getSnapToken(Request $request)
    {
        $data = $request->all();

        // print_r($data); die();

        foreach ($data as $key => $value) {
            // code...
            ${$key} = $value;
        }

        // print_r($barang);

        $item_details = [];
        foreach($barang as $brg) {
            $item_details[] = [
                'id' => $brg['id_barang'],
                'price' => $brg['harga'],
                'quantity' => $brg['jumlah'],
                'name' => $brg['nama_barang']
            ];
        }

        $billing_address = [
            'first_name' => $billing['nama_pembeli'],
            'address' => $billing['alamat'],
            'city' => $billing['kota'],
            'postal_code' => $billing['kode_pos'],
            'phone' => $billing['no_telp_penerima'],
            'country_code' => $billing['country_code'],
        ];

        $shipping_address = null;
        $customer_details = [
            'first_name' => $nama_pembeli,
            'email' => $email,
            'phone' => $no_telp_penerima,
            'billing_address' => $billing_address,
            'shipping_address' => $shipping_address
        ];

        //Set Your server key
        Config::$serverKey = config('services.midtrans_server_key');

        // Uncomment for production environment
        // Config::$isProduction = true;

        // Enable sanitization
        Config::$isSanitized = true;

        // Enable 3D-Secure
        Config::$is3ds = true;

        // required
        $transaction_details = [
            'order_id' => $no_transaksi,
            'gross_amount' => $jumlah_total_transaksi
        ];

        $transaction = [
                'transaction_details' => $transaction_details,
                'customer_details' => $customer_details,
                'item_details' => $item_details
        ];
        
        try {
            $snapToken = Snap::getSnapToken($transaction);
            return response()->json([
                "success" => true,
                "message" => "success",
                "snap_token" => $snapToken,
                "redirect" => "https://app.sandbox.midtrans.com/snap/v2/vtweb/" . $snapToken,
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                "success" => false,
                "message" => "failed",
            ], 200);
        }
    }
}
