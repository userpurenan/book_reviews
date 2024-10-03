<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImageUploadService
{
    public function upload($icon_image)
    {
        $user = User::findOrFail(Auth::id());
        $icon_url = $user->image_url;

        if($icon_url !== null) { //すでにアイコンのURLが保存されている場合には削除して、新しいアイコンURLに更新する
            $icon_file_name = str_replace(env('AWS_URL'), '', $icon_url);
            Storage::disk('s3')->delete($icon_file_name);
        }

        $image_url = Storage::disk('s3')->put('/', $icon_image);
        $user->update(['image_url' => env('AWS_URL').$image_url]);

        return $image_url;
    }
}
