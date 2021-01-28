<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CategoryArticle;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'getCategoryArticle']]);
    }

    /**
     * article.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $queryStrings = $request->except('limit', 'category_article_id', 'mood_id', 'created_by', 'order_by', 'order', 'page', 'count', 'current_page', 'last_page', 'next_page_url', 'per_page', 'previous_page_url', 'total', 'url', 'from', 'to');

        $limit = ($request->get('limit') ? $request->get('limit') : '10');
        $order_by = ($request->get('order') ? 'articles' . $request->get('order') : 'articles.created_at');
        $order = ($request->get('order_by') ? $request->get('order_by') : 'desc');
        $page = ($request->get('page') ? $request->get('page') : '1');
        $category_article_id = $request->get("category_article_id");
        $id = $request->get("id");
        $mood_id = $request->get("mood_id");
        $created_by = $request->get("created_by");
        if (isset($user)) {
            $userId = $user->id;
        } else {
            $userId = 0;
        }

        if (isset($id)) {
            $data = Article::select('articles.*', 'users.name', 'category_article.category_name')
                ->where('articles.id', $id)
                ->leftJoin('category_article', 'category_article.id', '=', 'articles.category_article_id')
                ->leftJoin('users', 'users.id', '=', 'articles.user_id')->first();

            $data['number_of_like'] = Like::where('article_id', $data['id'])->count();
            if ($userId != 0) {
                $like = Like::where('article_id', $data['id'])->where('user_id', $userId)->first();
                if (isset($like)) {
                    $data['is_like'] = true;
                } else {
                    $data['is_like'] = false;
                }
            } else {
                $data['is_like'] = false;
            }

            if (!isset($data)) {
                return response()->json([
                    "success" => false,
                    "message" => "article tidak ditemukan",
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
        $query = Article::select('articles.*', 'users.name', 'category_article.category_name')
            ->leftJoin('category_article', 'category_article.id', '=', 'articles.category_article_id')
            ->leftJoin('users', 'users.id', '=', 'articles.user_id');

        foreach ($queryStrings as $key => $value) {
            $query->where($key, '=', $value);
        }
        if (isset($category_article_id)) {
            $query->where('articles.category_article_id', $category_article_id);
        }
        if (isset($mood_id)) {
            $query->where('articles.mood_id', $mood_id);
        }
        if (isset($created_by)) {
            $query->where('articles.user_id', $created_by);
        }
        $query->orderBy($order_by, $order);
        // $query->simplePaginate($limit);

        // $data = array();
        $data = $query->simplePaginate($limit);

        foreach ($data as &$dt) {
            $dt->isi = substr($dt->isi, 0, 100);
            $dt->number_of_like = Like::where('article_id', $dt->id)->count();

            if ($userId != 0) {
                $like = Like::where('article_id', $dt->id)->where('user_id', $userId)->first();
                if (isset($like)) {
                    $dt->is_like = true;
                } else {
                    $dt->is_like = false;
                }
            } else {
                $dt->is_like = false;
            }
        }
        return response()->json($data);
    }

    /**
     * chat.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategoryArticle(Request $request)
    {
        $id = $request->get("id");

        if (isset($id)) {
            $category_article = CategoryArticle::where('id', $id)->first();
            if (!isset($category_article)) {
                return response()->json([
                    "success" => false,
                    "message" => "tidak ada data",
                ], 200);
            }

            return response()->json([
                "success" => true,
                "message" => "success",
                "data" => $category_article,
            ], 200);
        }

        $category_article = CategoryArticle::orderBy('category_name', 'desc')->get();

        if (!isset($category_article)) {
            return response()->json([
                "success" => false,
                "message" => "tidak ada data",
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $category_article,
        ], 200);
    }

    /**
     * merchant.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeOrDislike(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'article_id' => 'required',
        ]);
        $data['article_id'] = $request->input("article_id");
        $data['user_id'] = $user->id;
        $recent = Like::where('user_id', $user->id)->where('article_id', $data['article_id'])->first();
        if (isset($recent)) {
            $delete = Like::where('id', $recent->id)->where('user_id', $user->id)->delete();
            if (!isset($delete)) {
                return response()->json([
                    "success" => false,
                    "message" => 'fail delete data',
                ], 422);
            }
        } else {
            $create = Like::create($data);
            if (!$create) {
                return response()->json([
                    "success" => false,
                    "message" => 'fail create data',
                ], 422);
            }
        }

        return response()->json([
            "success" => true,
            "message" => 'success add or remove like',
        ], 201);
    }
}
