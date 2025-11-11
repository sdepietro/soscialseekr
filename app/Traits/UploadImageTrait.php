<?php

namespace App\Traits;

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UploadImageTrait
{

    public function uploadImage(UploadedFile $uploadedFile, $width, $height, $folder = null, $disk = 'public', $filename = null)
    {
        ini_set('memory_limit','256M');
        $ext = $uploadedFile->extension();
        $uploadedFile = $this->resizeImage($uploadedFile, $width, $height);
        $url = $folder . '/' . $filename . '.' . $ext;
        $url = str_replace('//', '/', $url);
        Storage::disk($disk)->put($url, $uploadedFile, 'public');
        return $url;
    }

    public function uploadSvg(UploadedFile $uploadedFile, $folder = null, $disk = 'public', $filename = null)
    {
        return Storage::disk($disk)->put($folder  , $uploadedFile);
    }
    public function deleteFile($file,$disk)
    {
       Storage::disk($disk)->delete($file);
    }

    public function uploadBase64Image($uploadedFile, $width, $height, $folder = null, $disk = 'public', $filename = null)
    {

        $image = Image::make($uploadedFile);

        $mime = $image->mime();
        switch ($mime) {
            case 'image/jpeg':
                $ext = 'jpg';
                break;
            case 'image/png':
                $ext = 'png';
                break;
            case 'image/gif':
                $ext = 'gif';
                break;
            default:
                $ext = '';
                break;
        }

        $final_route = $folder . '/' . $filename . '.' . $ext;
        $final_route = str_replace('//', '/', $final_route);
        $uploadedFile = $this->resizeImage($uploadedFile . $filename, $width, $height);
        Storage::disk($disk)->put($final_route, $uploadedFile, 'public');
        return $final_route;
    }

    public function resizeImage($uploadedFile, $width = null, $height = null)
    {
        $imageFile = Image::make($uploadedFile);
        $imageFile->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $imageFile = $imageFile->stream();
        $imageFile = $imageFile->__toString();
        return $imageFile;

    }


}
