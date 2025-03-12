<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

class ResponseHelper {
    public static function send($success, $data = null, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode(['success' => $success, 'data' => $data]);
        exit;
    }

    public static function error($message, $statusCode = 500) {
        http_response_code($statusCode);
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}

class AuthHelper {
    public static function authenticate() {
        $token = self::getAuthToken();
        $user = User::validateToken($token);
        if (!$user) {
            ResponseHelper::error('Unauthorized', 401);
        }
        return $user['user_id'];
    }

    private static function getAuthToken() {
        $headers = getallheaders();
        return str_replace('Bearer ', '', $headers['Authorization'] ?? '');
    }
}
class FileUploader {
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    private $mimeMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif'
    ];

    public function __construct($uploadDir = '/var/www/html/foto_flo/Foto-flo-server/uploads/') {
        // Always use absolute path for server environments
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        
        // Immediate directory check
        if (!is_dir($this->uploadDir)) {
            $this->ensureUploadDirExists();
        }
    }

    public function handleBase64Upload($base64Data) {
        try {
            $parsed = $this->parseBase64($base64Data);
            $mimeType = $parsed['mime'];
            $imageData = $parsed['data'];

            if (!in_array($mimeType, $this->allowedTypes)) {
                throw new Exception('Invalid file type: ' . $mimeType, 415);
            }

            $extension = $this->mimeMap[$mimeType];
            $fileName = uniqid() . '.' . $extension;
            $targetPath = $this->uploadDir . $fileName;

            // Triple-check directory state
            $this->ensureUploadDirExists(true); // Force recheck

            // Atomic file write with error suppression
            $bytesWritten = @file_put_contents($targetPath, $imageData);
            if ($bytesWritten === false || $bytesWritten !== strlen($imageData)) {
                throw new Exception('Failed to write image file. Check disk space and permissions.');
            }

            // Verify file was actually written
            if (!file_exists($targetPath)) {
                throw new Exception('File write verification failed');
            }

            return $targetPath; // Return full server path

        } catch (Exception $e) {
            error_log("Upload Error: " . $e->getMessage());
            throw $e; // Re-throw for controller handling
        }
    }

    private function parseBase64($base64Data) {
        // Handle data URI scheme
        if (preg_match('/^data:(image\/\w+);base64,/', $base64Data, $matches)) {
            return [
                'mime' => $matches[1],
                'data' => base64_decode(str_replace($matches[0], '', $base64Data))
            ];
        }

        // Handle raw base64
        $imageData = base64_decode($base64Data);
        if (!$imageData) {
            throw new Exception('Invalid base64 data');
        }

        // Detect MIME type from binary
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return [
            'mime' => $finfo->buffer($imageData),
            'data' => $imageData
        ];
    }

    private function ensureUploadDirExists($forceCheck = false) {
        if ($forceCheck) clearstatcache(true, $this->uploadDir);

        if (!is_dir($this->uploadDir)) {
            $parentDir = dirname($this->uploadDir);
            
            // Verify parent directory permissions
            if (!is_writable($parentDir)) {
                error_log("Parent directory permissions: " . substr(sprintf('%o', fileperms($parentDir)), -4));
                throw new Exception("Parent directory '$parentDir' not writable");
            }

            // Create with race condition protection
            if (!@mkdir($this->uploadDir, 0755, true)) {
                $error = error_get_last();
                
                // Check if directory was created by concurrent process
                if (!is_dir($this->uploadDir)) {
                    throw new Exception("Directory creation failed: " . ($error['message'] ?? 'Unknown error'));
                }
            }

            // Post-creation permission set
            if (!chmod($this->uploadDir, 0755)) {
                throw new Exception("Failed to set directory permissions");
            }
        }

        // Final writable check
        if (!is_writable($this->uploadDir)) {
            throw new Exception("Directory exists but is not writable");
        }
    }
}

function processTags($tagsInput) {
    return array_filter(
        array_map('trim', explode(',', $tagsInput)),
        function($tag) { return !empty($tag); }
    );
}

function getRequestData() {
    if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
    return $_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : $_POST;
}

function handleOptionsRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}