<?php

namespace OmegaUp;

class FileHandler {
    /** @var null|\OmegaUp\FileUploader */
    protected static $fileUploader = null;

    /** @var \Monolog\Logger */
    public static $log;

    public static function setFileUploaderForTesting(
        \OmegaUp\FileUploader $fileUploader
    ): void {
        self::$fileUploader = $fileUploader;
    }

    public static function getFileUploader(): \OmegaUp\FileUploader {
        if (self::$fileUploader === null) {
            self::$fileUploader = new \OmegaUp\FileUploader();
        }
        return self::$fileUploader;
    }

    public static function tempDir(
        string $dir,
        string $prefix = '',
        int $mode = 0700
    ): string {
        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }

        do {
            $path = $dir . $prefix . mt_rand(0, 9999999);
        } while (!@mkdir($path, $mode, true));

        return $path;
    }

    public static function deleteDirRecursively(string $pathName): void {
        self::rrmdir($pathName);
    }

    public static function deleteFile(string $pathName): void {
        if (!@unlink($pathName)) {
            $errors = error_get_last();
            if ($errors === null) {
                throw new \RuntimeException(
                    "FATAL: Not able to delete file {$pathName}"
                );
            }
            throw new \RuntimeException(
                "FATAL: Not able to delete file {$pathName} {$errors['type']} {$errors['message']}"
            );
        }
    }

    private static function rrmdir(string $dir): void {
        if (!is_dir($dir)) {
            return;
        }

        $dh = opendir($dir);
        if (!$dh) {
            throw new \RuntimeException("FATAL: Not able to open dir {$dir}");
        }
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir("$dir/$file")) {
                self::rrmdir("{$dir}/{$file}");
            } else {
                self::deleteFile("{$dir}/{$file}");
            }
        }
        closedir($dh);

        if (!@rmdir($dir)) {
            $errors = error_get_last();
            if ($errors === null) {
                self::$log->error("Not able to delete dir {$dir}");
            } else {
                self::$log->error(
                    "Not able to delete dir {$dir} {$errors['type']} {$errors['message']}"
                );
            }
            throw new \RuntimeException('unableToDeleteDir');
        }
    }
}

\OmegaUp\FileHandler::$log = \Monolog\Registry::omegaup()->withName(
    'FileHandler'
);
