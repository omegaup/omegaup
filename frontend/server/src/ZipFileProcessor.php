<?php

namespace OmegaUp;

use ZipArchive;

class ZipFileProcessor{

    public static function getFileContent(ZipArchive $zip, string $fileName) : string{
        $content = $zip->getFromName($fileName);

        if ($content === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidGetFileContent',
                'zipFile'
            );
        }
        return $content;
    }

    public static function getFileContentWithLimit(
        ZipArchive $zip,
        string $fileName,
        int $limitBytes
    ) : string {
        $stat = $zip->statName($fileName);

        if($stat === false){
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidGetStat',
                'zipFile'
            );
        }

        if( $stat['size'] > $limitBytes ){
            $stream = $zip->getStream($fileName);
            if( !$stream ){
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalidGetStream',
                    'zipFile'
                );
            }
            $content = fread($stream, $limitBytes);
            fclose($stream);
            return "{$content} ...[TRUNCATED]";
        }
        
        return self::getFileContent($zip, $fileName);
    }

    public static function iterateFiles(ZipArchive $zip,  callable $callback ) : void{
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filePath = $zip->getNameIndex($i);
            if (substr($filePath, -1) === '/') {
                continue;
            }
            $callback($filePath);
        }
    }

}