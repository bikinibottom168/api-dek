<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Review;
use App\Models\User;
use App\Models\Post;
use Image;

class UserController extends Controller
{


    public function user()
    {
        return response()->json(['user' => request()->user()]);
    }


    // Change Name
    public function changeName()
    {
        request()->validate([
            'name' => 'required|min:5'
        ]);

        auth()->user()->display_name = request()->name;
        auth()->user()->update();

        return response()->json(['status' => "ok"]);
    }

    // Change Image
    public function changeImage(Request $request)
    {
        request()->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240'
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = Str::random(32) . '.' . $request->file('image')->getClientOriginalExtension();

            $img = Image::make($image->path());
            $img->resize($img->width() / 4, $img->height() / 4)->save(public_path('images/member/profile') . "/" . $imageName,);

            $user = User::where('id', request()->user()->id)->first();
            $user->user_image = 'images/member/profile/' . $imageName;
            $user->update();

            return response()->json(['status' => 'ok']);
        }
    }

    // Check Name While Change Name
    public function checkName()
    {
        request()->validate([
            'name' => 'required'
        ]);

        $user = User::where('display_name', request()->name)->count();

        if ($user >= 1) {
            return response()->json(['status' => 'used']);
        } else {
            return response()->json(['status' => 'ok']);
        }
    }

    // Get My Post
    public function myPost()
    {
        $post = Post::where('users_id', auth()->user()->id)->orderBy('expire', 'desc')->get();

        return response()->json(['data' => $post]);
    }
}
