<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AuthUserController extends Controller
{
    public function imageUploads(Request $request)
    {
        $image_url = Storage::disk('s3')->put('/', $request->file('icon'));
        $user = User::findOrFail(Auth::id());

        $user->update(['image_url' => "https://laravel-app-icon.s3.ap-northeast-1.amazonaws.com/$image_url"]);

        return response()->json(['image_url' => "https://laravel-app-icon.s3.ap-northeast-1.amazonaws.com/$image_url"]);
    }

    public function getUser()
    {
        $user = Auth::user();

        return response()->json([
                    'name' => $user->name,
                    'image_url' => $user->image_url,
                ], 200);
    }

    public function editUser(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $user->update(['name' => $request->input('name') ?? $user->name ]);

        return response()->json([ 'name' => $user->name ], 200);
    }
}
