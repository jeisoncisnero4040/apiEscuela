<?php
 
namespace App\Mappers;

use App\Models\DetailFileModel;

class FilesMapper{
    protected $filesUnmapped;

    public function __construct($filesUnmapped)
    {
        $this->filesUnmapped=$filesUnmapped;
    }
    
    
    public function mapperfile(){
        $url = $this->filesUnmapped['file_url'];
        $filetype=self::getFileType($url);

        $this->filesUnmapped['type file'] = $filetype;
        return $this->filesUnmapped;
    }

    public function mapperFilesFromActivity(){
     
        $filesMapped = [];
        foreach ($this->filesUnmapped as $fileUnmapped) {
            
            $url = $fileUnmapped['file_url'];
            $filetype=self::getFileType($url);
    
            $fileUnmapped['type file'] = $filetype;
            array_push($filesMapped, $fileUnmapped);
        }
        return $filesMapped;
    }
    public function mapperFilesForStudent($studentId){
        $filesMapped = [];
        foreach ($this->filesUnmapped as $fileUnmapped) {
            
            $url = $fileUnmapped['file_url'];
            $activityFileId=$fileUnmapped['id'];
            $filetype=self::getFileType($url);

            $studentIsCheckedFile=DetailFileModel::where('student_id',$studentId)
                                                        ->where('activity_file_id',$activityFileId)
                                                        ->exists();

            $fileUnmapped['type file'] = $filetype;
            $fileUnmapped['was checked']=$studentIsCheckedFile;
            array_push($filesMapped, $fileUnmapped);
        }
        return $filesMapped;
    }

    static function getFileType($url){
        $parts = explode('.', $url);
        $extension = strtolower(end($parts));

        $extensions = [
            'pdf' => 'pdf',
            'doc' => 'word',
            'docx' => 'word',
            'xls' => 'excel',
            'xlsx' => 'excel',
            'ppt' => 'power point',
            'pptx' => 'power point'
        ];
        
        $filetype = $extensions[$extension] ?? 'url';
        return $filetype;
    } 
}