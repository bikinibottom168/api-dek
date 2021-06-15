<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Zone;
use App\Models\User;
use App\Models\Likereview;
use App\Models\Review;
use App\Models\Post;
use Carbon\Carbon;

class ApiController extends Controller
{

    //Get Post Sideline
    // public function sideline()
    // {
    //     $post = Post::where('post_slug', request()->slug)->first();

    //     if ($post) {
    //         $review = Review::where('reviews.posts_id', $post->id)
    //             ->join('users', 'reviews.users_id', '=', 'users.id')
    //             ->select('reviews.*', 'users.display_name', 'users.user_image')
    //             ->orderBy('reviews.created_at', 'desc')->get();

    //         return response()->json(['sideline' => $post, 'review' => $review]);
    //     } else {
    //         abort(404);
    //     }
    // }

    public function zone()
    {

        // $data = Zone::orderBy('zones.order','asc')
        //     ->Join('groups','zones.groups_id','=','groups.id')
        //     ->select('zones.id','zones.title as label','zones.groups_id','zones.order','groups.title as title_groups')
        //     ->get();
        $data = Group::with('children')->orderBy('label', 'asc')->get();

        return response()->json(['data' => $data]);
    }

    public function zoneHot()
    {
        $data = Zone::where('zonehot', '1')->orderBy('order', 'ASC')->get();
        return response()->json(['data' => $data]);
    }

    // Member Login,Register
    public function login()
    {
        $credentials = request()->validate([
            'email' => 'required',
            'password' => 'required'
        ]);
        if (!auth()->attempt($credentials)) {
            return abort(401);
        } else {
            $user = User::where('email', $credentials['email'])->first();
            $user->tokens()->delete();
            $token = $user->createToken('token');
            return response()->json(['token' => $token->plainTextToken]);
        }
    }

    public function register(Request $request)
    {
        request()->validate([
            'name' => 'required',
            'email' => 'required|min:5|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'display_name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'name' => Str::random(12)
        ]);

        // Success
        if ($user) {
            $token = $user->createToken('token');
            return response()->json(['token' => $token->plainTextToken]);
        }
    }


    // Check User While Register
    public function checkUser()
    {
        request()->validate([
            'email' => 'required'
        ]);

        $user = User::where('email', request()->email)->count();

        if ($user >= 1) {
            return response()->json(['status' => 'used']);
        } else {
            return response()->json(['status' => 'ok']);
        }
    }


    //Profile
    public function profile()
    {
        $profile = User::where('id', request()->name)->first();

        if ($profile) {
            $review = Review::where('reviews.users_id', $profile->id)
                ->join('posts', 'reviews.posts_id', 'posts.id')
                // ->join('likereviews', function($join) {
                //     $join->on("reviews.id","=","likereviews.reviews_id")
                //         ->on("reviews.users_id","=","likereviews.users_id");
                // })
                ->select('posts.post_slug as slug_post', 'posts.title as id_posts', 'posts.image as image_posts', 'posts.title as title_posts', 'reviews.*')
                ->orderBy('reviews.created_at', 'desc')->get();

            return response()->json(['member' => $profile, 'review' => $review]);
        } else {
            abort(404);
        }
    }

    //Search
    public function search(Request $req)
    {

        $search['name'] = $req->name == "" || $req->name == null ? null : $req->name;
        $search['zone'] = $req->zone == "" || $req->zone == null ? null : $req->zone;
        $search['sex'] = $req->sex == "" || $req->sex == null ? null : $req->sex;
        $search['price'] = $req->price == "" || $req->price == null ? null : $req->price;
        $min_price = 0;
        $max_price = 0;
        if ($search['price'] == "0,1") {
            $min_price = 500;
            $max_price = 1500;
        } else if ($search['price'] == "0,2") {
            $min_price = 500;
            $max_price = 999999;
        } else if ($search['price'] == "1,2") {
            $min_price = 1500;
            $max_price = 999999;
        }

        if ($search['name'] == null && $search['zone'] == null && $search['sex'] != null && $search['price'] != null) {

            if ($search['sex'] == "all") {
                $post = Post::where([['price', '<', $max_price], ['price', '>', $min_price],  ['vip', '>', Carbon::now()->timestamp], ['vip', '>', Carbon::now()->timestamp], ['expire', '>', Carbon::now()->timestamp], ['enable', '=', 1]])
                    ->inRandomOrder()->paginate(30);
            } else {
                $post = Post::where([['price', '<', $max_price], ['price', '>', $min_price], ['sex', '=', $search['sex']], ['vip', '>', Carbon::now()->timestamp], ['vip', '>', Carbon::now()->timestamp], ['expire', '>', Carbon::now()->timestamp], ['enable', '=', 1]])
                    ->inRandomOrder()->paginate(30);
            }
        } else if ($search['name'] == null && $search['zone'] != null && $search['sex'] != null && $search['price'] != null) {

            // Zone Setting
            $zone = explode(",", $search['zone']);

            if ($search['sex'] == "all") {
                $post = Post::whereIn('zones_id', $zone)->where([['price', '<', $max_price], ['price', '>', $min_price],  ['vip', '>', Carbon::now()->timestamp], ['vip', '>', Carbon::now()->timestamp], ['expire', '>', Carbon::now()->timestamp], ['enable', '=', 1]])
                    ->inRandomOrder()->paginate(30);
            } else {
                $post = Post::whereIn('zones_id', $zone)->where([['price', '<', $max_price], ['price', '>', $min_price], ['sex', '=', $search['sex']], ['vip', '>', Carbon::now()->timestamp], ['vip', '>', Carbon::now()->timestamp], ['expire', '>', Carbon::now()->timestamp], ['enable', '=', 1]])
                    ->inRandomOrder()->paginate(30);
            }
        } else if ($search['name'] != null && $search['zone'] != null && $search['sex'] != null && $search['price'] != null) {
            $zone = explode(",", $search['zone']);
            $post = Post::whereIn('zones_id', $zone)->where([['title', 'LIKE', '%' . $search['name'] . '%'], ['price', '<', $max_price], ['price', '>', $min_price], ['sex', '=', $search['sex']], ['vip', '>', Carbon::now()->timestamp], ['vip', '>', Carbon::now()->timestamp], ['expire', '>', Carbon::now()->timestamp], ['enable', '=', 1]])
                ->inRandomOrder()->paginate(30);
        } else if ($search['name'] != null && $search['zone'] == null && $search['sex'] != null && $search['price'] != null) {
            if ($search['sex'] == "all") {
                $post = Post::where([['title', 'LIKE', '%' . $search['name'] . '%'], ['price', '<', $max_price], ['price', '>', $min_price], ['vip', '>', Carbon::now()->timestamp], ['vip', '>', Carbon::now()->timestamp], ['expire', '>', Carbon::now()->timestamp], ['enable', '=', 1]])
                    ->inRandomOrder()->paginate(30);
            } else {
                $post = Post::where([['title', 'LIKE', '%' . $search['name'] . '%'], ['price', '<', $max_price], ['price', '>', $min_price], ['sex', '=', $search['sex']], ['vip', '>', Carbon::now()->timestamp], ['vip', '>', Carbon::now()->timestamp], ['expire', '>', Carbon::now()->timestamp], ['enable', '=', 1]])
                    ->inRandomOrder()->paginate(30);
            }
        }

        return response()->json(['data' => $post]);
    }
}
