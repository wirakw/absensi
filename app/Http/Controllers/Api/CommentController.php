<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Merchant;
use App\Models\User;
use App\Models\UserActivity;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'getParent']]);
    }

    /**
     * comment.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (isset($user)) {
            $userId = $user->id;
        } else {
            $userId = 0;
        }

        $id = $request->get("id");
        $article_id = $request->get("article_id");

        if (isset($id)) {
            $comment = Comment::where('id', $id)->first();
            if (!isset($comment)) {
                return response()->json([
                    "success" => false,
                    "message" => "data tidak ditemukan",
                ], 200);
            }
            return response()->json([
                "success" => true,
                "message" => "success",
                "data" => $comment,
            ], 200);
        }

        $comments = Comment::with('getChildComment')->where('parent_id', null)->where('article_id', $article_id)->get();
        if (!$comments->count()) {
            return response()->json([
                "success" => false,
                "message" => "belum ada komentar",
            ], 200);
        }

        foreach ($comments as &$comment) {
            $user = User::where('id', $comment->user_id)->first();
            $comment->username = $user->name;
            $comment->user_photo = $user->photo_url;

            if ($userId != 0) {
                $like = DB::table('like_comment')
                    ->where('comment_id', $comment->id)
                    ->where('user_id', $userId)->count();
                $comment->is_like = $like;
            } else {
                $comment->is_like = 0;
            }
            $like_count = DB::table('like_comment')->where('user_id', $comment->user_id)->where('comment_id', $comment->id)->count();
            $comment->like_count = $like_count;

            foreach ($comment->getChildComment as &$childComment) {
                $user = User::where('id', $childComment->user_id)->first();
                $childComment->username = $user->name;
                $childComment->user_photo = $user->photo_url;

                if ($userId != 0) {
                    $like = DB::table('like_comment')
                        ->where('comment_id', $childComment->id)->where('user_id', $userId)->count();
                    $childComment->is_like = $like;
                } else {
                    $childComment->is_like = 0;
                }
                $like_count = DB::table('like_comment')->where('user_id', $childComment->user_id)->where('comment_id', $childComment->id)->count();
                $childComment->like_count = $like_count;
            }
        }

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $comments,
        ], 200);
    }

    /**
     * moneyTransaction.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParent(Request $request)
    {
        $comments = Comment::with('getParentComment')->get();
        if (!isset($comments)) {
            return response()->json([
                "success" => false,
                "message" => "comment not found",
            ], 200);
        }
        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $comments,
        ], 200);
    }

    /**
     * comment.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'article_id' => 'required',
            'comment' => 'nullable',
        ]);
        $input = $request->all();
        $input['user_id'] = $user->id;
        $create = Comment::create($input);

        return response()->json([
            "success" => true,
            "message" => "success",
            "data" => $create,
        ], 201);
    }

    /**
     * comment.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $this->validate($request, [
            'comment' => 'nullable',
        ]);
        $input = $request->all();
        $comment = comment::where('id', $id)->where('user_id', $user->id)->first();
        $update = $comment->update($input);
        if ($update) {
            return response()->json([
                "success" => true,
                "message" => "success update comment",
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "gagal update comment",
            ], 200);
        }
    }

    /**
     * comment.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $user = Auth::user();
        $comment = Comment::where('id', $id)->where('user_id', $user->id)->first();
        DB::table('like_comment')->where('comment_id', $id)->delete();
        $comment->delete();
        return response()->json([
            "success" => true,
            "message" => "success delete comment",
        ], 200);
    }

    /**
     * merchant.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addOrRemoveLike(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'comment_id' => 'required',
        ]);
        $data['comment_id'] = $request->input("comment_id");
        $data['user_id'] = $user->id;
        $recent = DB::table('like_comment')->where('user_id', $user->id)->where('comment_id', $data['comment_id'])->first();
        if (isset($recent)) {
            $delete = DB::table('like_comment')->where('id', $recent->id)->where('user_id', $user->id)->delete();
            if (!isset($delete)) {
                return response()->json([
                    "success" => false,
                    "message" => 'fail delete data',
                ], 422);
            }
        } else {
            $create = DB::table('like_comment')->insertOrIgnore($data);
            if (!$create) {
                return response()->json([
                    "success" => false,
                    "message" => 'fail create data',
                ], 422);
            }
        }
        return response()->json([
            "success" => true,
            "message" => 'success',
        ], 201);
    }
}
