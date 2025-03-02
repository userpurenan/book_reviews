<?php

declare(strict_types=1);

namespace App\Http\Controllers\UserDomain;

use Illuminate\Http\Request;
use App\Models\UserDomain\User;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\User\ImageUploadService;

class AuthUserController extends Controller
{
    public function imageUploads(Request $request, ImageUploadService $imageUploadService): JsonResponse
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

    public function editUser(Request $request): JsonResponse
    {
        $user = User::findOrFail(Auth::id());

        $user->update(['name' => $request->input('name') ?? $user->name ]);

        return response()->json([ 'name' => $user->name ], 200);
    }

    public function deleteUser(): JsonResponse
    {
        User::findOrFail(Auth::id())->delete();

        return response()->json([
            'massage' => 'ユーザーの削除に成功しました',
            'user_id' => Auth::id(),
        ], 200);
    }
}
