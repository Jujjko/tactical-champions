<?php
declare(strict_types=1);

namespace App\Services;

class ImageUploadService {
    private string $uploadDir;
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private int $maxFileSize = 5242880;
    private int $maxWidth = 500;
    private int $maxHeight = 500;
    
    public function __construct() {
        $this->uploadDir = BASE_PATH . '/public/images/champions/';
        
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    public function upload(array $file, string $namePrefix = 'champion'): ?string {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            error_log("ImageUpload: No tmp_name or not uploaded file");
            return null;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("ImageUpload: Upload error code " . $file['error']);
            return null;
        }
        
        if (!in_array($file['type'], $this->allowedTypes)) {
            error_log("ImageUpload: Invalid type " . $file['type']);
            return null;
        }
        
        if ($file['size'] > $this->maxFileSize) {
            error_log("ImageUpload: File too large " . $file['size']);
            return null;
        }
        
        $filename = $this->generateFilename($namePrefix, 'webp');
        $filepath = $this->uploadDir . $filename;
        
        if ($this->resizeAndConvert($file['tmp_name'], $filepath)) {
            error_log("ImageUpload: Success - " . $filename);
            return '/images/champions/' . $filename;
        }
        
        error_log("ImageUpload: Failed to process image");
        return null;
    }
    
    private function resizeAndConvert(string $source, string $destination): bool {
        $imageInfo = getimagesize($source);
        if (!$imageInfo) {
            error_log("ImageUpload: Cannot get image size");
            return false;
        }
        
        $origWidth = $imageInfo[0];
        $origHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        $srcImage = match($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($source),
            'image/png' => @imagecreatefrompng($source),
            'image/webp' => @imagecreatefromwebp($source),
            'image/gif' => @imagecreatefromgif($source),
            default => false
        };
        
        if (!$srcImage) {
            error_log("ImageUpload: Cannot create image from source");
            return false;
        }
        
        $ratio = min($this->maxWidth / $origWidth, $this->maxHeight / $origHeight, 1.0);
        $newWidth = (int)($origWidth * $ratio);
        $newHeight = (int)($origHeight * $ratio);
        
        $dstImage = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
        imagefill($dstImage, 0, 0, $transparent);
        
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        
        $result = imagewebp($dstImage, $destination, 80);
        
        imagedestroy($srcImage);
        imagedestroy($dstImage);
        
        if (!$result) {
            error_log("ImageUpload: Failed to save WebP");
            return false;
        }
        
        error_log("ImageUpload: Resized from {$origWidth}x{$origHeight} to {$newWidth}x{$newHeight}");
        return true;
    }
    
    public function delete(?string $url): bool {
        if (!$url || !str_starts_with($url, '/images/champions/')) {
            return false;
        }
        
        $filepath = BASE_PATH . '/public' . $url;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
    
    private function generateFilename(string $prefix, string $extension): string {
        return $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    }
}
