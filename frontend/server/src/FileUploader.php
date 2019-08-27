<?php

namespace OmegaUp;

class FileUploader {
    public function isUploadedFile(string $filename) : bool {
        return is_uploaded_file($filename);
    }

    public function moveUploadedFile(string $filename, string $targetPath) : bool {
        return move_uploaded_file($filename, $targetPath);
    }
}
