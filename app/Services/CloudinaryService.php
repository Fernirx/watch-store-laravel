<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
        ]);
    }

    public function upload(UploadedFile $file, string $folder = 'watch-store'): array
    {
        // Temporary fix for Windows SSL certificate issue
        // For production, download cacert.pem and configure php.ini properly
        // https://curl.se/docs/caextract.html

        // Disable SSL verification for development only
        $previousCurlOptSslVerifyPeer = ini_get('curl.cainfo');

        try {
            $uploadedFile = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => $folder,
                    'resource_type' => 'auto',
                ]
            );

            return [
                'url' => $uploadedFile['secure_url'],
                'public_id' => $uploadedFile['public_id'],
            ];
        } catch (\Exception $e) {
            // If SSL error, try without verification (development only)
            if (strpos($e->getMessage(), 'SSL') !== false || strpos($e->getMessage(), 'certificate') !== false) {
                \Cloudinary::config([
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key' => config('cloudinary.api_key'),
                    'api_secret' => config('cloudinary.api_secret'),
                    'secure' => false, // Use HTTP instead of HTTPS for development
                ]);

                $uploadedFile = $this->cloudinary->uploadApi()->upload(
                    $file->getRealPath(),
                    [
                        'folder' => $folder,
                        'resource_type' => 'auto',
                    ]
                );

                return [
                    'url' => $uploadedFile['url'], // Use HTTP URL
                    'public_id' => $uploadedFile['public_id'],
                ];
            }

            throw $e;
        }
    }

    public function delete(string $publicId): bool
    {
        try {
            $result = $this->cloudinary->uploadApi()->destroy($publicId);
            return $result['result'] === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    public function uploadMultiple(array $files, string $folder = 'watch-store'): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploadedFiles[] = $this->upload($file, $folder);
            }
        }

        return $uploadedFiles;
    }
}
