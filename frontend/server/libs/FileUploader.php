<?php

class FileUploader {
    public function IsUploadedFile($filename) {
        return is_uploaded_file($filename);
    }

    public function MoveUploadedFile($filename, $targetPath) {
        return move_uploaded_file($filename, $targetPath);
    }
}
