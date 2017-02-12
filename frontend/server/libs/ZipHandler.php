<?php

require_once('ApiUtils.php');

class ZipHandler {
    public static function DeflateZip($pathToZip, $extractToDir, array $filesArray = null) {
        if (is_null($pathToZip)) {
            throw new Exception('Path to ZIP is null');
        }

        $zip = new ZipArchive();
        $zipResource = $zip->open($pathToZip);

        // Workaround for https://github.com/facebook/hhvm/issues/1804
        foreach ($filesArray as $file) {
            $dir = dirname("$extractToDir/$file");
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        if ($zipResource === true) {
            if (!$zip->extractTo($extractToDir, $filesArray)) {
                throw new Exception('Error extracting zip.');
            }

            $zip->close();
        } else {
            throw new Exception('Error opening zip file: ' . ZipHandler::ErrorMessage($zipResource));
        }
    }

    public static function AddDirectory($zip, $source, $prefix, $excluded) {
        if (!is_dir($source)) {
            return;
        }

        if ($handle = opendir($source)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $sourcePath = "$source/$entry";
                $targetPath = ($prefix == '') ? $entry : "$prefix/$entry";
                if (in_array($targetPath, $excluded)) {
                    continue;
                }
                if (is_dir($sourcePath)) {
                    $zip->AddEmptyDir($targetPath);
                    ZipHandler::AddDirectory($zip, $sourcePath, $targetPath, $excluded);
                } else {
                    $zip->addFile($sourcePath, $targetPath);
                }
            }
            closedir($handle);
        }
    }

    public static function ErrorMessage($errno) {
        $zipFileFunctionsErrors = [
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
        ];
        $errmsg = 'unknown';

        foreach ($zipFileFunctionsErrors as $constName => $errorMessage) {
            if (defined($constName) and constant($constName) === $errno) {
                return 'Zip File Function error: ' . $errorMessage;
            }
        }
        return 'Zip File Function error: unknown';
    }
}
