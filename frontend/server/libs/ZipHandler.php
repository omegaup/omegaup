<?php

require_once(SERVER_PATH . '/libs/ApiUtils.php');

class ZipHandler
{
    public static function DeflateZip($pathToZip, $extractToDir, array $filesArray = NULL)
    {
        if( is_null($pathToZip) )
        {
            throw new Exception( "Path to ZIP is null" );
        }
        
        $zip = new ZipArchive();        
        $zipResource = $zip->open($pathToZip);
        
        if($zipResource === TRUE)
        {
            if(!$zip->extractTo($extractToDir, $filesArray))
            {
                throw new Exception("Error extracting zip.");
            }
            
            $zip->close();
        }
        else
        {
            throw new Exception("Error openning zip file: " . $this->zipFileErrMsg($zipResource));
        }                
    }    
        
    public static function ZipCleanup( $tmp_dir )
    {
        if( unlink( $tmp_dir ) === FALSE )
        {
            Logger::error("Unable to remove the file " . $tmp_dir . " while doing ZipCleanup");
            throw new Excpetion("Unable to remove the file " . $tmp_dir);
        }
    }	
    
    public static function zipFileErrMsg($errno) {
                
        $zipFileFunctionsErrors = array(
            'ZIPARCHIVE::ER_MULTIDISK' => 'Multi-disk zip archives not supported.',
            'ZIPARCHIVE::ER_RENAME' => 'Renaming temporary file failed.',
            'ZIPARCHIVE::ER_CLOSE' => 'Closing zip archive failed', 
            'ZIPARCHIVE::ER_SEEK' => 'Seek error',
            'ZIPARCHIVE::ER_READ' => 'Read error',
            'ZIPARCHIVE::ER_WRITE' => 'Write error',
            'ZIPARCHIVE::ER_CRC' => 'CRC error',
            'ZIPARCHIVE::ER_ZIPCLOSED' => 'Containing zip archive was closed',
            'ZIPARCHIVE::ER_NOENT' => 'No such file.',
            'ZIPARCHIVE::ER_EXISTS' => 'File already exists',
            'ZIPARCHIVE::ER_OPEN' => 'Can\'t open file', 
            'ZIPARCHIVE::ER_TMPOPEN' => 'Failure to create temporary file.', 
            'ZIPARCHIVE::ER_ZLIB' => 'Zlib error',
            'ZIPARCHIVE::ER_MEMORY' => 'Memory allocation failure', 
            'ZIPARCHIVE::ER_CHANGED' => 'Entry has been changed',
            'ZIPARCHIVE::ER_COMPNOTSUPP' => 'Compression method not supported.', 
            'ZIPARCHIVE::ER_EOF' => 'Premature EOF',
            'ZIPARCHIVE::ER_INVAL' => 'Invalid argument',
            'ZIPARCHIVE::ER_NOZIP' => 'Not a zip archive',
            'ZIPARCHIVE::ER_INTERNAL' => 'Internal error',
            'ZIPARCHIVE::ER_INCONS' => 'Zip archive inconsistent', 
            'ZIPARCHIVE::ER_REMOVE' => 'Can\'t remove file',
            'ZIPARCHIVE::ER_DELETED' => 'Entry has been deleted',
        );
        $errmsg = 'unknown';
        
        foreach ($zipFileFunctionsErrors as $constName => $errorMessage) 
        {
            if (defined($constName) and constant($constName) === $errno) 
            {
                return 'Zip File Function error: '.$errorMessage;
            }
        }
        return 'Zip File Function error: unknown';
    }
}
