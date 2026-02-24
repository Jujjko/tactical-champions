<?php
declare(strict_types=1);

namespace App\Services;

class ImageUploadService {
    private string $uploadDir;
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private int $maxFileSize = 5242880;
    private int $maxWidth = 500;
    private int $maxHeight = 500;
    
    private array $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive (' . ini_get('upload_max_filesize') . ')',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
    ];
    
    public function __construct() {
        $this->uploadDir = BASE_PATH . '/public/images/champions/';
        
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    public function upload(array $file, string $namePrefix = 'champion'): array {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = $this->uploadErrors[$file['error']] ?? 'Unknown upload error';
            error_log("ImageUpload: Upload error code " . $file['error'] . " - " . $errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        }
        
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'Security: File not uploaded via HTTP POST'];
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type: ' . $mimeType . '. Allowed: JPEG, PNG, WebP, GIF'];
        }
        
        if ($file['size'] > $this->maxFileSize) {
            $maxMB = round($this->maxFileSize / 1048576, 1);
            return ['success' => false, 'error' => "File too large: " . round($file['size'] / 1048576, 1) . "MB. Max: {$maxMB}MB"];
        }
        
        $filename = $this->generateFilename($namePrefix, 'webp');
        $filepath = $this->uploadDir . $filename;
        
        $result = $this->resizeAndConvert($file['tmp_name'], $filepath);
        
        if ($result['success']) {
            error_log("ImageUpload: Success - " . $filename);
            return ['success' => true, 'url' => '/images/champions/' . $filename];
        }
        
        return ['success' => false, 'error' => $result['error'] ?? 'Failed to process image'];
    }
    
    private function resizeAndConvert(string $source, string $destination): array {
        $imageInfo = @getimagesize($source);
        if (!$imageInfo) {
            return ['success' => false, 'error' => 'Cannot read image file'];
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
            return ['success' => false, 'error' => 'Cannot create image from source'];
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
            return ['success' => false, 'error' => 'Failed to save WebP'];
        }
        
        error_log("ImageUpload: Resized from {$origWidth}x{$origHeight} to {$newWidth}x{$newHeight}");
        return ['success' => true];
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
    
    public function getMaxFileSize(): int {
        return $this->maxFileSize;
    }
    
    public function getAllowedTypes(): array {
        return $this->allowedTypes;
    }
}
