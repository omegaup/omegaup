<?php

namespace OmegaUp;

class ZipFileProcessor{
    /**
     * Get the content of a file inside a ZIP.
     *
     * @param \ZipArchive $zip
     * @param string $fileName
     * @return string
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException if the file cannot be read
     */
    public static function getFileContent(
        \ZipArchive $zip,
        string $fileName
    ): string {
        $content = $zip->getFromName($fileName);

        if ($content === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidFile',
                'contentFile'
            );
        }
        return $content;
    }

    /**
     * Get the content of a file inside a ZIP, limiting to a number of bytes.
     * If the file is larger than $limitBytes, return only the first $limitBytes
     * followed by a truncation indicator.
     *
     * @param \ZipArchive $zip
     * @param string $fileName
     * @param int $limitBytes
     * @return string
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException if the file cannot be read or stream cannot be opened
     */
    public static function getFileContentWithLimit(
        \ZipArchive $zip,
        string $fileName,
        int $limitBytes
    ): string {
        $stat = $zip->statName($fileName);

        if ($stat === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidFile',
                'fileStat'
            );
        }

        if ($stat['size'] > $limitBytes) {
            $stream = $zip->getStream($fileName);
            if (!$stream) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalidFile',
                    'fileStream'
                );
            }
            $content = fread($stream, $limitBytes);
            fclose($stream);
            return "{$content} ...[TRUNCATED]";
        }

        return self::getFileContent($zip, $fileName);
    }

    /**
     * Iterate over all files inside a ZIP archive, ignoring directories.
     *
     * @param \ZipArchive $zip
     * @param callable(string): void $callback Callback invoked for each file path
     *
     * @return void
     */
    public static function iterateFiles(
        \ZipArchive $zip,
        callable $callback
    ): void {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filePath = $zip->getNameIndex($i);
            if (str_ends_with($filePath, '/')) {
                continue;
            }
            $callback($filePath);
        }
    }
}
