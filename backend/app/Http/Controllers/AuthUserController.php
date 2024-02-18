<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthUserController extends Controller
{
    public function imageUploads(Request $request)
    {
        $file_path = $request->file('icon')->store('public/img');
        $image_path = str_replace('public', 'storage', $file_path);
        $image_url = asset($image_path);
        $user = User::findOrFail(Auth::id());

        $user->update(['image_url' => $image_url]);

        return response()->json(['image_url' => $image_url]);
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

        $user->update(['name' => $request->input('name')]);
    }
}
