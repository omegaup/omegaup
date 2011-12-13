<?php

class FileHandler 
{
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
}

?>
