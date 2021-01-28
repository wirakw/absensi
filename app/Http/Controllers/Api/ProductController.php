<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Traits\ApiResponser;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    use ApiResponser;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'search', 'categoryProduct']]);
    }

    /**
     * product.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $queryStrings = $request->except('limit', 'category_id', 'user_id', 'order_by', 'order', 'page', 'count', 'current_page', 'last_page', 'next_page_url', 'per_page', 'previous_page_url', 'total', 'url', 'from', 'to');

        $limit = ($request->get('limit') ? $request->get('limit') : '10');
        $order_by = ($request->get('order') ? 'products' . $request->get('order') : 'products.id');
        $order = ($request->get('order_by') ? $request->get('order_by') : 'desc');
        $page = ($request->get('page') ? $request->get('page') : '1');
        $category_id = $request->get("category_id");
        $id = $request->get("id");
        if (isset($user)) {
            $userId = $user->id;
        } else {
            $userId = 0;
        }

        if (isset($id)) {
            
            $data = Product::select('products.*', 'category_product.category_product_name')
                ->where('products.id', $id)
                ->leftJoin('category_product', 'category_product.id', '=', 'products.category_product_id')->first();

            // if ($userId != 0) {
            //     $whitelist = DB::connection('wallet')->table('whitelists')
            //         ->select('whitelists.*')
            //         ->where('product_id', $data->id)->where('user_id', $userId)->first();
            //     if (isset($whitelist->iswhitelist)) {
            //         $data['iswhitelist'] = $whitelist->iswhitelist;
            //     } else {
            //         $data['iswhitelist'] = false;
            //     }
            // } else {
            //     $data['iswhitelist'] = false;
            // }

            if (!isset($data)) {
                return response()->json([
                    "success" => false,
                    "message" => "product tidak ditemukan",
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
        // $query = DB::connection('merchant')->table('merchant_products');
        $query = Product::select('products.*', 'category_product.category_product_name')
            ->where('products.is_active', true)
            ->leftJoin('category_product', 'category_product.id', '=', 'products.category_product_id');

        foreach ($queryStrings as $key => $value) {
            $query->where($key, '=', $value);
        }
        if (isset($category_id)) {
            $query->where('products.category_product_id', $category_id);
        }
        $query->orderBy($order_by, $order);
        // $query->simplePaginate($limit);

        // $data = array();
        $data = $query->simplePaginate($limit);

        foreach ($data as &$dt) {
            // if ($userId != 0) {
            //     $whitelist = DB::connection('wallet')->table('whitelists')
            //         ->select('whitelists.*')
            //         ->where('product_id', $dt->id)->where('user_id', $userId)->first();
            //     if (isset($whitelist->iswhitelist)) {
            //         $dt->iswhitelist = $whitelist->iswhitelist;
            //     } else {
            //         $dt->iswhitelist = false;
            //     }
            // } else {
            //     $dt->iswhitelist = false;
            // }
        }
        return response()->json($data);
    }

    /**
     * product.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $queryStrings = $request->except('limit', 'user_id', 'category_id', 'search', 'order_by', 'order', 'page', 'count', 'current_page', 'last_page', 'next_page_url', 'per_page', 'previous_page_url', 'total', 'url', 'from', 'to');

        $limit = ($request->get('limit') ? $request->get('limit') : '10');
        $order_by = ($request->get('order') ? 'products' . $request->get('order') : 'products.id');
        $order = ($request->get('order_by') ? $request->get('order_by') : 'desc');
        $page = ($request->get('page') ? $request->get('page') : '1');
        $search = $request->get("search");
        $category_id = $request->get("category_id");
        if (isset($user)) {
            $userId = $user->id;
        } else {
            $userId = 0;
        }
        if ($limit >= 100) {
            $limit = 100;
        }

        $query = Product::select('products.*', 'category_product.category_product_name')
            ->where('products.is_active', true)
            ->leftJoin('category_product', 'category_product.id', '=', 'products.category_product_id');

        if (isset($category_id)) {
            $query->where('products.category_product_id', $category_id);
        }
        if (isset($search)) {
            $query->where(function ($subquery) use ($search) {
                $subquery->where('products.product_name', 'like', "%{$search}%")
                    ->orWhere('category_product.category_product_name', 'like', "%{$search}%");
            });
        }
        $query->orderBy($order_by, $order);
        $data = $query->simplePaginate($limit);

        foreach ($data as &$dt) {
            // if ($userId != 0) {
            //     $whitelist = DB::connection('wallet')->table('whitelists')
            //         ->select('whitelists.*')
            //         ->where('product_id', $dt->id)->where('user_id', $userId)->first();
            //     if (isset($whitelist->iswhitelist)) {
            //         $dt->iswhitelist = $whitelist->iswhitelist;
            //     } else {
            //         $dt->iswhitelist = false;
            //     }
            // } else {
            //     $dt->iswhitelist = false;
            // }
        }
        return response()->json($data);
    }

    /**
     * product.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryProduct(Request $request)
    {
        $id = $request->get("id");
        if (isset($id)) {
            $data = CategoryProduct::where('id', $id)->first();
            if (!isset($data)) {
                return response()->json([
                    "success" => false,
                    "message" => "product category tidak ditemukan",
                    "data" => $data,
                ], 200);
            }
            return response()->json([
                "success" => true,
                "message" => "message",
                "data" => $data,
            ], 200);
        }

        $data = CategoryProduct::get();
        if (!$data->count()) {
            return response()->json([
                "success" => false,
                "message" => "product category tidak ditemukan",
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "message",
            "data" => $data,
        ], 200);
    }

    /**
     * merchant.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMerchant(Request $request)
    {
        $queryStrings = $request->except('limit', 'user_id', 'order_by', 'order', 'page', 'count', 'current_page', 'last_page', 'next_page_url', 'per_page', 'previous_page_url', 'total', 'url', 'from', 'to');
        $limit = ($request->get('limit') ? $request->get('limit') : '10');
        $order_by = ($request->get('order') ? $request->get('order') : 'created_at');
        $order = ($request->get('order_by') ? $request->get('order_by') : 'desc');
        $page = ($request->get('page') ? $request->get('page') : '1');

        $id = $request->get("id");
        if (isset($id)) {
            $data = Merchant::where('id', $id)->first();
            if (!isset($data)) {
                return response()->json([
                    "success" => false,
                    "message" => 'merchant tidak ditemukan',
                    "data" => $data,
                ], 200);
            }

            return response()->json([
                "success" => true,
                "message" => 'success',
                "data" => $data,
            ], 200);
        }

        $query = Merchant::where('is_active', true);
        foreach ($queryStrings as $key => $value) {
            $query->where($key, '=', $value);
        }
        $query->orderBy($order_by, $order);
        $data = $query->simplePaginate($limit);

        if (!$data->count()) {
            return response()->json([
                "success" => false,
                "message" => 'merchant tidak ditemukan',
                "data" => $data,
            ], 200);
        }
        return response()->json($data, 200);
    }

    /**
     * merchant.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addorRemoveWhiteList(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'product_id' => 'required',
        ]);
        $data['product_id'] = $request->input("product_id");
        $data['user_id'] = $user->id;
        $recent = DB::connection('wallet')->table('whitelists')->where('user_id', $user->id)->where('product_id', $data['product_id'])->first();
        if (isset($recent)) {
            $delete = DB::connection('wallet')->table('whitelists')->where('id', $recent->id)->where('user_id', $user->id)->delete();
            if (!isset($delete)) {
                return response()->json([
                    "success" => false,
                    "message" => 'fail delete data',
                ], 422);
            }
            UserActivity::create([
                'user_id' => Auth::user()->id,
                'activity' => 'remove wishlist product with id ' . $data['product_id']
            ]);
        } else {
            $create = DB::connection('wallet')->table('whitelists')->insertOrIgnore($data);
            if (!$create) {
                return response()->json([
                    "success" => false,
                    "message" => 'fail create data',
                ], 422);
            }
            UserActivity::create([
                'user_id' => Auth::user()->id,
                'activity' => 'add wishlist product with id ' . $data['product_id']
            ]);
        }

        return response()->json([
            "success" => true,
            "message" => 'success',
        ], 201);
    }

    /**
     * merchant.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function getWhitelist(Request $request)
    {
        $user = Auth::user();
        $id = $request->get("whitelist_id");
        if (isset($id)) {
            $data = Product::select('products.*', 'whitelists.id as whitelist_id', 'whitelists.iswhitelist', 'category_product.category_product_name', 'type_product.type', 'merchant.nama')
                ->leftJoin('merchant', 'merchant.id', '=', 'products.merchant_id')
                ->where('products.is_active', true)
                ->where('whitelists.user_id', $user->id)
                ->where('whitelists.id', $id)
                ->leftJoin('category_product', 'category_product.id', '=', 'products.category_product_id')
                ->leftJoin('wallet.whitelists as whitelists', 'whitelists.product_id', '=', 'products.id')
                ->leftJoin('type_product', 'type_product.id', '=', 'products.type_product_id')->first();

            if (!isset($data)) {
                return response()->json([
                    "success" => false,
                    "message" => 'wishlist tidak ditemukan',
                    "data" => $data,
                ], 200);
            }

            return response()->json([
                "success" => true,
                "message" => 'success',
                "data" => $data,
            ], 200);
        }

        $data = Product::select('products.*', 'whitelists.id as whitelist_id', 'whitelists.iswhitelist', 'category_product.category_product_name', 'type_product.type', 'merchant.nama')
            ->leftJoin('merchant', 'merchant.id', '=', 'products.merchant_id')
            ->where('products.is_active', true)
            ->where('whitelists.user_id', $user->id)
            ->leftJoin('category_product', 'category_product.id', '=', 'products.category_product_id')
            ->leftJoin('wallet.whitelists as whitelists', 'whitelists.product_id', '=', 'products.id')
            ->leftJoin('type_product', 'type_product.id', '=', 'products.type_product_id')->get();

        if (!$data->count()) {
            return response()->json([
                "success" => false,
                "message" => 'wishlist tidak ditemukan',
                "data" => $data,
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => 'success',
            "data" => $data,
        ], 200);
    }

}
