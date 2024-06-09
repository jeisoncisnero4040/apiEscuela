<?php
namespace App\Helpers;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class FileManager{

    protected $file;
    public function __construct($file)
    {
        $this->file=$file;
    }

    public function saveFile(){
        
        $originalFilename = pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $this->file->getClientOriginalExtension();

        $uploadedFile = Cloudinary::upload($this->file->getRealPath(), [
            'folder' => 'files',
            'resource_type' => 'raw',
            'public_id' => $originalFilename, 
            'format' => $extension
        ]);
        $uploadedFileUrl=$uploadedFile->getSecurePath();
        if (!$uploadedFileUrl) {
            throw new \Exception("Error al guardar la imagen");
        
        }
        return $uploadedFileUrl;
    }
}