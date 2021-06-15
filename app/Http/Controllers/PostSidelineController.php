<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Post;
use App\Models\Review;
use App\Models\Favorite;
use App\Models\Group;
use App\Models\Zone;

class PostSidelineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (request()->clear) {
            Cache::flush();
        }
        if (request()->page != null) {
            if (Cache::has('sideline-' . request()->page)) {
                return response()->json(['data' => Cache::get('sideline-' . request()->page)]);
            } else {
                $post = Cache::remember('sideline-' . request()->page, 1200, function () {
                    return response()->json([
                        'vip' => Post::where([['vip', '>', Carbon::now()->timestamp], ['expire', '>', Carbon::now()->timestamp], ['enable', '=', 1]])
                            ->inRandomOrder()->paginate(30)
                    ]);
                });
            }
        } else {
            if (Cache::has('sideline-1')) {
                return response()->json(['data' => Cache::get('sideline-' . request()->page)]);
            } else {
                $post = Cache::remember('sideline-1', 1200, function () {
                    return response()->json([
                        'vip' => Post::where([['vip', '>', Carbon::now()->timestamp], ['expire', '>', Carbon::now()->timestamp], ['enable', '= ', 1]])
                            ->inRandomOrder()->paginate(30)
                    ]);
                });
            }
        }

        if ($post) {
            return response()->json(['data' => $post]);
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        request()->validate([
            'title' => 'required',
            'price' => 'integer',
            'description' => 'required',
            'sex' => 'required',
            'weight' => 'integer',
            'height' => 'integer',
            'chest' => 'integer',
            'hip' => 'integer',
            'waist' => 'integer',
            'age' => 'integer',
            'line' => 'required',
            'location' => 'integer',
            'selectJob' => 'required',
            'image' => 'file',
            'package' => 'required',
            ''
        ]);

        $post = new Post;
        $post->title = "";
        $post->price = "";
        $post->description = "";
        $post->sex = "";
        $post->weight = "";
        $post->height = "";
        $post->chest = "";
        $post->hip = "";
        $post->waist = "";
        $post->age = "";
        $post->line = "";
        $post->location = "";
        $post->selectJob = "";
        $post->image = "";
        $post->package = "";
        $post->save();

        return response()->json(['status' => "ok"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::where([['post_slug', $id], ['enable', '=', 1]])->with(array('getComment' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }, 'getComment.getUser' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }))->first();

        if ($post) {
            if ($post->expire > Carbon::now()->timestamp && $post->vip > Carbon::now()->timestamp) {
                return response()->json(['data' => $post]);
            }
            return response()->json(['error' => 403], 403);
        } else {
            return response()->json(['error' => 404], 404);
        }
    }

    public function updateEnablePost()
    {

        $post = Post::where([
            ['users_id', '=', auth()->user()->id], ['id', '=', request()->id]
        ])->first();

        if ($post) {
            if ($post->enable == "1") {
                $now = Carbon::now()->timestamp;
                if ($now < $post->expire && $now < $post->vip) {
                    $diff_expire = $post->expire - $now;
                    $diff_vip = $post->vip - $now;
                } elseif ($now > $post->expire && $now > $post->vip) {
                    $diff_expire =  0;
                    $diff_vip =  0;
                }

                $post->enable = 0;
                $post->expire_count = $diff_expire;
                $post->vip_count = $diff_vip;
                $post->update();

                return response()->json(['status' => "stop"]);
            } elseif ($post->enable == "0") {
                $now = Carbon::now()->timestamp;
                $new_expire = $now + $post->expire_count;
                $new_vip = $now + $post->vip_count;

                $post->enable = 1;
                $post->expire_count = 0;
                $post->vip_count = 0;
                $post->expire = $new_expire;
                $post->vip = $new_vip;
                $post->update();

                return response()->json(['status' => "star", "expire" => $new_expire, "vip" => $new_vip]);
            }
        } else {
            abort(401);
        }

        if ($post) {
            $post->enable = !$post->enable;
            $post->update();

            return response()->json(['status', 'ok']);
        } else {
            return response()->json(['status', 'error'], '403');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        request()->validate([
            'title' => 'required',
            'price' => 'integer',
            'description' => 'required',
            'sex' => 'required',
            'weight' => 'integer',
            'height' => 'integer',
            'chest' => 'integer',
            'hip' => 'integer',
            'waist' => 'integer',
            'age' => 'integer',
            'line' => 'required',
            'location' => 'integer',
            'selectJob' => 'required',
            'image' => 'file',
            'package' => 'required',
            ''
        ]);

        $post = Post::where([['post_slug', $id], ['users_id', auth()->user()->id]])->first();
        if ($post) {
            $post->title = "";
            $post->price = "";
            $post->description = "";
            $post->sex = "";
            $post->weight = "";
            $post->height = "";
            $post->chest = "";
            $post->hip = "";
            $post->waist = "";
            $post->age = "";
            $post->line = "";
            $post->location = "";
            $post->selectJob = "";
            $post->image = "";
            $post->package = "";
            $post->update();
            return response()->json(['status' => "ok"]);
        } else {
            abort(401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::where([['post_slug', $id], ['users_id', auth()->user()->id]])->first();

        if ($post) {
            $post->delete();
            return response()->json(['status' => 'ok']);
        } else {
            abort(401);
        }
    }


    public function postSidelineUser()
    {
        $post = Post::where([['users_id', auth()->user()->id]])->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $post]);
    }

    public function favorite()
    {
        $post = Favorite::where('favorites.users_id', auth()->user()->id)
            ->join('posts', 'favorites.posts_id', '=', 'posts.id')
            ->select('posts.*')
            ->orderBy('posts.created_at')
            ->get();

        return response()->json(['data' => $post]);
    }

    public function favoriteStore()
    {
        request()->validate([
            "posts_id" => "required"
        ]);

        $check = Favorite::where([['posts_id', request()->posts_id], ['users_id', auth()->user()->id]])->first();

        if (!$check) {
            $favorite = new Favorite;
            $favorite->posts_id = request()->posts_id;
            $favorite->users_id = auth()->user()->id;
            $favorite->save();

            return response()->json(['liked' => true]);
        } else {
            $check->delete();

            return response()->json(['liked' => false]);
        }
    }

    public function favoriteCheck(Request $request)
    {

        $check = Favorite::where([['posts_id', $request->id], ['users_id', auth()->user()->id]])->first();


        if (!$check) {
            return response()->json(['liked' => false]);
        } else {
            return response()->json(['liked' => true]);
        }
    }
}
