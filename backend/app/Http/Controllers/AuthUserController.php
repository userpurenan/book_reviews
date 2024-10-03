<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\User\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthUserController extends Controller
{
    public function imageUploads(Request $request, ImageUploadService $imageUploadService)
    {
        $image_url = $imageUploadService->upload($request->file('icon'));

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
