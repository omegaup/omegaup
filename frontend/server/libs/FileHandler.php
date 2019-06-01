<?php

require_once 'libs/FileUploader.php';

class FileHandler {
    protected static $fileUploader;
    public static $log;

    public static function SetFileUploader(FileUploader $fileUploader) {
        self::$fileUploader = $fileUploader;
    }

    public static function GetFileUploader() {
        if (is_null(self::$fileUploader)) {
            self::$fileUploader = new FileUploader();
        }
        return self::$fileUploader;
    }

    public static function ReadFile($filepath) {
        if (!file_exists($filepath)) {
            throw new RuntimeException('fileNotFound');
        }

        $file_content = file_get_contents($filepath);

        if (!$file_content) {
            throw new RuntimeException('Not able to read contents of file.');
        }

        return $file_content;
    }

    public static function CreateFile($filename, $contents) {
        // Open file
        self::MakeDirRecursively(dirname($filename));
        $handle = @fopen($filename, 'w');

        if (!$handle) {
            throw new RuntimeException("Not able to create file. $filename");
        }

        // Write to file
        if (!fwrite($handle, $contents)) {
            throw new RuntimeException("Not able to write '$contents' in file. $filename");
        }

        // Close file
        if (!fclose($handle)) {
            throw new RuntimeException("Not able to close recently created file. $filename");
        }
    }

    public static function MoveFileFromRequestTo($fileUploadName, $targetPath) {
        if (!(static::$fileUploader->IsUploadedFile($_FILES[$fileUploadName]['tmp_name']) &&
                static::$fileUploader->MoveUploadedFile($_FILES[$fileUploadName]['tmp_name'], $targetPath))) {
            throw new RuntimeException('FATAL: Not able to move tmp_file from _FILE. ' . implode('\n', $_FILES[$fileUploadName]));
        }
    }

    public static function MakeDirRecursively($path) {
        $directoriesToCreate = [];
        for (; $path != '/' && !is_dir($path); $path = dirname($path)) {
            $directoriesToCreate[] = $path;
        }
        $directoriesToCreate = array_reverse($directoriesToCreate);
        foreach ($directoriesToCreate as $directory) {
            self::MakeDir($directory);
        }
    }

    public static function MakeDir($pathName, $chmod = 0755) {
        self::$log->debug('Trying to create directory: ' . $pathName);
        if (!@mkdir($pathName, $chmod)) {
            throw new RuntimeException('FATAL: Not able to move create dir ' . $pathName . ' CHMOD: ' . $chmod);
        }
    }

    public static function TempDir($dir, $prefix = '', $mode = 0700) {
        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }

        do {
            $path = $dir.$prefix.mt_rand(0, 9999999);
        } while (!@mkdir($path, $mode));

        return $path;
    }

    public static function BackupDir($source, $dest) {
        if (!is_dir($source)) {
            return;
        }
        if (!is_dir($dest)) {
            @mkdir($dest, 0755);
        }

        if ($handle = opendir($source)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $sourcePath = "$source/$entry";
                $targetPath = "$dest/$entry";
                if (is_dir($sourcePath)) {
                    FileHandler::BackupDir($sourcePath, $targetPath);
                } else {
                    link($sourcePath, $targetPath);
                }
            }
            closedir($handle);
        }
    }

    public static function DeleteDirRecursive($pathName) {
        self::$log->debug('Trying to delete recursively dir: ' . $pathName);
        self::rrmdir($pathName);
    }

    public static function DeleteFile($pathName) {
        self::$log->debug('Trying to delete file: ' . $pathName);
        if (!@unlink($pathName)) {
            $errors = error_get_last();
            throw new RuntimeException("FATAL: Not able to delete file $pathName ". $errors['type'].' '. $errors['message']);
        }
    }

    private static function rrmdir($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $dh = opendir($dir);
        if (!$dh) {
            throw new RuntimeException('FATAL: Not able to open dir ' . $dir);
        }
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir("$dir/$file")) {
                self::rrmdir("$dir/$file");
            } else {
                self::DeleteFile("$dir/$file");
            }
        }
        closedir($dh);

        if (!@rmdir($dir)) {
            $errors = error_get_last();
            self::$log->error("Not able to delete dir $dir {$errors['type']} {$errors['message']}");
            throw new RuntimeException('unableToDeleteDir');
        }
    }

    public static function Copy($source, $dest) {
        if (!@copy($source, $dest)) {
            $errors = error_get_last();
            throw new RuntimeException("FATAL: Unable to copy $source to $dest: ". $errors['type'].' '. $errors['message']);
        }
    }

    public static function Rename($old, $new) {
        self::$log->info("Renaming $old to $new");
        if (!@rename($old, $new)) {
            $errors = error_get_last();
            throw new RuntimeException("FATAL: Unable to rename $old to $new " . $errors['type'].' '. $errors['message']);
        }
    }

    public static function SafeReplace($old, $new) {
        self::Rename($old, $old.'_old');
        self::Rename($new, $old);

        self::$log->info("Deleting $old _old dir");
        self::DeleteDirRecursive($old.'_old');
    }

    public static function FilePutContents($filename, $contents) {
        if (file_put_contents($filename, $contents) === false) {
            throw new RuntimeException("Not able to create file. $filename");
        }
    }
}

FileHandler::$log = Logger::getLogger('FileHandler');
