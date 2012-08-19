<?php

require_once('FileUploader.php');

class FileHandler 
{
    
    protected static $fileUploader;
    
    static function SetFileUploader(FileUploader $fileUploader)
    {
        self::$fileUploader = $fileUploader;
    }
    
    static function GetFileUploader()
    {
        return self::$fileUploader;
    }
        
    static function ReadFile($filepath)
    {
        if(!file_exists($filepath))
        {
            throw new Exception("File doesn't exists.");
        }
        
        $file_content = file_get_contents($filepath);
        
        if(!$file_content)
        {
            throw new Exception("Not able to read contents of file.");
        }
        
        return $file_content;
    }
    
    static function CreateFile($filename, $contents)
    {
        // Open file
        $handle = fopen($filename, 'w');
        
        if(!$handle)
        {
            throw new Exception("Not able to create file. ");
        }
        
        // Write to file
        if(!fwrite($handle, $contents))
        {
            throw new Exception("Not able to write in file. ");
        }
        
        // Close file
        if(!fclose($handle))
        {
            throw new Exception("Not able to close recently created file. ");
        }
    }
    
    static function MoveFileFromRequestTo($fileUploadName, $targetPath)
    {
        if(!(static::$fileUploader->IsUploadedFile($_FILES[$fileUploadName]['tmp_name']) && 
            static::$fileUploader->MoveUploadedFile($_FILES[$fileUploadName]['tmp_name'], $targetPath)))
        {                  
            throw new Exception("FATAL: Not able to move tmp_file from _FILE. ". implode('\n', $_FILES[$fileUploadName]));            
        }
    }
    
    static function MakeDir($pathName, $chmod = 0777)
    {
        if(!mkdir($pathName, $chmod))
        {
            throw new Exception("FATAL: Not able to move create dir ". $pathName . " CHMOD: " . $chmod);
        }
    }
    
    static function DeleteDirRecursive($pathName)
    {
        self::rrmdir($pathName);
    }
    
    private static function rrmdir($dir) 
    {
        foreach(glob($dir . '/*') as $file) 
        {
            if(is_dir($file))
            {
                self::rrmdir($file);
            }
            else
                if (!unlink($file))
                {
                    throw new Exception("FATAL: Not able to delete file ". $file);
                }
        }
        
        if (!rmdir($dir))
        {
            throw new Exception("FATAL: Not able to delete dir ". $dir);
        }
    }

}

?>
