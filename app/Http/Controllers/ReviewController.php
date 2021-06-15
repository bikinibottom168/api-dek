<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Review;
use App\Models\Likereview;
use App\Models\User;
use App\Models\Posts;
use Image;


class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $review = Review::orderBy('created_at', 'desc')->paginate(30);
        return response()->json(['data' => $review]);
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
            'score' => 'required',
            'description' => 'required',
            'image' => 'array',
            'posts_id' => 'required'
        ]);

        // Upload Image
        $imageRes = "";
        if ($request->hasFile('image')) {
            for ($i = 0; $i < count($request->image); $i++) {
                $imageName = Str::random(32) . '.' . $request->image[$i]->getClientOriginalExtension();
                // $request->image[$i]->move(public_path('images/reviews'), $imageName);

                $image = $request->image[$i];
                $img = Image::make($image->path());
                $img->resize($img->width() / 4, $img->height() / 4)->save(public_path('images/reviews/') . $imageName);

                // last
                if ($i == count($request->image) - 1) {
                    $imageRes .= "images/reviews/$imageName";
                } else {
                    $imageRes .= "images/reviews/$imageName|";
                }
            }
        }



        $review = new Review;
        $review->title = request()->title;
        $review->description = request()->description;
        $review->score = request()->score;
        $review->image = $imageRes;
        $review->users_id = auth()->user()->id;
        $review->posts_id = request()->posts_id;
        $review->save();

        // $post = Post::where([['post_slug', $id], ['enable', '=', 1]])->with(array('getComment' => function ($query) {
        //     $query->orderBy('created_at', 'desc');

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
        // $review = Review::where('users_id',$id)->orderBy('created_at','desc')->paginate(30);
        // return response()->json(['data' => $review]);

        $review = Review::where('reviews.users_id', $id)
            ->join('posts', 'reviews.posts_id', 'posts.id')
            ->select('posts.post_slug as slug_post', 'posts.title as id_posts', 'posts.image as image_posts', 'posts.title as title_posts', 'reviews.*')
            ->orderBy('reviews.created_at', 'desc')->paginate(10);
        return response()->json(['data' => $review]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
            'score' => 'required',
            'description' => 'required',
            'image' => 'required',
            'posts_id' => 'required'
        ]);

        $review = Review::where([['id', request()->id], ['users_id', auth()->user()->id]]);
        $review->title = request()->title;
        $review->description = request()->description;
        $review->score = request()->score;
        $review->image = request()->image;
        $review->users_id = auth()->user()->id;
        $review->posts_id = request()->posts_id;
        $review->update();

        return response()->json(['status' => "ok"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $review = Review::find($id);
        $review->delete();

        return response()->json(['status' => "ok"]);
    }

    public function likeReview()
    {
        request()->validate([
            'reviews_id' => 'required'
        ]);

        $check = Likereview::where([['reviews_id', request()->reviews_id], ['users_id', auth()->user()->id]])->first();

        if (!$check) {
            $like = new Likereview;
            $like->users_id = auth()->user()->id;
            $like->reviews_id = request()->reviews_id;
            $like->save();

            $review = Review::find(request()->reviews_id);
            $review->like_count = $review->like_count + 1;
            $review->update();
        } else {
            $check->delete();

            $review = Review::find(request()->reviews_id);
            $review->like_count = $review->like_count - 1;
            $review->update();
        }
        return response()->json(['status' => "ok"]);
    }
}
