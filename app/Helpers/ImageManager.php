<?php

namespace App\Helpers;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Storage;
 

class ImageManager
{
    protected $image;
    public function __construct($image)
    {
        $this->image = $image;
        
    }

    public function saveImage(){
        if (!$this->image){
            return null;
        }
        
        $uploadedFileUrl = Cloudinary::upload($this->image->getRealPath())->getSecurePath();
        if (!$uploadedFileUrl) {
            throw new \Exception("Error al guardar la imagen");
        
        }
        return $uploadedFileUrl;
    }
    public function saveImageInLocal(){

        if (!$this->image) {
            return null;
        }

        $imageName = time() . '_' . $this->image->getClientOriginalName();
        $path = $this->image->storeAs('public/images', $imageName);
        if (!$path) {
            throw new \Exception("Error al guardar la imagen");
        }

         
        return Storage::url($path);
    }
    public function getImageFromLocal($path){
        $publicPath = 'public/images/' . basename($path);
            if (!Storage::exists($publicPath)) {
                throw new \Exception("image ".$path." not found.");
            }

            $imageContent = Storage::get($publicPath);

            return base64_encode($imageContent);
        }
    

    public function changeImage($urlOldImage) {
        if ($urlOldImage) {
            $publicId = basename($urlOldImage, '.' . pathinfo($urlOldImage, PATHINFO_EXTENSION));
            Cloudinary::destroy($publicId);
        }
        if (!$this->image ) {
            return null;
        }
        $newChangeFileUrl = Cloudinary::upload($this->image->getRealPath())->getSecurePath();
        if (!$newChangeFileUrl) {
            throw new \Exception("Error al guardar la imagen");
        }
        return $newChangeFileUrl;
    }
    public function changeImageInLocal($urlOldImage)
    {
        if ($urlOldImage) {
            $oldImagePath = str_replace('/storage', 'public', parse_url($urlOldImage, PHP_URL_PATH));
            if (Storage::exists($oldImagePath)) {
                Storage::delete($oldImagePath);
            }
        }

        if (!$this->image) {
            return null;
        }

        $newChangeFilePath = $this->saveImageInLocal();

        if (!$newChangeFilePath) {
            throw new \Exception("Error al guardar la nueva imagen");
        }

        return $newChangeFilePath;
    }

}

  