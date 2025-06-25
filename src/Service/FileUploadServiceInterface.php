<?php

/**
 * File upload service interface.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface FileUploadService.
 */
interface FileUploadServiceInterface
{
    /**
     * Upload file.
     *
     * @param UploadedFile $file File to upload
     *
     * @return string Filename of uploaded file
     */
    public function upload(UploadedFile $file): string;

    /**
     * Getter for target directory.
     *
     * @return string Target directory
     */
    public function getTargetDirectory(): string;

    /**
     * Delete File from directory.
     *
     * @param string $filename filename
     *
     * @return bool bool
     */
    public function deleteFile(string $filename): bool;
}
