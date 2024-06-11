<?php

namespace App\Helpers;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class FileManager
{
    

    public function saveFile($file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        $uploadedFile = Cloudinary::upload($file->getRealPath(), [
            'folder' => 'files',
            'resource_type' => 'raw',
            'public_id' => $originalFilename, 
            'format' => $extension
        ]);

        $uploadedFileUrl = $uploadedFile->getSecurePath();

        if (!$uploadedFileUrl) {
            throw new \Exception("Error al guardar el archivo");
        }

        return $uploadedFileUrl;
    }

    public function changeFile($file,$oldUrl){
        $this->deleteFile($oldUrl);

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        $uploadedFile = Cloudinary::upload($file->getRealPath(), [
            'folder' => 'files',
            'resource_type' => 'raw',
            'public_id' => $originalFilename, 
            'format' => $extension
        ]);

        $uploadedFileUrl = $uploadedFile->getSecurePath();

        if (!$uploadedFileUrl) {
            throw new \Exception("Error al guardar el archivo");
        }

        return $uploadedFileUrl;
    }

    public function deleteFile($urlFile)
    {
        if (!$urlFile) {
            return false;
        }

        try {
            $publicId = basename($urlFile, '.' . pathinfo($urlFile, PATHINFO_EXTENSION));
            Cloudinary::destroy($publicId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
