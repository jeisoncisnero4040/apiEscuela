<?php

namespace App\Helpers;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
 

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

}

  