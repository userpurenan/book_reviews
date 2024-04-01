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
        $user = User::findOrFail(Auth::id());
        $icon_url = $user->image_url;

        if($icon_url !== null) { //すでにアイコンのURLが保存されている場合には削除して、新しいアイコンURLに更新する
            $icon_file_name = str_replace(env('AWS_URL'), '', $icon_url);
            Storage::disk('s3')->delete($icon_file_name);
        }

        $image_url = Storage::disk('s3')->put('/', $request->file('icon'));
        $user->update(['image_url' => env('AWS_URL').$image_url]);

        return response()->json(['image_url' => env('AWS_URL').$image_url]);
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

    public function deleteUser()
    {
        User::findOrFail(Auth::id())->delete();

        return response()->json(['massage' => 'success!!'], 200);
    }
}
